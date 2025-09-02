<?php
// backend/api/routes/contrats.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response

use Core\Response;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Adaptez à votre frontend
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer un ou plusieurs contrats ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }
        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $id = $params['id'] ?? null;
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_debut';
        $order = $_GET['order'] ?? 'desc';

        $allowedSortColumns = ['name', 'objet', 'date_debut', 'date_fin', 'montant', 'type', 'status'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'date_debut';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de contrat invalide.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT * FROM contrats WHERE id = :id AND is_active = 1");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Contrat non trouvé.');
                } else {
                    Response::success('Contrat récupéré avec succès.', $item);
                }
            } else {
                $sql = "SELECT * FROM contrats WHERE is_active = 1";
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " WHERE (name LIKE :search OR objet LIKE :search OR type LIKE :search OR status LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sort $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Contrats récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching contrats: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des contrats.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouveau contrat ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);



        // Champs obligatoires
        $requiredFields = ['name', 'objet', 'date_debut', 'date_fin', 'montant', 'date_signature', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire A.");
                return;
            }
        }

        // Validation des données
        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            Response::badRequest("Le champ 'montant' doit être un nombre positif.");
            return;
        }

        $allowedTypes = ['client', 'fournisseur', 'employe'];
        if (!in_array($data['type'], $allowedTypes)) {
            Response::badRequest("Le champ 'type' doit être 'client', 'fournisseur' ou 'employe'.");
            return;
        }

        // Les dates doivent être au bon format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_debut'])) {
            Response::badRequest("Le format de 'date_debut' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_fin'])) {
            Response::badRequest("Le format de 'date_fin' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_signature'])) {
            Response::badRequest("Le format de 'date_signature' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }

        try {
            $sql = "INSERT INTO contrats (name, objet, date_debut, date_fin, status, montant, signataire, date_signature, fichier_contrat, type) 
                    VALUES (:name, :objet, :date_debut, :date_fin, :status, :montant, :signataire, :date_signature, :fichier_contrat, :type)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'             => $data['name'],
                ':objet'            => $data['objet'],
                ':date_debut'       => $data['date_debut'],
                ':date_fin'         => $data['date_fin'],
                ':status'           => $data['status'] ?? 'en cours', // Utilise la valeur par défaut si non fournie
                ':montant'          => (float) $data['montant'],
                ':signataire'       => $data['signataire'] ?? null,
                ':date_signature'   => $data['date_signature'],
                ':fichier_contrat'  => $data['fichier_contrat'] ?? null,
                ':type'             => $data['type'],
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Contrat ajouté avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating contrat: ' . $e->getMessage());
            Response::error('Erreur lors de la création du contrat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier un contrat spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        $id = $params['id'] ?? null;

        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de contrat invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'objet', 'date_debut', 'date_fin', 'montant', 'date_signature', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des données
        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            Response::badRequest("Le champ 'montant' doit être un nombre positif.");
            return;
        }

        $allowedTypes = ['client', 'fournisseur', 'employe'];
        if (!in_array($data['type'], $allowedTypes)) {
            Response::badRequest("Le champ 'type' doit être 'client', 'fournisseur' ou 'employe'.");
            return;
        }

        // Les dates doivent être au bon format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_debut'])) {
            Response::badRequest("Le format de 'date_debut' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_fin'])) {
            Response::badRequest("Le format de 'date_fin' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_signature'])) {
            Response::badRequest("Le format de 'date_signature' est invalide. Utilisez AAAA-MM-JJ.");
            return;
        }

        try {
            $sql = "UPDATE contrats SET 
                        name = :name, 
                        objet = :objet, 
                        date_debut = :date_debut, 
                        date_fin = :date_fin,
                        status = :status,
                        montant = :montant,
                        signataire = :signataire,
                        date_signature = :date_signature,
                        fichier_contrat = :fichier_contrat,
                        type = :type
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'             => $data['name'],
                ':objet'            => $data['objet'],
                ':date_debut'       => $data['date_debut'],
                ':date_fin'         => $data['date_fin'],
                ':status'           => $data['status'] ?? 'en cours',
                ':montant'          => (float) $data['montant'],
                ':signataire'       => $data['signataire'] ?? null,
                ':date_signature'   => $data['date_signature'],
                ':fichier_contrat'  => $data['fichier_contrat'] ?? null,
                ':type'             => $data['type'],
                ':id'               => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Contrat non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Contrat modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating contrat: ' . $e->getMessage());
            Response::error('Erreur lors de la modification du contrat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer un contrat spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de contrat invalide ou manquant.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "UPDATE contrats SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Contrat non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Contrat supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting contrat: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression du contrat.', 500, ['details' => $e->getMessage()]);
        }
    },
];
