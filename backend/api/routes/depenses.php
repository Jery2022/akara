<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Core\Response;

$pdo = getPDO();

return [
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé.');
            return;
        }

        $id = $params['id'] ?? null;
        try {
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM depenses WHERE id = :id AND is_active = 1");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$item) {
                    Response::notFound('Dépense non trouvée.');
                } else {
                    Response::success('Dépense récupérée.', $item);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM depenses WHERE is_active = 1 ORDER BY date_depense DESC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Dépenses récupérées.', $items);
            }
        } catch (PDOException $e) {
            Response::error('Erreur de base de données.', 500, ['details' => $e->getMessage()]);
        }
    },

    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé.');
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['name', 'produit_id', 'quantity', 'price', 'date_depense', 'description', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        try {
            $total = (float)$data['quantity'] * (float)$data['price'];

            $sql = "INSERT INTO depenses (name, user_id, produit_id, suppliers_id, contrat_id, quantity, price, total, date_depense, description, nature, category) 
                    VALUES (:name, :user_id, :produit_id, :suppliers_id, :contrat_id, :quantity, :price, :total, :date_depense, :description, :nature, :category)";

            $stmt = $pdo->prepare($sql);
            $executed = $stmt->execute([
                ':name'         => $data['name'],
                ':user_id'      => $currentUser->user_id,
                ':produit_id'   => $data['produit_id'],
                ':suppliers_id' => $data['suppliers_id'] ?? null,
                ':contrat_id'   => $data['contrat_id'] ?? null,
                ':quantity'     => $data['quantity'],
                ':price'        => $data['price'],
                ':total'        => $total,
                ':date_depense'  => $data['date_depense'],
                ':description'  => $data['description'],
                ':nature'       => $data['nature'],
                ':category'     => $data['category']
            ]);

            if ($executed) {
                Response::created(['id' => $pdo->lastInsertId()], 'Dépense créée avec succès.');
            } else {
                Response::error('Erreur lors de la création de la dépense.', 500, ['details' => $stmt->errorInfo()]);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Code pour violation de contrainte d'intégrité (ex: duplicata)
                Response::error("Une dépense avec ce nom ('{$data['name']}') existe déjà.", 409); // 409 Conflict
            } else {
                Response::error('Erreur de base de données.', 500, ['details' => $e->getMessage()]);
            }
        }
    },

    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id) {
            Response::badRequest('ID de dépense manquant.');
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['name', 'produit_id', 'quantity', 'price', 'date_depense', 'description', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        try {
            $total = (float)$data['quantity'] * (float)$data['price'];

            $sql = "UPDATE depenses SET 
                        name = :name,
                        user_id = :user_id,
                        produit_id = :produit_id,
                        suppliers_id = :suppliers_id,
                        contrat_id = :contrat_id,
                        quantity = :quantity,
                        price = :price,
                        total = :total,
                        date_depense = :date_depense,
                        description = :description,
                        nature = :nature,
                        category = :category
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $executed = $stmt->execute([
                ':name'         => $data['name'],
                ':user_id'      => $currentUser->user_id,
                ':produit_id'   => $data['produit_id'],
                ':suppliers_id' => $data['suppliers_id'] ?? null,
                ':contrat_id'   => $data['contrat_id'] ?? null,
                ':quantity'     => $data['quantity'],
                ':price'        => $data['price'],
                ':total'        => $total,
                ':date_depense'  => $data['date_depense'],
                ':description'  => $data['description'],
                ':nature'       => $data['nature'],
                ':category'     => $data['category'],
                ':id'           => $id
            ]);

            if ($stmt->rowCount() > 0) {
                Response::success('Dépense mise à jour avec succès.', ['id' => $id]);
            } else {
                Response::notFound('Dépense non trouvée ou aucune modification effectuée.');
            }
        } catch (PDOException $e) {
            Response::error('Erreur de base de données.', 500, ['details' => $e->getMessage()]);
        }
    },

    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id) {
            Response::badRequest('ID de dépense manquant.');
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE depenses SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                Response::success('Dépense supprimée avec succès.', ['id' => $id]);
            } else {
                Response::notFound('Dépense non trouvée.');
            }
        } catch (PDOException $e) {
            Response::error('Erreur de base de données.', 500, ['details' => $e->getMessage()]);
        }
    },
];
