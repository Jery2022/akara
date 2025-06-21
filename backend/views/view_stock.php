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

    // Filtrage et tri des clients
    $rentabilityFilter    = $_GET['rentability'] ?? '';
    $classificationFilter = $_GET['classification'] ?? '';
    $sortBy               = $_GET['sort_by'] ?? 'min';
    $order                = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'min', 'quantity', 'produit_id', 'supplier_id', 'entrepot_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL
    $query  = "SELECT * FROM stock WHERE 1=1";
    $params = [];

    if ($rentabilityFilter) {
        $query .= " AND rentability = ?";
        $params[] = $rentabilityFilter;
    }

    if ($classificationFilter) {
        $query .= " AND classification = ?";
        $params[] = $classificationFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Stock</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des stocks</h2>
    <?php echo $message ?>

     <!-- Formulaire de filtre -->
      <form method="get" class="row g-5 mb-4 mt-3">
        <div class="col-md-3">
            <select name="rentability" class="form-select">
                <option value="">Toutes les rentabilités</option>
                <option value="forte"
                  <?php echo($rentabilityFilter === "forte") ? 'selected' : ''; ?>>Forte</option>
                <option value="bonne"
                  <?php echo($rentabilityFilter === "bonne") ? 'selected' : ''; ?>>Bonne</option>
                <option value="faible"
                  <?php echo($rentabilityFilter === "faible") ? 'selected' : ''; ?>>Faible</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="classification" class="form-select">
                <option value="">Toutes les classifications</option>
                <option value="A"
                  <?php echo($classificationFilter === "A") ? 'selected' : ''; ?>>Classification A</option>
                <option value="B"
                  <?php echo($classificationFilter === "B") ? 'selected' : ''; ?>>Classification B</option>
                <option value="C"
                  <?php echo($classificationFilter === "C") ? 'selected' : ''; ?>>Classification C</option>
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

    <!-- Tableau des stocks -->
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Libellé</th>
          <th>Qté actuelle</th>
          <th>Unité</th>
          <th>Qté alerte</th>
          <th>Rentabilté</th>
          <th>Classification</th>
          <th>ID fournisseur</th>
          <th>ID entrepôt</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($stock)): ?>
              <tr>
                  <td colspan="10" class="text-center">Aucun stock trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($stock as $row): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['id']) ?></td>
                  <td><?php echo htmlspecialchars($row['produit_id']) ?></td>
                  <td><?php echo htmlspecialchars($row['quantity']) ?></td>
                  <td><?php echo htmlspecialchars($row['unit']) ?></td>
                  <td><?php echo htmlspecialchars($row['min']) ?></td>
                  <td><?php echo htmlspecialchars($row['rentability']) ?></td>
                  <td><?php echo htmlspecialchars($row['classification']) ?></td>
                  <td><?php echo htmlspecialchars($row['supplier_id']) ?></td>
                  <td><?php echo htmlspecialchars($row['entrepot_id']) ?></td>
                </tr>
                <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>