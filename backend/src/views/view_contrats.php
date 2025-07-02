<?php
    session_start();
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once '../../db.php';
    require_once 'partials/_header.php';

    $pdo = getPDO();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Filtrage et tri des contrats
    $typeFilter   = $_GET['type'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    $sortBy       = $_GET['sort_by'] ?? 'montant';
    $order        = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['montant', 'id', 'date_debut', 'date_fin', 'status', 'type'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'montant'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validTypes    = ['client', 'fournisseur', 'employe'];
    $validStatuses = ['en cours', 'terminé', 'annulé'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM contrats WHERE 1=1";
    $params = [];

    if ($typeFilter && in_array($typeFilter, $validTypes)) {
        $query .= " AND type = ?";
        $params[] = $typeFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $contrats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des contrats.</div>';
    }

    // Affichage du titre et du CSS
?>
<title>Gestion Contrats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des contrats</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <option value="client"
                    <?php echo($typeFilter === 'client') ? 'selected' : ''; ?>>Client</option>
                <option value="fournisseur"
                    <?php echo($typeFilter === 'fournisseur') ? 'selected' : ''; ?>>Fournisseur</option>
                <option value="employe"
                    <?php echo($typeFilter === 'employe') ? 'selected' : ''; ?>>Employé</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="en cours"
                    <?php echo($statusFilter === "en cours") ? 'selected' : ''; ?>>En cours</option>
                <option value="terminé"
                    <?php echo($statusFilter === 'terminé') ? 'selected' : ''; ?>>Terminé</option>
                <option value="annulé"
                    <?php echo($statusFilter === 'annulé') ? 'selected' : ''; ?>>Annulé</option>
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

    <!-- Tableau des contrats -->
     <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Référence</th>
                <th>Objet</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Statut</th>
                <th>Montant</th>
                <th>Signé le</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($contrats)): ?>
                <tr>
                    <td colspan="10" class="text-center">Aucun contrat trouvé.</td>
                </tr>
            <?php else: ?>
<?php foreach ($contrats as $row): ?>
                    <tr>
                        <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                        <td data-label="Référence"><?php echo htmlspecialchars($row['ref']) ?></td>
                        <td data-label="Objet"><?php echo htmlspecialchars($row['objet']) ?></td>
                        <td data-label="Date début"><?php echo htmlspecialchars($row['date_debut']) ?></td>
                        <td data-label="Date fin"><?php echo htmlspecialchars($row['date_fin']) ?></td>
                        <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                        <td data-label="Montant"><?php echo htmlspecialchars($row['montant']) ?></td>
                        <td data-label="Signé le"><?php echo htmlspecialchars($row['date_signature']) ?></td>
                        <td data-label="Type"><?php echo htmlspecialchars($row['type']) ?></td>
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
