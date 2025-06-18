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
        // Récupérer toutes les sorties de stock
        $result = $conn->query("SELECT * FROM sortieStock");
        $items  = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'POST':
        // Ajouter une sortie de stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['quantity'], $data['date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $customer_id = $data['customer_id'] ?? null;
        $motif       = $data['motif'] ?? null;
        $stmt        = $conn->prepare("INSERT INTO sortieStock (produit_id, quantity, date, user_id, customer_id, entrepot_id, motif) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiiss", $data['produit_id'], $data['quantity'], $data['date'], $user_id, $customer_id, $data['entrepot_id'], $motif);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        break;

    case 'PUT':
        // Modifier une sortie de stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['quantity'], $data['date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $customer_id = $data['customer_id'] ?? null;
        $motif       = $data['motif'] ?? null;
        $stmt        = $conn->prepare("UPDATE sortieStock SET produit_id=?, quantity=?, date=?, user_id=?, customer_id=?, entrepot_id=?, motif=? WHERE id=?");
        $stmt->bind_param("iisiissi", $data['produit_id'], $data['quantity'], $data['date'], $user_id, $customer_id, $data['entrepot_id'], $motif, $data['id']);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Supprimer une sortie de stock
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $conn->query("DELETE FROM sortieStock WHERE id = $id");
        echo json_encode(['success' => true]);
        break;

    default:
        // case default pour les méthodes non autorisées
        http_response_code(405);
        echo json_encode(['error' => 'Erreur critique serveur']);
        break;
}
