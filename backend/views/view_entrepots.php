<?php
    session_start();
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../admin_login.php');
        exit;
    }

    require_once '../db.php';
    require_once 'partials/_header.php';

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Filtrage et tri des entrepôts
    $quality_stockageFilter = $_GET['quality_stockage'] ?? '';
    $black_listFilter       = $_GET['black_list'] ?? '';
    $sortBy                 = $_GET['sort_by'] ?? 'name';
    $order                  = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email', 'capacity'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validBlackList       = ['oui', 'non'];
    $validQualityStockage = ['bonne', 'moyenne', 'mauvaise'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM entrepots WHERE 1=1";
    $params = [];

    if ($black_listFilter && in_array($black_listFilter, $validBlackList)) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($quality_stockageFilter && in_array($quality_stockageFilter, $validQualityStockage)) {
        $query .= " AND quality_stockage = ?";
        $params[] = $quality_stockageFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des entrepôts.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion Entrepôts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<div class="container my-4">
    <h2>Liste des entrepôts</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow p-3">
        <div class="col-md-3">
            <select name="black_list" class="form-select">
                <option value="">Tous les black-listés ou non</option>
                <option value="oui"
                  <?php echo($black_listFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"
                  <?php echo($black_listFilter === "non") ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="quality_stockage" class="form-select">
                <option value="">Toutes les qualités de stockage</option>
                <option value="bonne"
                  <?php echo($quality_stockageFilter === "bonne") ? 'selected' : ''; ?>>Bonne</option>
                <option value="moyenne"
                  <?php echo($quality_stockageFilter === "moyenne") ? 'selected' : ''; ?>>Moyenne</option>
                <option value="mauvaise"
                  <?php echo($quality_stockageFilter === "mauvaise") ? 'selected' : ''; ?>>Mauvaise</option>
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

    <!-- Tableau des entrepôts -->
<div class="bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Responsable</th>
                <th>E-mail</th>
                <th>Téléphone</th>
                <th>Capacité</th>
                <th>Stockage</th>
                <th>Bannis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entrepots)): ?>
                <tr>
                    <td colspan="9" class="text-center">Aucun entrepôt trouvé.</td>
                </tr>
            <?php else: ?>
<?php foreach ($entrepots as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']) ?> </td>
                        <td><?php echo htmlspecialchars($row['name']) ?> </td>
                        <td><?php echo htmlspecialchars($row['adresse']) ?> </td>
                        <td><?php echo htmlspecialchars($row['responsable']) ?> </td>
                        <td><?php echo htmlspecialchars($row['email']) ?> </td>
                        <td><?php echo htmlspecialchars($row['telephone']) ?> </td>
                        <td><?php echo htmlspecialchars($row['capacity']) ?> </td>
                        <td><?php echo htmlspecialchars($row['quality_stockage']) ?> </td>
                        <td><?php echo htmlspecialchars($row['black_list']) ?> </td>
                    </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</div>
</div>
<?php
    require_once 'partials/_footer.php';
?>
