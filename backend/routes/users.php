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
        $stmt  = $pdo->query("SELECT * FROM users");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['email'], $data['password'], $data['role'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide']);
            exit;
        }

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql           = "INSERT INTO users (email, password, role, statut) VALUES (?, ?, ?, ?)";
        $stmt          = $pdo->prepare($sql);
        $stmt->execute([
            $data['email'],
            $password_hash,
            $data['role'],
            'actif',
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
        ]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['email'], $data['password'], $data['role'], $data['statut'])) {
            echo json_encode(['error' => 'Champs manquants']);
            exit;
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide']);
            exit;
        }

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql           = "UPDATE users SET email=?, password=?, role=?, statut=? WHERE id=?";
        $stmt          = $pdo->prepare($sql);
        $stmt->execute([
            $data['email'],
            $password_hash,
            $data['role'],
            $data['statut'],
            $data['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
