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
        // Récupère toutes les recettes
        $stmt  = $pdo->query("SELECT * FROM drpenses");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        // Ajoute une recette
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['supplier_id'], $data['quantity'], $data['price'], $data['nature'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $date        = $data['date_depense'] ?? date('d-m-Y');
        $description = $data['description'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO depenses (user_id, produit_id, supplier_id, contrat_id, quantity, price, date_depense, description, nature, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['user_id'],
            $data['produit_id'],
            $data['supplier_id'],
            $contrat_id,
            $data['quantity'],
            $data['price'],
            $date,
            $description,
            $data['nature'],
            $data['category'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Dépense ajoutée avec succès',
        ]);
        break;

    case 'PUT':
        // Modifier une recette
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['suppliers_id'], $data['quantity'], $data['price'], $data['nature'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $date        = $data['date_recette'] ?? date('d-m-Y');
        $description = $data['description'] ?? null;

        $stmt = $pdo->prepare("UPDATE recettes SET user_id=?,
            produit_id=?,
            supplier_id=?,
            contrat_id=?,
            quantity=?,
            price=?,
            date_depense=?,
            description=?,
            nature=?,
            category=? WHERE id=?"
        );

        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['user_id'],
            $data['produit_id'],
            $data['supplier_id'],
            $contrat_id,
            $data['quantity'],
            $data['price'],
            $date,
            $description,
            $data['nature'],
            $data['category'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Dépense modifiée avec succès',
        ]);
        break;

    case 'DELETE':
        // Supprime une recette
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM depense WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Dépense supprimée avec succès',
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
