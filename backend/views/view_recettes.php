<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Recettes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des recettes</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Produit</th>
          <th>Quantité</th>
          <th>Total</th>
          <th>Date Vente</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM recettes");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['produit_id']}</td>
                  <td>{$row['quantity']}</td>
                  <td>{$row['total']}</td>
                  <td>{$row['date']}</td>
                </tr>";
            }
        ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>