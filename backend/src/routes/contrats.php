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
        // Récupère tous les contrats
        $stmt  = $pdo->query("SELECT * FROM contrats");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        break;

    case 'POST':
        // Ajoute un contrat
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['ref'], $data['objet'], $data['date_debut'], $data['date_fin'], $data['montant'], $data['type'], $data['date_signature'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $status          = $data['status'] ?? 'en cours';
        $signataire      = $data['signataire'] ?? null;
        $fichier_contrat = $data['fichier_contrat'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO contrats (ref, objet, date_debut, date_fin, status, montant, signataire, date_signature, fichier_contrat, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['ref'],
            $data['objet'],
            $data['date_debut'],
            $data['date_fin'],
            $status,
            $data['montant'],
            $signataire,
            $data['date_signature'],
            $fichier_contrat,
            $data['type'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Contrat ajouté avec succès',
        ]);
        break;

    case 'PUT':
        // Modifie un contrat
        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['ref'], $data['objet'], $data['date_debut'], $data['date_fin'], $data['montant'], $data['type'], $data['date_signature'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $status          = $data['status'] ?? 'en cours';
        $signataire      = $data['signataire'] ?? null;
        $fichier_contrat = $data['fichier_contrat'] ?? null;

        $stmt = $pdo->prepare("UPDATE contrats SET ref=?, objet=?, date_debut=?, date_fin=?, status=?, montant=?, signataire=?, date_signature=?, fichier_contrat=?, type=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['ref'],
            $data['objet'],
            $data['date_debut'],
            $data['date_fin'],
            $status,
            $data['montant'],
            $signataire,
            $data['date_signature'],
            $fichier_contrat,
            $data['type'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Contrat modifié avec succès',
        ]);
        break;

    case 'DELETE':
        // Supprime un contrat
        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM contrats WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Contrat supprimé avec succès',
        ]);
        break;

    default:
        // Méthode non autorisée
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}
// Ferme la connexion à la base de données
$pdo = null;
