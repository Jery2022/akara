<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Produits</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des produits</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th>Prix</th>
          <th>Créé le</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM produits ORDER BY id ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['name']}</td>
                  <td>{$row['description']}</td>
                  <td>{$row['price']}</td>
                  <td>{$row['created_at']}</td>
                </tr>";
            }
        ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>