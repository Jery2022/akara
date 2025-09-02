<?php
// backend/api/routes/entreeStock.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response

use Core\Response;


// Obtenez l'instance PDO une seule fois au début
$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer une ou plusieurs entrées de stock ---
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
        $sort = $_GET['sort'] ?? 'ref_date';
        $order = $_GET['order'] ?? 'desc';

        $allowedSortColumns = ['produit_nom', 'quantity', 'ref_date', 'user_name', 'supplier_nom', 'entrepot_nom'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'ref_date';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $baseQuery = "SELECT es.*, p.name AS produit_nom, u.name AS user_name, s.name AS supplier_nom, e.name AS entrepot_nom
                      FROM entreeStock es
                      LEFT JOIN produits p ON es.produit_id = p.id
                      LEFT JOIN users u ON es.user_id = u.id
                      LEFT JOIN suppliers s ON es.suppliers_id = s.id
                      LEFT JOIN entrepots e ON es.entrepot_id = e.id";

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID d\'entrée de stock invalide.');
                    return;
                }
                $stmt = $pdo->prepare("$baseQuery WHERE es.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Entrée de stock non trouvée.');
                } else {
                    Response::success('Entrée de stock récupérée avec succès.', $item);
                }
            } else {
                $sql = $baseQuery;
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " WHERE (p.name LIKE :search OR u.name LIKE :search OR s.name LIKE :search OR e.name LIKE :search OR es.motif LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sort $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Entrées de stock récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching entreeStock: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des entrées de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle entrée de stock ---
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
        $requiredFields = ['produit_id', 'quantity', 'ref_date', 'entrepot_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des IDs (doivent être des entiers positifs)
        $idFields = ['produit_id', 'entrepot_id'];
        foreach ($idFields as $field) {
            $data[$field] = filter_var($data[$field], FILTER_VALIDATE_INT);
            if ($data[$field] === false || $data[$field] <= 0) {
                Response::badRequest("Le champ '{$field}' doit être un ID valide (entier positif).");
                return;
            }
        }

        // Validation de quantity
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity <= 0) { // La quantité doit être un entier positif
            Response::badRequest("Le champ 'quantity' doit être un entier positif.");
            return;
        }

        // Validation de user_id et suppliers_id (s'ils sont présents, ils doivent être des entiers positifs)
        $user_id = null;
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                Response::badRequest("Le champ 'user_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        $suppliers_id = null;
        if (isset($data['suppliers_id']) && $data['suppliers_id'] !== '') {
            $suppliers_id = filter_var($data['suppliers_id'], FILTER_VALIDATE_INT);
            if ($suppliers_id === false || $suppliers_id <= 0) {
                Response::badRequest("Le champ 'suppliers_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation du format de la date (YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['ref_date'])) {
            Response::badRequest("Le format de la date de référence est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $produit_id  = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];
            $ref_date    = $data['ref_date'];
            $motif       = trim($data['motif'] ?? '');

            $sql = "INSERT INTO entreeStock (produit_id, quantity, ref_date, user_id, suppliers_id, entrepot_id, motif) 
                    VALUES (:produit_id, :quantity, :ref_date, :user_id, :suppliers_id, :entrepot_id, :motif)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':ref_date'    => $ref_date,
                ':user_id'     => $user_id,
                ':suppliers_id' => $suppliers_id,
                ':entrepot_id' => $entrepot_id,
                ':motif'       => $motif,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Entrée de stock ajoutée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating entreeStock: ' . $e->getMessage());
            Response::error('Erreur lors de la création de l\'entrée de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier une entrée de stock spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'entrée de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['produit_id', 'quantity', 'ref_date', 'entrepot_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des IDs (doivent être des entiers positifs)
        $idFields = ['produit_id', 'entrepot_id'];
        foreach ($idFields as $field) {
            $data[$field] = filter_var($data[$field], FILTER_VALIDATE_INT);
            if ($data[$field] === false || $data[$field] <= 0) {
                Response::badRequest("Le champ '{$field}' doit être un ID valide (entier positif).");
                return;
            }
        }

        // Validation de quantity
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity <= 0) {
            Response::badRequest("Le champ 'quantity' doit être un entier positif.");
            return;
        }

        // Validation de user_id et suppliers_id (s'ils sont présents, ils doivent être des entiers positifs)
        $user_id = null;
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                Response::badRequest("Le champ 'user_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        $suppliers_id = null;
        if (isset($data['suppliers_id']) && $data['suppliers_id'] !== '') {
            $suppliers_id = filter_var($data['suppliers_id'], FILTER_VALIDATE_INT);
            if ($suppliers_id === false || $suppliers_id <= 0) {
                Response::badRequest("Le champ 'suppliers_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation du format de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['ref_date'])) {
            Response::badRequest("Le format de la date de référence est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $produit_id  = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];
            $ref_date    = $data['ref_date'];
            $motif       = trim($data['motif'] ?? '');

            $sql = "UPDATE entreeStock SET 
                        produit_id = :produit_id, 
                        quantity = :quantity, 
                        ref_date = :ref_date, 
                        user_id = :user_id, 
                        suppliers_id = :suppliers_id, 
                        entrepot_id = :entrepot_id, 
                        motif = :motif 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':ref_date'    => $ref_date,
                ':user_id'     => $user_id,
                ':suppliers_id' => $suppliers_id,
                ':entrepot_id' => $entrepot_id,
                ':motif'       => $motif,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Entrée de stock non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Entrée de stock modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating entreeStock: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'entrée de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer une entrée de stock spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'entrée de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM entreeStock WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Entrée de stock non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Entrée de stock supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting entreeStock: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de l\'entrée de stock.', 500, ['details' => $e->getMessage()]);
        }
    },
];
