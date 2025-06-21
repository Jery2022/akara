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

    // Construction de la requête SQL
    $query  = "SELECT * FROM customers WHERE 1=1";
    $params = [];

    if ($black_listFilter) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($statusFilter) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Clients</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des clients</h2>
    <?php echo $message ?>

     <!-- Formulaire de filtre -->
      <form method="get" class="row g-5 mb-4 mt-3">
        <div class="col-md-3">
            <select name="black_list" class="form-select">
                <option value="">Tous les black-listés</option>
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
                  <?php echo($statusFilter === "à risque") ? 'selected' : ''; ?>>A risque</option>
                <option value="à suivre"
                  <?php echo($statusFilter === "à suivre") ? 'selected' : ''; ?>>A suivre</option>
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
          <th>Contact</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>Statut</th>
          <th>Bannis</th>
          <th>ID Contrat</th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($customers)): ?>
              <tr>
                  <td colspan="10" class="text-center">Aucun client trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($customers as $row): ?>
              <tr>
                  <td><?php echo htmlspecialchars($row['id']) ?> </td>
                  <td><?php echo htmlspecialchars($row['name']) ?></td>
                  <td><?php echo htmlspecialchars($row['refContact']) ?></td>
                  <td><?php echo htmlspecialchars($row['phone']) ?></td>
                  <td><?php echo htmlspecialchars($row['email']) ?></td>
                  <td><?php echo htmlspecialchars($row['status']) ?></td>
                  <td><?php echo htmlspecialchars($row['black_list']) ?></td>
                  <td><?php echo htmlspecialchars($row['contrat_id'] || '') ?></td>
              </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>