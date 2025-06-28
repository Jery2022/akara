<?php
    session_start();
    if (! isset($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
    require_once 'db.php';
    require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Administration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>

  <div class="sidebar d-flex flex-column align-items-start">
    <h4 class="px-3 mb-2">Administration</h4>
    <hr class="w-100 my-3 border-light">
    <nav class="nav flex-column w-100">
      <?php
          $tables = [
              'users'     => 'Utilisateurs',
              'suppliers' => 'Fournisseurs',
              'customers' => 'Clients',
              'produits'  => 'Produits',
              'stock'     => 'Stock',
              'employees' => 'Employés',
              'contrats'  => 'Contrats',
              'entrepots' => 'Entrepôts',
              'recettes'  => 'Recettes',
              'payments'  => 'Paiements',
          ];
      foreach ($tables as $table => $label): ?>
          <a href="views/view_<?php echo $table ?>.php" class="nav-link  "><?php echo $label ?></a>
      <?php endforeach; ?>
      <hr class="w-100 my-3 border-light">
      <a href="admin_logout.php" class="nav-link text-warning">Déconnexion</a>
    </nav>
  </div>

  <div class="main-content">
    <nav class="navbar navbar-dark bg-primary shadow-sm rounded mb-4">
      <div class="container-fluid">
        <span class="navbar-brand">Tableau de bord</span>
      </div>
    </nav>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-primary animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Clients</h6>
            <h4 class="card-title"><?php echo getTotal('customers') ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-success animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Produits</h6>
            <h4 class="card-title"><?php echo getTotal('produits') ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-warning animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Recettes Totales</h6>
            <h4 class="card-title"><?php echo getRecettesTotal() ?> €</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-danger animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Paiements Totals</h6>
            <h4 class="card-title"><?php echo getPaymentsTotal() ?> €</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Graphique simple -->
     <div class="card row g-4 mb-4 mt-4">
         <div class="col-xl-12 col-md-6 shadow-sm mb-4">
            <div class="card-body">
               <h5 class="card-title">Statistiques rapides</h5>
               <canvas id="statChart" height="100"></canvas>
            </div>
         </div>
         <div class="col-xl-12 col-md-6 shadow-sm mb-4">
            <div class="card-body">
               <h5 class="card-title">Graphique des recettes mensuelles</h5>
               <canvas id="AutreStatChart" height="100"></canvas>
            </div>
         </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx1 = document.getElementById('statChart').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: ['Clients', 'Produits'],
        datasets: [{
          label: 'Nombre',
          data: [<?php echo getTotal('customers') ?>,<?php echo getTotal('produits') ?>],
          backgroundColor: ['#0d6efd', '#198754']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        }
      }
    });
 </script>
  <script>
    // Graphique des recettes mensuelles
   <?php var_dump(getMonthlyRecettes())?>; //log
      const ctx2 = document.getElementById('AutreStatChart').getContext('2d');
      new Chart(ctx2, {
         type: 'bar',
         data: {
         labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
         datasets: [{
            label: 'Recettes mensuelles',
            data: [<?php echo getMonthlyRecettes() ?>],
            borderColor: '#0d6efd',
            backgroundColor: '#0d6efd',
            fill: false
         }]
         },
         options: {
         responsive: true,
         plugins: {
            legend: { display: false }
         }
         }
      });
  </script>
</body>
</html>