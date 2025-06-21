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
    $natureFilter   = $_GET['nature'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'produit_id';
    $order          = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'produit_id', 'user_id', 'supplier_id', 'contrat_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'produit_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL
    $query  = "SELECT * FROM depenses WHERE 1=1";
    $params = [];

    if ($natureFilter) {
        $query .= " AND nature = ?";
        $params[] = $natureFilter;
    }

    if ($categoryFilter) {
        $query .= " AND category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Dépenses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des dépenses</h2>
        <?php echo $message ?>

     <!-- Formulaire de filtre -->
      <form method="get" class="row g-5 mb-4 mt-3">
        <div class="col-md-3">
            <select name="nature" class="form-select">
                <option value="">Toutes les natures</option>
                <option value="achat"
                  <?php echo($natureFilter === "achat") ? 'selected' : ''; ?>>Achat</option>
                <option value="location"
                  <?php echo($natureFilter === "location") ? 'selected' : ''; ?>>Location</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <option value="fournitures"
                  <?php echo($categoryFilter === "fournitures") ? 'selected' : ''; ?>>Fournitures</option>
                <option value="équipement"
                  <?php echo($categoryFilter === "équipement") ? 'selected' : ''; ?>>Equipement</option>
                <option value="services"
                  <?php echo($categoryFilter === "services") ? 'selected' : ''; ?>>Services</option>
                <option value="maintenance"
                  <?php echo($categoryFilter === "maintenance") ? 'selected' : ''; ?>>Maintenance</option>
                <option value="logistique"
                  <?php echo($categoryFilter === "logistique") ? 'selected' : ''; ?>>Logistique</option>
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

    <!-- Tableau des clients -->
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Produit</th>
          <th>Quantité</th>
          <th>Prix</th>
          <th>Total</th>
          <th>Fournisseur</th>
          <th>Nature</th>
          <th>Catégorie</th>
          <th>Date Dépense</th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($recettes)): ?>
              <tr>
                  <td colspan="10" class="text-center">Aucune recette trouvée.</td>
              </tr>
          <?php else: ?>
<?php foreach ($recettes as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']) ?></td>
                <td><?php echo htmlspecialchars($row['produit_id']) ?></td>
                <td><?php echo htmlspecialchars($row['quantity']) ?></td>
                <td><?php echo htmlspecialchars($row['price']) ?></td>
                <td><?php echo htmlspecialchars($row['amount']) ?></td>
                <td><?php echo htmlspecialchars($row['customer_id']) ?></td>
                <td><?php echo htmlspecialchars($row['nature']) ?></td>
                <td><?php echo htmlspecialchars($row['category']) ?></td>
                <td><?php echo htmlspecialchars($row['date_depense']) ?></td>
            </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>