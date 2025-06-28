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

    // Filtrage et tri des employés
    $statusFilter  = $_GET['status'] ?? '';
    $qualityFilter = $_GET['quality'] ?? '';
    $sortBy        = $_GET['sort_by'] ?? 'name';
    $order         = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email', 'fonction', 'category', 'salary'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validStatuses  = ['actif', 'inactif'];
    $validQualities = ['ouvrier', 'technicien', 'ingenieur', 'ceo'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM employees WHERE 1=1";
    $params = [];

    if ($qualityFilter && in_array($qualityFilter, $validQualities)) {
        $query .= " AND quality = ?";
        $params[] = $qualityFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des employés.</div>';
    }
?>

<title>Gestion des Employés</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des employés</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="quality" class="form-select">
                <option value="">Toutes les qualifications</option>
                <option value="ouvrier"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($qualityFilter === "ouvrier") ? 'selected' : ''; ?>>Ouvrier</option>
                <option value="technicien"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo($qualityFilter === "technicien") ? 'selected' : ''; ?>>Technicien</option>
                <option value="ingenieur"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($qualityFilter === "ingenieur") ? 'selected' : ''; ?>>Ingénieur</option>
                <option value="ceo"                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($qualityFilter === "ceo") ? 'selected' : ''; ?>>CEO</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="actif"                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($statusFilter === "actif") ? 'selected' : ''; ?>>Actif</option>
                <option value="inactif"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($statusFilter === "inactif") ? 'selected' : ''; ?>>Inactif</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <!-- Tableau des employés -->
     <div class="table-container bg-dark-subtle shadow p-3 ">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Fonction</th>
            <th>Salaire</th>
            <th>Téléphone</th>
            <th>E-mail</th>
            <th>Statut</th>
            <th>Qualité</th>
            <th>ID Contrat</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($employees)): ?>
            <tr>
                <td colspan="9" class="text-center">Aucun employé trouvé.</td>
            </tr>
        <?php else: ?>
<?php foreach ($employees as $row): ?>
                <tr>
                    <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                    <td data-label="Nom"><?php echo htmlspecialchars($row['name']) ?></td>
                    <td data-label="Fonction"><?php echo htmlspecialchars($row['fonction']) ?></td>
                    <td data-label="Salaire"><?php echo htmlspecialchars($row['salary']) ?></td>
                    <td data-label="Téléphone"><?php echo htmlspecialchars($row['phone']) ?></td>
                    <td data-label="E-mail"><?php echo htmlspecialchars($row['email']) ?></td>
                    <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                    <td data-label="Qualité"><?php echo htmlspecialchars($row['quality']) ?></td>
                    <td data-label="ID Contrat"><?php echo htmlspecialchars($row['contrat_id'] ?? '') ?></td>
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
