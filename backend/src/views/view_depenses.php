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

    // Filtrage et tri des dépenses
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
    $validNatures    = ['achat', 'location'];
    $validCategories = ['fournitures', 'équipement', 'services', 'maintenance', 'logistique'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM depenses WHERE 1=1";
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
        $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des dépenses.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion des Dépenses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des dépenses</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="nature" class="form-select">
                <option value="">Toutes les natures</option>
                <option value="achat"
                  <?php echo($natureFilter === "achat") ? 'selected' : ''; ?>>Achat</option>
                <option value="location"
                  <?php echo($natureFilter === "location") ? 'selected' : ''; ?>>Location</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <option value="fournitures"
                  <?php echo($categoryFilter === "fournitures") ? 'selected' : ''; ?>>Fournitures</option>
                <option value="équipement"
                  <?php echo($categoryFilter === "équipement") ? 'selected' : ''; ?>>Équipement</option>
                <option value="services"                                         .
                  <?php echo($categoryFilter === "services") ? 'selected' : ''; ?>>Services</option>
                <option value="maintenance"
                  <?php echo($categoryFilter === "maintenance") ? 'selected' : ''; ?>>Maintenance</option>
                <option value="logistique"
                  <?php echo($categoryFilter === "logistique") ? 'selected' : ''; ?>>Logistique</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"
                  <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"
                  <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <!-- Tableau des dépenses -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix</th>
                <th>Total</th>
                <th>Fournisseur</th>
                <th>Nature</th>
                <th>Catégorie</th>
                <th>Date Dépense</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($depenses)): ?>
                <tr>
                    <td colspan="9" class="text-center">Aucune dépense trouvée.</td>
                </tr>
            <?php else: ?>
<?php foreach ($depenses as $row): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                        <td data-label="Produit"><?php echo htmlspecialchars($row['produit_id']) ?></td>
                        <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']) ?></td>
                        <td data-label="Prix"><?php echo htmlspecialchars($row['price']) ?></td>
                        <td data-label="Total"><?php echo htmlspecialchars($row['total']) ?></td>
                        <td data-label="Fournisseur"><?php echo htmlspecialchars($row['suppliers_id']) ?></td>
                        <td data-label="Nature"><?php echo htmlspecialchars($row['nature']) ?></td>
                        <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']) ?></td>
                        <td data-label="Date Dépense"><?php echo htmlspecialchars($row['date_depense']) ?></td>
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
