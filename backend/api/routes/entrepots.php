<?php
// backend/api/routes/entrepots.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response

use Core\Response;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Adaptez à votre frontend
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Ajout de OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer un ou plusieurs entrepôts ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour accéder à cette ressource.');
            return;
        }
        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $id = $params['id'] ?? null;
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'name';
        $order = $_GET['order'] ?? 'asc';

        $allowedSortColumns = ['name', 'adresse', 'responsable', 'capacity'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'name';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'asc';
        }

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID d\'entrepôt invalide.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT * FROM entrepots WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Entrepôt non trouvé.');
                } else {
                    Response::success('Entrepôt récupéré avec succès.', $item);
                }
            } else {
                $sql = "SELECT * FROM entrepots";
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " WHERE (name LIKE :search OR adresse LIKE :search OR responsable LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sort $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Entrepôts récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching entrepots: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des entrepôts.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouvel entrepôt ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour créer une ressource.'
            );
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires  
        if (!isset($data['name']) || empty(trim($data['name']))) {
            Response::badRequest("Le champ 'name' est obligatoire et ne peut pas être vide.");
            return;
        }
        if (!isset($data['adresse']) || empty(trim($data['adresse']))) {
            Response::badRequest("Le champ 'adresse' est obligatoire et ne peut pas être vide.");
            return;
        }
        if (!isset($data['responsable']) || empty(trim($data['responsable']))) {
            Response::badRequest("Le champ 'responsable' est obligatoire et ne peut pas être vide.");
            return;
        }

        try {
            $name        = trim($data['name']);
            $adresse     = trim($data['adresse']);
            $responsable = trim($data['responsable']);

            $sql  = "INSERT INTO entrepots (name, adresse, responsable) VALUES (:name, :adresse, :responsable)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'         => $name,
                ':adresse'      => $adresse,
                ':responsable'  => $responsable,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Entrepôt ajouté avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating entrepot: ' . $e->getMessage());
            Response::error('Erreur lors de la création de l\'entrepôt.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier un entrepôt spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'entrepôt invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour : tous ceux que le frontend envoie
        $requiredFields = [
            'name',
            'adresse',
            'email',
            'telephone',
            'capacity',
            'quality_stockage',
            'black_list'
        ];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation spécifique de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation de la capacité (doit être un nombre positif ou nul)
        if (!is_numeric($data['capacity']) || $data['capacity'] < 0) {
            Response::badRequest('La capacité doit être un nombre positif ou nul.');
            return;
        }

        try {
            // Nettoyage et récupération de tous les champs
            $name             = trim($data['name']);
            $adresse          = trim($data['adresse']);
            $responsable      = trim($data['responsable'] ?? '');
            $email            = trim($data['email']);
            $telephone        = trim($data['telephone']);
            $capacity         = (float) $data['capacity'];
            $quality_stockage = trim($data['quality_stockage']);
            $black_list       = trim($data['black_list']);

            // Requête UPDATE avec tous les champs
            $sql  = "UPDATE entrepots SET 
                        name = :name, 
                        adresse = :adresse, 
                        responsable = :responsable, 
                        email = :email, 
                        telephone = :telephone, 
                        capacity = :capacity, 
                        quality_stockage = :quality_stockage, 
                        black_list = :black_list 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'             => $name,
                ':adresse'          => $adresse,
                ':responsable'      => $responsable,
                ':email'            => $email,
                ':telephone'        => $telephone,
                ':capacity'         => $capacity,
                ':quality_stockage' => $quality_stockage,
                ':black_list'       => $black_list,
                ':id'               => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Entrepôt non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Entrepôt modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating entrepot: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'entrepôt.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer un entrepôt spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'entrepôt invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM entrepots WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Entrepôt non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Entrepôt supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting entrepot: ' . $e->getMessage());
            // Si l'erreur est due à une contrainte de clé étrangère, donnez un message plus spécifique
            if ($e->getCode() === '23000') { // Code SQLSTATE pour violation d'intégrité
                Response::error('Impossible de supprimer cet entrepôt car il est lié à des articles en stock ou d\'autres enregistrements.', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la suppression de l\'entrepôt.', 500, ['details' => $e->getMessage()]);
            }
        }
    },
];
