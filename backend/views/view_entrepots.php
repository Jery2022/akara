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
    $quality_stockageFilter = $_GET['quality_stockage'] ?? '';
    $black_listFilter       = $_GET['black_list'] ?? '';
    $sortBy                 = $_GET['sort_by'] ?? 'name';
    $order                  = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email', 'capacity'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL
    $query  = "SELECT * FROM entrepots WHERE 1=1";
    $params = [];

    if ($black_listFilter) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($quality_stockageFilter) {
        $query .= " AND quality_stockage = ?";
        $params[] = $quality_stockageFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <title>Gestion Entrpôts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des entrepôts</h2>
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
            <select name="quality_stockage" class="form-select">
                <option value="">Toutes les qualités</option>
                <option value="bonne"
                  <?php echo($quality_stockageFilter === "bonne") ? 'selected' : ''; ?>>Bonne</option>
                <option value="moyenne"
                  <?php echo($quality_stockageFilter === "moyenne") ? 'selected' : ''; ?>>Moyenne</option>
                <option value="mauvaise"
                  <?php echo($quality_stockageFilter === "mauvaise") ? 'selected' : ''; ?>>Mauvaise</option>
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

    <!-- Tableau des entrepôts -->
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Adresse</th>
          <th>Responsable</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Capacité</th>
          <th>Stockage</th>
          <th>Bannis</th>
        </tr>
      </thead>
      <tbody>
          <?php if (empty($entrepots)): ?>
              <tr>
                  <td colspan="10" class="text-center">Aucun client trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($entrepots as $row): ?>
              <tr>
                  <td><?php echo htmlspecialchars($row['id']) ?> </td>
                  <td><?php echo htmlspecialchars($row['name']) ?> </td>
                  <td><?php echo htmlspecialchars($row['adresse']) ?> </td>
                  <td><?php echo htmlspecialchars($row['responsable']) ?> </td>
                  <td><?php echo htmlspecialchars($row['email']) ?> </td>
                  <td><?php echo htmlspecialchars($row['telephone']) ?> </td>
                  <td><?php echo htmlspecialchars($row['capacity']) ?> </td>
                  <td><?php echo htmlspecialchars($row['quality_stockage']) ?> </td>
                  <td><?php echo htmlspecialchars($row['black_list']) ?> </td>
                </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>