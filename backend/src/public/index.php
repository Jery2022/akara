<?php
require_once __DIR__ . '/../bootstrap.php'; // Inclure le fichier bootstrap pour charger les variables d'environnement
session_start();

// Récupérer l'URI de la requête
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Vérifier si la requête est destinée à l'API
// Si l'URI commence par '/api/', nous ne devons PAS appliquer la logique de redirection des vues.
if (strpos($requestUri, '/api/') === 0) {
    // Laisser la requête être gérée par le routeur de l'API via la configuration Apache.
    // Ce script (index.php du public) ne doit pas intervenir pour les requêtes API.
    // Nous ne faisons rien ici, le script continuera son exécution et Apache le réécrira.
    // La logique CORS est gérée par Nginx (frontend) et le routeur API (backend/api/index.php).
    // Nous ne mettons pas 'exit;' ici car cela empêcherait Apache de réécrire la requête.
} else {
    // Logique de redirection pour les vues traditionnelles (non-API)
    $role = $_SESSION['role'] ?? '';

    // Routing simple pour les vues
    $route = $_GET['route'] ?? '';

    $allowedViews = [
        'users',
        'suppliers',
        'customers',
        'employees',
        'produits',
        'contrats',
        'stock',
        'entrepots',
        'recettes',
        'depenses',
        'payments',
        'achats',
        'factures',
        'quittances',
    ]; // Vues autorisées

    // Si une vue est demandée et que l'utilisateur est connecté, on sert la vue
    if (isset($_SESSION['user_id']) && in_array($route, $allowedViews)) {
        require_once __DIR__ . '/../views/view_' . $route . '.php';
        exit;
    }

    // Si l'utilisateur est connecté sans route, on le redirige vers son dashboard
    if (isset($_SESSION['user_id'])) {
        if ($role === 'admin') {
            header('Location: /admin_dashboard.php');
            exit;
        } elseif ($role === 'employe') {
            header('Location: /employe_dashboard.php');
            exit;
        }
    }

    // Sinon, page de login
    header('Location: /login.php');
    exit;
}
