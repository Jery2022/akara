<?php
    session_start();
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

    // Filtrage et tri des stocks
    $rentabilityFilter    = $_GET['rentability'] ?? '';
    $classificationFilter = $_GET['classification'] ?? '';
    $sortBy               = $_GET['sort_by'] ?? 'min';
    $order                = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'min', 'quantity', 'produit_id', 'supplier_id', 'entrepot_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'min'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validRentabilities   = ['forte', 'bonne', 'faible'];
    $validClassifications = ['A', 'B', 'C'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM stock WHERE 1=1";
    $params = [];

    if ($rentabilityFilter && in_array($rentabilityFilter, $validRentabilities)) {
        $query .= " AND rentability = ?";
        $params[] = $rentabilityFilter;
    }

    if ($classificationFilter && in_array($classificationFilter, $validClassifications)) {
        $query .= " AND classification = ?";
        $params[] = $classificationFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des stocks.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion Stock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des stocks</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="rentability" class="form-select">
                <option value="">Toutes les rentabilités</option>
                <option value="forte"                                                                                                                                                                                                                                                                                                         <?php echo($rentabilityFilter === "forte") ? 'selected' : ''; ?>>Forte</option>
                <option value="bonne"                                                                                                                                                                                                                                                                                                         <?php echo($rentabilityFilter === "bonne") ? 'selected' : ''; ?>>Bonne</option>
                <option value="faible"                                                                                                                                                                                                                                                                                                                 <?php echo($rentabilityFilter === "faible") ? 'selected' : ''; ?>>Faible</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="classification" class="form-select">
                <option value="">Toutes les classifications</option>
                <option value="A"                                                                                                                                                                                                                                                                         <?php echo($classificationFilter === "A") ? 'selected' : ''; ?>>Classification A</option>
                <option value="B"                                                                                                                                                                                                                                                                         <?php echo($classificationFilter === "B") ? 'selected' : ''; ?>>Classification B</option>
                <option value="C"                                                                                                                                                                                                                                                                         <?php echo($classificationFilter === "C") ? 'selected' : ''; ?>>Classification C</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                         <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                 <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <!-- Tableau des stocks -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
                <th>Qté actuelle</th>
                <th>Unité</th>
                <th>Alerte</th>
                <th>Rentabilité</th>
                <th>Classification</th>
                <th>ID fournisseur</th>
                <th>ID entrepôt</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stock)): ?>
                <tr>
                    <td colspan="9" class="text-center">Aucun stock trouvé.</td>
                </tr>
            <?php else: ?>
<?php foreach ($stock as $row): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                        <td data-label="Libellé"><?php echo htmlspecialchars($row['produit_id']) ?></td>
                        <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']) ?></td>
                        <td data-label="Unité"><?php echo htmlspecialchars($row['unit']) ?></td>
                        <td data-label="Seuil"><?php echo htmlspecialchars($row['min']) ?></td>
                        <td data-label="Rentabilité"><?php echo htmlspecialchars($row['rentability']) ?></td>
                        <td data-label="Classification"><?php echo htmlspecialchars($row['classification']) ?></td>
                        <td data-label="ID fournisseur"><?php echo htmlspecialchars($row['supplier_id']) ?></td>
                        <td data-label="ID entrepôt"><?php echo htmlspecialchars($row['entrepot_id']) ?></td>
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
