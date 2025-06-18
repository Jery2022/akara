<?php
    require_once '../db.php';
    require_once 'partials/_header.php';
?>
  <title>Gestion Employés</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2>Liste des employés</h2>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Fonction</th>
          <th>Salaire</th>
          <th>Téléphone</th>
          <th>Email</th>
          <th>ID Contrat</th>
          <th>ID utilisateur</th>
        </tr>
      </thead>
      <tbody>
        <?php
            $stmt = $pdo->query("SELECT * FROM employees");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                  <td>{$row['id']}</td>
                  <td>{$row['name']}</td>
                  <td>{$row['role']}</td>
                  <td>{$row['salary']}</td>
                  <td>{$row['phone']}</td>
                  <td>{$row['email']}</td>
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