<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Entrpôts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des entrepôts</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Adresse</th>
          <th>Responsable</th>
          <th>Email</th>
          <th>Téléphone</th>
          <th>Créé le</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM entrepots ORDER BY created_at DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['nom']}</td>
                  <td>{$row['adresse']}</td>
                  <td>{$row['responsable']}</td>
                  <td>{$row['email']}</td>
                  <td>{$row['telephone']}</td>
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