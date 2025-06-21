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

    // Filtrage et tri des paiements
    $typeFilter     = $_GET['type'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'customer_id';
    $order          = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'contrat_id', 'user_id', 'supplier_id', 'amount'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'customers_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL
    $query  = "SELECT * FROM achats WHERE 1=1";
    $params = [];

    if ($typeFilter) {
        $query .= " AND type = ?";
        $params[] = $typeFilter;
    }

    if ($categoryFilter) {
        $query .= " AND category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Achat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des achats</h2>
           <?php echo $message ?>

     <!-- Formulaire de filtre -->
      <form method="get" class="row g-5 mb-4 mt-3">
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
                <option value="élecricité"
                  <?php echo($categoryFilter === "élecricité") ? 'selected' : ''; ?>>Elecricité</option>
                <option value="téléphone"
                  <?php echo($categoryFilter === "téléphone") ? 'selected' : ''; ?>>Téléphone</option>
                <option value="carburant"
                  <?php echo($categoryFilter === "carburant") ? 'selected' : ''; ?>>Carburant</option>
                <option value="eau"
                  <?php echo($categoryFilter === "eau") ? 'selected' : ''; ?>>Eau</option>
                <option value="mobiliers"
                  <?php echo($categoryFilter === "mobiliers") ? 'selected' : ''; ?>>Mobiliers</option>
                                  <option value="fiscalité"
                  <?php echo($categoryFilter === "fiscalité") ? 'selected' : ''; ?>>fiscalité</option>
                                  <option value="impôts"
                  <?php echo($categoryFilter === "impôts") ? 'selected' : ''; ?>>Impôts</option>
                                  <option value="taxes"
                  <?php echo($categoryFilter === "taxes") ? 'selected' : ''; ?>>Taxes</option>
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
                  <td colspan="10" class="text-center">Aucun achat trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($achats as $row): ?>
            <tr>
                  <td><?php echo htmlspecialchars($row['id']) ?></td>
                  <td><?php echo htmlspecialchars($row['contrat_id']) ?></td>
                  <td><?php echo htmlspecialchars($row['user_id']) ?></td>
                  <td><?php echo htmlspecialchars($row['suppliers_id']) ?></td>
                  <td><?php echo htmlspecialchars($row['amount']) ?></td>
                  <td><?php echo htmlspecialchars($row['type']) ?></td>
                  <td><?php echo htmlspecialchars($row['category']) ?></td>
                  <td><?php echo htmlspecialchars($row['date_achat']) ?></td>
            </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>