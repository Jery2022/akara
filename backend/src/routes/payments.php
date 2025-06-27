<?php
include './db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function isAuthenticated()
{
    $headers = getallheaders();
    if (! isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return false;
    }

    $jwt        = trim(str_replace('Bearer ', '', $authHeader));
    $secret_key = JWT_SECRET;

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

if (! isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'error'   => 'Accès non autorisé',
        'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Vérification de la connexion à la base de données
if (! $pdo) {
    echo json_encode(['error' => 'Échec de la connexion à la base de données']);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Récupére tous les paiements
        $stmt  = $pdo->query("SELECT * FROM payments");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        // Ajoute un paiement
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['type'], $data['customer_id'], $data['amount'], $data['date'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $description = $data['description'] ?? null;
        $stmt        = $pdo->prepare("INSERT INTO payments (type, customer_id, user_id, contrat_id, description, amount, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['type'],
            $data['customer_id'],
            $user_id,
            $contrat_id,
            $description,
            $data['amount'],
            $data['date'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Paiement ajouté avec succès',
        ]);
        break;

    case 'PUT':
        // Modifie un paiement
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['type'], $data['customer_id'], $data['amount'], $data['date'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $description = $data['description'] ?? null;
        $stmt        = $pdo->prepare("UPDATE payments SET type=?, customer_id=?, user_id=?, contrat_id=?, description=?, amount=?, date=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['type'],
            $data['customer_id'],
            $user_id,
            $contrat_id,
            $description,
            $data['amount'],
            $data['date'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Paiement modifié avec succès',
        ]);
        break;

    case 'DELETE':
        // Supprimer un paiement
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->query("DELETE FROM payments WHERE id = $id");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Paiement supprimé avec succès',
        ]);
        break;

    default:
        // case default pour les méthodes non autorisées
        http_response_code(405);
        echo json_encode(['error' => 'Erreur critique serveur']);
        break;
}
// Ferme la connexion à la base de données
$pdo = null;
