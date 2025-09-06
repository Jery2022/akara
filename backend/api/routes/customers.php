<?php
// backend/api/routes/customers.php

// Désactiver l'affichage des erreurs pour éviter l'output HTML non JSON
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour JWT et Core\Response

use Core\Response; // Importation de la classe Response
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
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

        $allowedSortColumns = ['name', 'refContact', 'phone', 'email'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'name';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'asc';
        }

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de client invalide.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT c.id, c.name, c.refContact, c.phone, c.email, c.contrat_id, co.name as contrat_name 
                                       FROM customers c 
                                       LEFT JOIN contrats co ON c.contrat_id = co.id 
                                       WHERE c.is_active = 1 AND c.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Client non trouvé.');
                } else {
                    Response::success('Client récupéré avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query("SELECT c.id, c.name, c.refContact, c.phone, c.email, c.contrat_id, co.name as contrat_name 
                                     FROM customers c 
                                     LEFT JOIN contrats co ON c.contrat_id = co.id 
                                     WHERE c.is_active = 1 
                                     ORDER BY c.name ASC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Clients récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('PDOException in customers.php: ' . $e->getMessage());
            Response::error('Erreur de base de données lors de la récupération des clients.', 500);
        } catch (Throwable $e) {
            error_log('General error in customers.php: ' . $e->getMessage());
            Response::error('Une erreur inattendue est survenue.', 500);
        }
    },

    // --- Méthode POST : Créer un nouveau client ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour créer une ressource.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation spécifique de l'email
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation du téléphone (simple regex pour des chiffres, espaces, tirets, parenthèses, plus)
        if (! preg_match('/^[0-9\s\-\(\)\+]+$/', $data['phone'])) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }
        try {
            // Gestion améliorée de contrat_id
            $contrat_id = null; // Initialiser à null par défaut
            if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
                $filtered_contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT);
                if ($filtered_contrat_id === false || $filtered_contrat_id <= 0) {
                    Response::badRequest("Le champ 'contrat_id' doit être un ID valide (entier positif) s'il est fourni.");
                    return;
                }
                $contrat_id = $filtered_contrat_id;
            }

            $sql = "INSERT INTO customers (name, refContact, phone, email, contrat_id, is_active) 
                     VALUES (:name, :refContact, :phone, :email, :contrat_id, 1)";

            $stmt = $pdo->prepare($sql);
            if (! $stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':name'       => $data['name'],
                ':refContact' => $data['refContact'],
                ':phone'      => $data['phone'],
                ':email'      => $data['email'],
                ':contrat_id' => $contrat_id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Client ajouté avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating customer: ' . $e->getMessage());
            // Gestion plus spécifique pour les erreurs de clé étrangère
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                Response::error('Impossible d\'ajouter le client : l\'ID du contrat spécifié n\'existe pas.', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la création du client.', 500, ['details' => $e->getMessage()]);
            }
        }
    },

    // --- Méthode PUT : Modifier un client spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (! is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de client invalide ou manquant dans l\'URL.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation spécifique de l'email
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation du téléphone
        if (! preg_match('/^[0-9\s\-\(\)\+]+$/', $data['phone'])) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }

        try {
            // Gestion améliorée de contrat_id pour la mise à jour
            $contrat_id = null; // Initialiser à null par défaut
            if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
                $filtered_contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT);
                if ($filtered_contrat_id === false || $filtered_contrat_id <= 0) {
                    Response::badRequest("Le champ 'contrat_id' doit être un ID valide (entier positif) s'il est fourni.");
                    return;
                }
                $contrat_id = $filtered_contrat_id;
            }

            $sql = "UPDATE customers SET 
                        name = :name, 
                        refContact = :refContact, 
                        phone = :phone, 
                        email = :email, 
                        contrat_id = :contrat_id 
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            if (! $stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':name'       => $data['name'],
                ':refContact' => $data['refContact'],
                ':phone'      => $data['phone'],
                ':email'      => $data['email'],
                ':contrat_id' => $contrat_id,
                ':id'         => $id,
            ]);

            if (! $executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Client non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Client modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating customer: ' . $e->getMessage());
            // Ajout d'une gestion plus spécifique pour les erreurs de clé étrangère
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                Response::error('Impossible de modifier le client : l\'ID du contrat spécifié n\'existe pas.', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la modification du client.', 500, ['details' => $e->getMessage()]);
            }
        }
    },

    // --- Méthode DELETE : Supprimer un client spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour effectuer cette action.');
            return;
        }

        $id = $params['id'] ?? null;

        if (empty($id)) {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
        }

        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de client invalide ou manquant pour la suppression.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "UPDATE customers SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression logique.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun client trouvé avec cet ID.');
                return;
            }

            Response::success('Client désactivé avec succès.', ['id' => (int) $id]);
        } catch (\PDOException $e) {
            error_log("Erreur DB lors de la suppression logique du client: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la suppression.', 500, ['details' => $e->getMessage()]);
        }
    },
];
