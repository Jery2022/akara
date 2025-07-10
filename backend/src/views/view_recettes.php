<?php
    // session_start();

    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once __DIR__ . '/../db.php';
    require_once __DIR__ . '/partials/_header.php';

    $pdo = getPDO();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Filtrage et tri des recettes
    $natureFilter   = $_GET['nature'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'produit_id';
    $order          = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'produit_id', 'user_id', 'customer_id', 'contrat_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'produit_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validNatures    = ['vente', 'location'];
    $validCategories = ['construction', 'sécurité', 'hygiène', 'entretien', 'logistique', 'mobilité'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM recettes WHERE 1=1";
    $params = [];

    if ($natureFilter && in_array($natureFilter, $validNatures)) {
        $query .= " AND nature = ?";
        $params[] = $natureFilter;
    }

    if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
        $query .= " AND category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des recettes.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion des Recettes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des recettes</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="nature" class="form-select">
                <option value="">Toutes les natures</option>
                <option value="vente"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      <?php echo($natureFilter === "vente") ? 'selected' : ''; ?>>Vente</option>
                <option value="location"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo($natureFilter === "location") ? 'selected' : ''; ?>>Location</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <option value="construction"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($categoryFilter === "construction") ? 'selected' : ''; ?>>Construction</option>
                <option value="sécurité"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo($categoryFilter === "sécurité") ? 'selected' : ''; ?>>Sécurité</option>
                <option value="hygiène"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo($categoryFilter === "hygiène") ? 'selected' : ''; ?>>Hygiène</option>
                <option value="entretien"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          <?php echo($categoryFilter === "entretien") ? 'selected' : ''; ?>>Entretien</option>
                <option value="logistique"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           <?php echo($categoryFilter === "logistique") ? 'selected' : ''; ?>>Logistique</option>
                <option value="mobilité"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          <?php echo($categoryFilter === "mobilité") ? 'selected' : ''; ?>>Mobilité</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <!-- Tableau des recettes -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix</th>
                <th>Total</th>
                <th>Client</th>
                <th>Nature</th>
                <th>Catégorie</th>
                <th>Date Vente</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recettes)): ?>
                <tr>
                    <td colspan="9" class="text-center">Aucune recette trouvée.</td>
                </tr>
            <?php else: ?>
<?php foreach ($recettes as $row): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                        <td data-label="Produit"><?php echo htmlspecialchars($row['produit_id']) ?></td>
                        <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']) ?></td>
                        <td data-label="Prix"><?php echo htmlspecialchars($row['price']) ?></td>
                        <td data-label="Total"><?php echo htmlspecialchars($row['total']) ?></td>
                        <td data-label="Client"><?php echo htmlspecialchars($row['customer_id']) ?></td>
                        <td data-label="Nature"><?php echo htmlspecialchars($row['nature']) ?></td>
                        <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']) ?></td>
                        <td data-label="Date Vente"><?php echo htmlspecialchars($row['date_recette']) ?></td>
                    </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</div>
</main>
<?php
    require_once 'partials/_footer.php';
?>
