<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Contrats</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des contrats</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Référence</th>
          <th>Objet</th>
          <th>Date début</th>
          <th>Date fin</th>
          <th>Status</th>
          <th>Montant</th>
          <th>Signataire</th>
          <th>Signé le</th>
          <th>Type</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM contrats ORDER BY id ASC LIMIT 25");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['ref']}</td>
                  <td>{$row['objet']}</td>
                  <td>{$row['date_debut']}</td>
                  <td>{$row['date_fin']}</td>
                  <td>{$row['status']}</td>
                  <td>{$row['montant']}</td>
                  <td>{$row['signataire']}</td>
                  <td>{$row['date_signature']}</td>
                  <td>{$row['type']}</td>
                </tr>";
            }
        ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>