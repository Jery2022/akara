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

    // Filtrage et tri des paiements
    $typeFilter     = $_GET['type'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'customer_id';
    $order          = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'contrat_id', 'user_id', 'customer_id', 'amount'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'customer_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validTypes      = ['virement', 'chèque', 'espèces'];
    $validCategories = ['travaux', 'services'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM payments WHERE 1=1";
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
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des paiements.</div>';
    }

?>
<title>Gestion des Paiements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<div class="container my-4">
    <h2>Liste des paiements</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow p-3" >
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
                <option value="travaux"
                  <?php echo($categoryFilter === "travaux") ? 'selected' : ''; ?>>Travaux</option>
                <option value="services"
                  <?php echo($categoryFilter === "services") ? 'selected' : ''; ?>>Services</option>
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

    <!-- Tableau des paiements -->
    <div class="bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Contrat</th>
                <th>Reçu par</th>
                <th>Client</th>
                <th>Montant</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Date paiement</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" class="text-center">Aucun paiement trouvé.</td>
                </tr>
            <?php else: ?>
<?php foreach ($payments as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']) ?></td>
                        <td><?php echo htmlspecialchars($row['contrat_id']) ?></td>
                        <td><?php echo htmlspecialchars($row['user_id']) ?></td>
                        <td><?php echo htmlspecialchars($row['customer_id']) ?></td>
                        <td><?php echo htmlspecialchars($row['amount']) ?></td>
                        <td><?php echo htmlspecialchars($row['type']) ?></td>
                        <td><?php echo htmlspecialchars($row['category']) ?></td>
                        <td><?php echo htmlspecialchars($row['date_payment']) ?></td>
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
