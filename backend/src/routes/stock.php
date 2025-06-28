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
        // Récupérer tous les stocks
        $result = $conn->query("SELECT * FROM stock");
        $items  = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'POST':
        // Ajouter un stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['quantity'], $data['unit'], $data['min'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $supplier_id = $data['supplier_id'] ?? null;
        $stmt        = $conn->prepare("INSERT INTO stock (produit_id, quantity, unit, min, supplier_id, entrepot_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiii", $data['produit_id'], $data['quantity'], $data['unit'], $data['min'], $supplier_id, $data['entrepot_id']);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        break;

    case 'PUT':
        // Modifier un stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['quantity'], $data['unit'], $data['min'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $supplier_id = $data['supplier_id'] ?? null;
        $stmt        = $conn->prepare("UPDATE stock SET produit_id=?, quantity=?, unit=?, min=?, supplier_id=?, entrepot_id=? WHERE id=?");
        $stmt->bind_param("iisiiii", $data['produit_id'], $data['quantity'], $data['unit'], $data['min'], $supplier_id, $data['entrepot_id'], $data['id']);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Supprimer un stock
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $conn->query("DELETE FROM stock WHERE id = $id");
        echo json_encode(['success' => true]);
        break;

    default:
        // case default pour les méthodes non autorisées
        http_response_code(405);
        echo json_encode(['error' => 'Erreur critique serveur']);
        break;
}
