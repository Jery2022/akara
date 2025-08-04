
<?php
// backend/api/routes/suppliers.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response, et JWT si utilisé globalement

use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;  

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
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
    // --- Méthode GET : Récupérer tous les fournisseurs ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt  = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Fournisseurs récupérés avec succès.', $items); // Utilisation de success()
        } catch (\PDOException $e) {
            error_log("Erreur DB GET suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la récupération des fournisseurs.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer un fournisseur spécifique ---
    'GET_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) { // Validation numérique de l'ID
            Response::badRequest('ID de fournisseur invalide ou manquant.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = :id"); // Paramètre nommé
            $stmt->execute([':id' => $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                Response::notFound('Fournisseur non trouvé.');
                return;
            }
            Response::success('Fournisseur récupéré avec succès.', $item); // Utilisation de success()
        } catch (\PDOException $e) {
            error_log("Erreur DB GET_ID suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la récupération du fournisseur.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- GESTION DES REQUÊTES POST (Ajouter un nouveau fournisseur) ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
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
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        $name       = trim($data['name']);
        $refContact = trim($data['refContact']);
        $phone      = trim($data['phone']);
        $email      = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $contrat_id = null; // Initialiser à null par défaut

        if (!$email) {
            Response::badRequest('Format d\'e-mail invalide.');
            return;
        }
        // Validation du téléphone: une regex simple pour des chiffres, espaces, tirets, parenthèses, plus
        if (!preg_match('/^[0-9\s\-\(\)\+]+$/', $phone)) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }

        try {
            $sql  = "INSERT INTO suppliers (name, refContact, phone, email, contrat_id) 
                     VALUES (:name, :refContact, :phone, :email, :contrat_id)";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':name'       => $name,
                ':refContact' => $refContact,
                ':phone'      => $phone,
                ':email'      => $email,
                ':contrat_id' => $contrat_id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Fournisseur ajouté avec succès.', ['id' => $pdo->lastInsertId()]); // 201 Created
        } catch (\PDOException $e) {
            error_log("Erreur DB POST suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de l\'ajout du fournisseur.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- GESTION DES REQUÊTES PUT_ID (Mettre à jour un fournisseur existant) ---
    'PUT_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour modifier une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) { // Validation numérique de l'ID
            Response::badRequest('ID de fournisseur invalide ou manquant pour la mise à jour.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        $name       = trim($data['name']);
        $refContact = trim($data['refContact']);
        $phone      = trim($data['phone']);
        $email      = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $contrat_id = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);

        if (!$email) {
            Response::badRequest('Format d\'e-mail invalide.');
            return;
        }
        // Validation du téléphone
        if (!preg_match('/^[0-9\s\-\(\)\+]+$/', $phone)) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }

        try {
            $sql  = "UPDATE suppliers SET 
                        name = :name, 
                        refContact = :refContact, 
                        phone = :phone, 
                        email = :email, 
                        contrat_id = :contrat_id 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':name'       => $name,
                ':refContact' => $refContact,
                ':phone'      => $phone,
                ':email'      => $email,
                ':contrat_id' => $contrat_id,
                ':id'         => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun fournisseur trouvé avec cet ID ou aucune modification effectuée.');
                return;
            }

            Response::success('Fournisseur mis à jour avec succès.', ['id' => (int) $id]); // Utilisation de success() et ajout de l'ID
        } catch (\PDOException $e) {
            error_log("Erreur DB PUT suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la mise à jour du fournisseur.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- GESTION DES REQUÊTES DELETE_ID (Supprimer un fournisseur) ---
    'DELETE_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour supprimer une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) { // Validation numérique de l'ID
            Response::badRequest('ID de fournisseur invalide ou manquant pour la suppression.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql  = "DELETE FROM suppliers WHERE id = :id"; // Paramètre nommé
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun fournisseur trouvé avec cet ID pour la suppression.');
                return;
            }

            Response::success('Fournisseur supprimé avec succès.', ['id' => (int) $id]); // Utilisation de success()
        } catch (\PDOException $e) {
            error_log("Erreur DB DELETE suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la suppression du fournisseur.', 500, ['details' => $e->getMessage()]);
        }
    },
];