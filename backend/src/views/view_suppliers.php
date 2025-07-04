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

    // Filtrage et tri des fournisseurs
    $statusFilter     = $_GET['status'] ?? '';
    $black_listFilter = $_GET['black_list'] ?? '';
    $sortBy           = $_GET['sort_by'] ?? 'name';
    $order            = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validStatuses  = ['sérieux', 'à risque', 'à suivre'];
    $validBlackList = ['oui', 'non'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM suppliers WHERE 1=1";
    $params = [];

    if ($black_listFilter && in_array($black_listFilter, $validBlackList)) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des fournisseurs.</div>';
    }

?>
<title>Gestion des Fournisseurs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des fournisseurs</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
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
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="sérieux"
                  <?php echo($statusFilter === "sérieux") ? 'selected' : ''; ?>>Sérieux</option>
                <option value="à risque"
                  <?php echo($statusFilter === "à risque") ? 'selected' : ''; ?>>À risque</option>
                <option value="à suivre"
                  <?php echo($statusFilter === "à suivre") ? 'selected' : ''; ?>>À suivre</option>
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

    <!-- Tableau des fournisseurs -->
    <div class="table-container bg-dark-subtle shadow gap-3 p-3 ">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Contact</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Statut</th>
            <th>Bannis</th>
            <th>ID Contrat</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($suppliers)): ?>
            <tr>
                <td colspan="8" class="text-center">Aucun fournisseur trouvé.</td>
            </tr>
        <?php else: ?>
<?php foreach ($suppliers as $row): ?>
                <tr>
                    <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                    <td data-label="Nom"><?php echo htmlspecialchars($row['name']) ?></td>
                    <td data-label="Contact"><?php echo htmlspecialchars($row['refContact']) ?></td>
                    <td data-label="Téléphone"><?php echo htmlspecialchars($row['phone']) ?></td>
                    <td data-label="E-mail"><?php echo htmlspecialchars($row['email']) ?></td>
                    <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                    <td data-label="Banis"><?php echo htmlspecialchars($row['black_list']) ?></td>
                    <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_id']) ?></td>
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
