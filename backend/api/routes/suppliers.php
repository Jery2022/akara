
<?php
// backend/api/routes/suppliers.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response, et JWT si utilisé globalement

use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer un ou plusieurs fournisseurs ---
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

        try {
            // Affiche uniquement les fournisseurs actifs (is_active = 1)
            $baseQuery = "SELECT s.id, s.name, s.refContact, s.phone, s.email, s.contrat_id, c.name as contrat_name 
                          FROM suppliers s 
                          LEFT JOIN contrats c ON s.contrat_id = c.id 
                          WHERE s.is_active = 1";

            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de fournisseur invalide.');
                    return;
                }
                // Ajout de la condition sur l'ID à la requête de base
                $stmt = $pdo->prepare("$baseQuery AND s.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Fournisseur non trouvé.');
                } else {
                    Response::success('Fournisseur récupéré avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query("$baseQuery ORDER BY s.name ASC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Fournisseurs récupérés avec succès.', $items);
            }
        } catch (\PDOException $e) {
            error_log("Erreur DB GET suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la récupération des fournisseurs.', 500, ['details' => $e->getMessage()]);
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
            // Par défaut, un nouveau fournisseur est actif
            $sql  = "INSERT INTO suppliers (name, refContact, phone, email, contrat_id, is_active) 
                     VALUES (:name, :refContact, :phone, :email, :contrat_id, 1)";
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

            Response::created(['id' => $pdo->lastInsertId()], 'Fournisseur ajouté avec succès.');
        } catch (\PDOException $e) {
            error_log("Erreur DB POST suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de l\'ajout du fournisseur.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- GESTION DES REQUÊTES PUT (Mettre à jour un fournisseur existant) ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $idFromUrl = $params['id'] ?? null;
        $data = json_decode(file_get_contents('php://input'), true);
        $idFromBody = $data['id'] ?? null;

        // L'ID de l'URL est prioritaire (convention REST), 
        // mais on utilise l'ID du corps de la requête comme fallback (suggestion de l'utilisateur).
        $id = $idFromUrl ?? $idFromBody;

        if (!is_numeric($id) || $id <= 0) { // Validation numérique de l'ID final
            Response::badRequest('ID de fournisseur invalide ou manquant pour la mise à jour.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

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
        // Valider contrat_id : doit être un entier ou null
        $contrat_id = $data['contrat_id'] ?? null;
        if ($contrat_id !== null) {
            $contrat_id = filter_var($contrat_id, FILTER_VALIDATE_INT);
            if ($contrat_id === false) {
                // Si la valeur n'est pas un entier valide, la forcer à null
                // pour éviter les erreurs de clé étrangère.
                $contrat_id = null;
            }
        }

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

    // --- GESTION DES REQUÊTES DELETE (Supprimer un fournisseur) ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour effectuer cette action.');
            return;
        }

        $id = $params['id'] ?? null;

        // Fallback pour lire l'ID depuis le corps de la requête si non présent dans l'URL
        if (empty($id)) {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
        }

        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de fournisseur invalide ou manquant pour la suppression.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Suppression logique en mettant le champ is_active à 0
            $sql = "UPDATE suppliers SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression logique.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun fournisseur trouvé avec cet ID.');
                return;
            }

            Response::success('Fournisseur désactivé avec succès.', ['id' => (int) $id]);
        } catch (\PDOException $e) {
            error_log("Erreur DB lors de la suppression logique du fournisseur: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la suppression.', 500, ['details' => $e->getMessage()]);
        }
    },
];
