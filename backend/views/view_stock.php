<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Stock</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des stocks</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Libellé</th>
          <th>Qté actuelle</th>
          <th>Unité</th>
          <th>Qté alerte</th>
          <th>ID fournisseur</th>
          <th>ID entrepôt</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM stock");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['produit_id']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['unit']}</td>
                  <td>{$row['min']}</td>
                  <td>{$row['supplier_id']}</td>
                  <td>{$row['entrepot_id']}</td>
                </tr>";
            }
        ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>