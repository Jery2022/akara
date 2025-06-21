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
    $provenanceFilter    = $_GET['provenance'] ?? '';
    $disponibilityFilter = $_GET['disponibility'] ?? '';
    $sortBy              = $_GET['sort_by'] ?? 'name';
    $order               = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'price', 'supplier_id', 'entrepot_id '];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL
    $query  = "SELECT * FROM produits WHERE 1=1";
    $params = [];

    if ($provenanceFilter) {
        $query .= " AND provenance = ?";
        $params[] = $provenanceFilter;
    }

    if ($disponibilityFilter) {
        $query .= " AND disponibility = ?";
        $params[] = $disponibilityFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Produits</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des produits</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
      <form method="get" class="row g-5 mb-4 mt-3">
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

    <!-- Tableau des clients -->
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
                  <td colspan="10" class="text-center">Aucun client trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($produits as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['unit']); ?></td>
            <td><?php echo htmlspecialchars($row['price']); ?></td>
            <td><?php echo htmlspecialchars($row['provenance']); ?></td>
            <td><?php echo htmlspecialchars($row['disponibility']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
          </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>