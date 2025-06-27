<?php
    session_start();
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once '../db.php';
    require_once 'partials/_header.php';

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Filtrage et tri des produits
    $provenanceFilter    = $_GET['provenance'] ?? '';
    $disponibilityFilter = $_GET['disponibility'] ?? '';
    $sortBy              = $_GET['sort_by'] ?? 'name';
    $order               = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'price', 'supplier_id', 'entrepot_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validProvenances     = ['local', 'etranger'];
    $validDisponibilities = ['oui', 'non'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM produits WHERE 1=1";
    $params = [];

    if ($provenanceFilter && in_array($provenanceFilter, $validProvenances)) {
        $query .= " AND provenance = ?";
        $params[] = $provenanceFilter;
    }

    if ($disponibilityFilter && in_array($disponibilityFilter, $validDisponibilities)) {
        $query .= " AND disponibility = ?";
        $params[] = $disponibilityFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des produits.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion Produits</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des produits</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="provenance" class="form-select">
                <option value="">Toutes les provenances</option>
                <option value="local"
                  <?php echo($provenanceFilter === "local") ? 'selected' : ''; ?>>Local</option>
                <option value="etranger"
                  <?php echo($provenanceFilter === "etranger") ? 'selected' : ''; ?>>Etranger</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="disponibility" class="form-select">
                <option value="">Toutes les disponibilités</option>
                <option value="oui"
                  <?php echo($disponibilityFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"
                  <?php echo($disponibilityFilter === "non") ? 'selected' : ''; ?>>Non</option>
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

    <!-- Tableau des produits -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Unité</th>
            <th>Prix</th>
            <th>Provenance</th>
            <th>Disponible</th>
            <th>Délai</th>
            <th>Catégorie</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($produits)): ?>
            <tr>
                <td colspan="9" class="text-center">Aucun produit trouvé.</td>
            </tr>
        <?php else: ?>
<?php foreach ($produits as $row): ?>
                <tr>
                    <td data-label="ID">><?php echo htmlspecialchars($row['id']); ?></td>
                    <td data-label="Nom">><?php echo htmlspecialchars($row['name']); ?></td>
                    <td data-label="Description">><?php echo htmlspecialchars($row['description']); ?></td>
                    <td data-label="Unité">><?php echo htmlspecialchars($row['unit']); ?></td>
                    <td data-label="Prix">><?php echo htmlspecialchars($row['price']); ?></td>
                    <td data-label="Provenance">><?php echo htmlspecialchars($row['provenance']); ?></td>
                    <td data-label="Disponible">><?php echo htmlspecialchars($row['disponibility']); ?></td>
                    <td data-label="Délai">><?php echo htmlspecialchars($row['delai_livraison']); ?></td>
                    <td data-label="Catégorie">><?php echo htmlspecialchars($row['category']); ?></td>
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
