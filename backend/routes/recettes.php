<?php
include './db.php';
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (! $conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Récupérer toutes les recettes
        $result = $conn->query("SELECT * FROM recettes");
        $items  = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'POST':
        // Ajouter une recette
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['customer_id'], $data['quantity'], $data['total'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $date       = $data['date'] ?? date('Y-m-d');
        $stmt       = $conn->prepare("INSERT INTO recettes (produit_id, customer_id, contrat_id, quantity, total, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiids", $data['produit_id'], $data['customer_id'], $contrat_id, $data['quantity'], $data['total'], $date);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        break;

    case 'PUT':
        // Modifier une recette
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['customer_id'], $data['quantity'], $data['total'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $date       = $data['date'] ?? date('Y-m-d');
        $stmt       = $conn->prepare("UPDATE recettes SET produit_id=?, customer_id=?, contrat_id=?, quantity=?, total=?, date=? WHERE id=?");
        $stmt->bind_param("iiiidsi", $data['produit_id'], $data['customer_id'], $contrat_id, $data['quantity'], $data['total'], $date, $data['id']);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Supprimer une recette
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $conn->query("DELETE FROM recettes WHERE id = $id");
        echo json_encode(['success' => true]);
        break;

    default:
        // case default pour les méthodes non autorisées
        http_response_code(405);
        echo json_encode(['error' => 'Erreur critique serveur']);
        break;
}
