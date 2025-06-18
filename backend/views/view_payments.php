<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Paiements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des paiements</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Montant</th>
          <th>Nature</th>
          <th>Date</th>
          <th>Description</th>
          <th>Client</th>
          <th>Contrat</th>
          <th>Reçu par</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM payments ORDER BY date_payment DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['amount']}</td>
                  <td>{$row['type']}</td>
                  <td>{$row['date_payment']}</td>
                  <td>{$row['description']}</td>
                  <td>{$row['customer_id']}</td>
                  <td>{$row['contrat_id']}</td>
                  <td>{$row['user_id']}</td>
                </tr>";
            }
        ?>
      </tbody>
    </table>
  </div>
<?php
require_once 'partials/_footer.php';
?>