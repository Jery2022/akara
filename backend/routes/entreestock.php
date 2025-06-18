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
        // Récupére toutes les entrées de stock
        $stmt  = $pdo->query("SELECT * FROM entreeStock");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        // Ajoute une entrée de stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['quantity'], $data['ref_date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id      = $data['user_id'] ?? null;
        $suppliers_id = $data['suppliers_id'] ?? null;
        $motif        = $data['motif'] ?? null;
        $stmt         = $pdo->prepare("INSERT INTO entreeStock (produit_id, quantity, ref_date, user_id, suppliers_id, entrepot_id, motif) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['produit_id'],
            $data['quantity'],
            $data['ref_date'],
            $user_id,
            $suppliers_id,
            $data['entrepot_id'],
            $motif,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
        ]);
        break;

    case 'PUT':
        // Modifie une entrée de stock
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['quantity'], $data['ref_date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id      = $data['user_id'] ?? null;
        $suppliers_id = $data['suppliers_id'] ?? null;
        $motif        = $data['motif'] ?? null;
        $stmt         = $pdo->prepare("UPDATE entreeStock SET produit_id=?, quantity=?, ref_date=?, user_id=?, suppliers_id=?, entrepot_id=?, motif=? WHERE id=?");
        $stmt->execute([
            $data['produit_id'],
            $data['quantity'],
            $data['ref_date'],
            $user_id,
            $suppliers_id,
            $data['entrepot_id'],
            $motif,
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
        ]);
        break;

    case 'DELETE':
        // Supprime une entrée de stock
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->query("DELETE FROM entreeStock WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Entrée de stock supprimée avec succès',
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
