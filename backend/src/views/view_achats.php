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

    // Filtrage et tri des achats
    $typeFilter     = $_GET['type'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'suppliers_id';
    $order          = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'contrat_id', 'user_id', 'suppliers_id', 'amount'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'suppliers_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validTypes      = ['virement', 'chèque', 'espèces'];
    $validCategories = ['fournitures', 'équipement', 'services', 'maintenance', 'logistique'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM achats WHERE 1=1";
    $params = [];

    if ($typeFilter && in_array($typeFilter, $validTypes)) {
        $query .= " AND type = ?";
        $params[] = $typeFilter;
    }

    if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
        $query .= " AND category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des achats.</div>';
    }

?>
    <title>Gestion Achats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des achats</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <option value="virement"
                  <?php echo($typeFilter === "virement") ? 'selected' : ''; ?>>Virement</option>
                <option value="chèque"
                  <?php echo($typeFilter === "chèque") ? 'selected' : ''; ?>>Chèque</option>
                <option value="espèces"
                  <?php echo($typeFilter === "espèces") ? 'selected' : ''; ?>>Espèces</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <option value="fournitures"
                  <?php echo($categoryFilter === "fournitures") ? 'selected' : ''; ?>>Fournitures</option>
                <option value="équipement"
                  <?php echo($categoryFilter === "équipement") ? 'selected' : ''; ?>>Équipement</option>
                <option value="services"
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

    <!-- Tableau des achats -->
    <div class="table-container bg-dark-subtle shadow p-3">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contrat</th>
                    <th>Reçu par</th>
                    <th>Fournisseur</th>
                    <th>Montant</th>
                    <th>Type</th>
                    <th>Catégorie</th>
                    <th>Date achat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($achats)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Aucun achat trouvé.</td>
                    </tr>
                <?php else: ?>
<?php foreach ($achats as $row): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                            <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_id'] || '') ?></td>
                            <td data-label="Reçu par"><?php echo htmlspecialchars($row['user_id']) ?></td>
                            <td data-label="Fournisseur"><?php echo htmlspecialchars($row['suppliers_id']) ?></td>
                            <td data-label="Montant"><?php echo htmlspecialchars($row['amount']) ?></td>
                            <td data-label="Type"><?php echo htmlspecialchars($row['type']) ?></td>
                            <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']) ?></td>
                            <td data-label="Date achat"><?php echo htmlspecialchars($row['date_achat']) ?></td>
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
