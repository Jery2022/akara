<?php
    session_start();
    if (! isset($_SESSION['admin'])) {
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

    // Construction de la requête SQL
    $query  = "SELECT * FROM contrats WHERE 1=1";
    $params = [];

    if ($typeFilter) {
        $query .= " AND type = ?";
        $params[] = $typeFilter;
    }

    if ($statusFilter) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $contrats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<title>Gestion Contrats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2>Liste des contrats</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row g-5 mb-4 mt-3">
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
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Référence</th>
                <th>Objet</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Status</th>
                <th>Montant</th>
                <th>Signataire</th>
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
                        <td><?php echo htmlspecialchars($row['id']) ?></td>
                        <td><?php echo htmlspecialchars($row['ref']) ?></td>
                        <td><?php echo htmlspecialchars($row['objet']) ?></td>
                        <td><?php echo htmlspecialchars($row['date_debut']) ?></td>
                        <td><?php echo htmlspecialchars($row['date_fin']) ?></td>
                        <td><?php echo htmlspecialchars($row['status']) ?></td>
                        <td><?php echo htmlspecialchars($row['montant']) ?></td>
                        <td><?php echo htmlspecialchars($row['signataire']) ?></td>
                        <td><?php echo htmlspecialchars($row['date_signature']) ?></td>
                        <td><?php echo htmlspecialchars($row['type']) ?></td>
                    </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</div>
<?php
    require_once 'partials/_footer.php';
?>
