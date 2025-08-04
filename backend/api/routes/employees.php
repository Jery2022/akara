<?php
// backend/api/routes/employees.php

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
    // --- Méthode GET : Récupérer tous les employés ACTIFS ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
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
            // Sélectionne uniquement les employés actifs
            $stmt  = $pdo->query("SELECT * FROM employees WHERE is_active = TRUE ORDER BY name ASC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Employés actifs récupérés avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching active employees: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des employés actifs.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET avec ID : Récupérer un employé spécifique (actif ou non) ---
    'GET_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'employé invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Récupère l'employé par ID, qu'il soit actif ou non
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                Response::notFound('Employé non trouvé.');
                return;
            }
            Response::success('Employé récupéré avec succès.', $employee);
        } catch (PDOException $e) {
            error_log('Error fetching single employee: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération de l\'employé.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouvel employé (actif par défaut) ---
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

        // Champs obligatoires pour la création d'un employé
        $requiredFields = ['name', 'fonction', 'salary', 'phone', 'email', 'quality', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }
        
        // Validation spécifique de l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation du salaire (doit être un nombre positif ou nul)
        if (!is_numeric($data['salary']) || $data['salary'] < 0) {
            Response::badRequest('Le salaire doit être un nombre positif ou nul.');
            return;
        }

        // Nettoyage et validation des autres champs texte
        $name     = trim($data['name']);
        $fonction = trim($data['fonction']);
        $phone    = trim($data['phone']);
        $email    = trim($data['email']);
        $quality  = trim($data['quality']);
        $category = trim($data['category']);
        $salary   = (float) $data['salary'];

        try {
            $sql = "INSERT INTO employees (name, fonction, salary, phone, email, quality, category, user_id, is_active) 
                    VALUES (:name, :fonction, :salary, :phone, :email, :quality, :category, :user_id, :is_active)";
            $stmt = $pdo->prepare($sql);

            // Récupération de l'ID de l'utilisateur authentifié si disponible
            $user_id = $currentUser->id ?? null; 

            $executed = $stmt->execute([
                ':name'     => $name,
                ':fonction' => $fonction,
                ':salary'   => $salary,
                ':phone'    => $phone,
                ':email'    => $email,
                ':quality'  => $quality, 
                ':category' => $category, 
                ':user_id'  => $user_id, // L'ID de l'utilisateur authentifié
                ':is_active' => TRUE, // Nouvel employé actif par défaut
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Employé créé avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating employee: ' . $e->getMessage());
            Response::error('Erreur lors de la création de l\'employé.', 500, ['details' => $e->getMessage()]);
        }
    },

    'PUT_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour modifier une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;

        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'employé invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour d'un employé
        $requiredFields = ['name', 'fonction', 'salary', 'phone', 'email', 'quality', 'category'];
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

        // Validation du salaire (doit être un nombre positif ou nul)
        if (!is_numeric($data['salary']) || $data['salary'] < 0) {
            Response::badRequest('Le salaire doit être un nombre positif ou nul.');
            return;
        }

        // Nettoyage et validation des autres champs texte
        $name     = trim($data['name']);
        $fonction = trim($data['fonction']);
        $phone    = trim($data['phone']);
        $email    = trim($data['email']);
        $quality  = trim($data['quality']);
        $category = trim($data['category']);
        $salary   = (float) $data['salary'];

        // is_active peut être envoyé dans la payload pour réactiver un employé
        $is_active = isset($data['is_active']) ? (bool)$data['is_active'] : null;

        try {
            $sql = "UPDATE employees SET 
                        name = :name, 
                        fonction = :fonction, 
                        salary = :salary, 
                        phone = :phone, 
                        email = :email, 
                        quality = :quality, 
                        category = :category ";  

            // Ajoute is_active à la requête de mise à jour seulement si elle est fournie
            if ($is_active !== null) {
                $sql .= ", is_active = :is_active"; // Ajout d'une virgule si is_active est ajouté
            }

            $sql .= " WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $paramsToExecute = [
                ':name'     => $name,
                ':fonction' => $fonction,
                ':salary'   => $salary,
                ':phone'    => $phone,
                ':email'    => $email,
                ':quality'  => $quality, 
                ':category' => $category, 
                ':id'       => $id, // Utilise l'ID extrait des paramètres de l'URL
            ];

            if ($is_active !== null) {
                $paramsToExecute[':is_active'] = $is_active;
            }

            $executed = $stmt->execute($paramsToExecute);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Employé non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Employé modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating employee: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'employé.', 500, ['details' => $e->getMessage()]);
        }
    },
    
    // --- Méthode DELETE_ID : Désactiver (suppression logique) un employé spécifique ---
    'DELETE_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour supprimer une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;

        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'employé invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Mise à jour de is_active à FALSE (suppression logique)
            $sql = "UPDATE employees SET is_active = FALSE WHERE id = :id"; 
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête de désactivation.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de désactivation.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Employé non trouvé avec l\'ID spécifié ou déjà inactif.'); 
                return;
            }

            Response::success('Employé désactivé (supprimé logiquement) avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error soft deleting employee: ' . $e->getMessage());
            Response::error('Erreur lors de la désactivation de l\'employé.', 500, ['details' => $e->getMessage()]);
        }
    },
];
