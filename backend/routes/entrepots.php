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
        // Récupére tous les entrepôts
        $stmt  = $pdo->query("SELECT * FROM entrepots");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        // Ajoute un entrepôt
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['nom'])) {
            echo json_encode(['error' => 'Le nom est obligatoire']);
            exit;
        }
        $adresse     = $data['adresse'] ?? null;
        $responsable = $data['responsable'] ?? null;
        $stmt        = $pdo->prepare("INSERT INTO entrepots (nom, adresse, responsable) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data['nom'], $adresse, $responsable);
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['nom'],
            $adresse,
            $responsable,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
        ]);
        break;

    case 'PUT':
        // Modifie un entrepôt
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['nom'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $adresse     = $data['adresse'] ?? null;
        $responsable = $data['responsable'] ?? null;
        $stmt        = $pdo->prepare("UPDATE entrepots SET nom=?, adresse=?, responsable=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['nom'],
            $adresse,
            $responsable,
            $data['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Supprime un entrepôt
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM entrepots WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Entrepôt supprimé avec succès',
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
