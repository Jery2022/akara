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
        // Récupérer tous les fournisseurs
        $result = $conn->query("SELECT * FROM suppliers");
        $items  = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        echo json_encode($items);
        break;

    case 'POST':
        // Ajouter un fournisseur
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $stmt       = $conn->prepare("INSERT INTO suppliers (name, refContact, phone, email, contrat_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $data['name'], $data['refContact'], $data['phone'], $data['email'], $contrat_id);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        break;

    case 'PUT':
        // Modifier un fournisseur
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $stmt       = $conn->prepare("UPDATE suppliers SET name=?, refContact=?, phone=?, email=?, contrat_id=? WHERE id=?");
        $stmt->bind_param("ssssii", $data['name'], $data['refContact'], $data['phone'], $data['email'], $contrat_id, $data['id']);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Supprimer un fournisseur
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $conn->query("DELETE FROM suppliers WHERE id = $id");
        echo json_encode(['success' => true]);
        break;

    default:
        // case default pour les méthodes non autorisées
        http_response_code(405);
        echo json_encode(['error' => 'Erreur critique serveur']);
        break;
}
