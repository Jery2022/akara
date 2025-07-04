<?php
    session_start();
    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'employe') {
        header('Location: login.php');
        exit;
    }

    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/functions.php';
?>
<?php require_once __DIR__ . '/views/partials/_header.php'; ?>
<title>Akara Administration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <?php require_once __DIR__ . '/views/partials/_navbar.php'; ?>
  <div class="sidebar d-flex flex-column align-items-start">
    <h4 class="px-3 mb-1">Dashboard</h4>
    <hr class="w-100 my-2 border-light">
    <nav class="nav flex-column w-100">
      <?php
          $tables = [
              'suppliers' => 'Fournisseurs',
              'customers' => 'Clients',
              'employees' => 'Employés',
              'produits'  => 'Produits',
              'stock'     => 'Stock',
              'entrepots' => 'Entrepôts',
          ];
      foreach ($tables as $table => $label): ?>
          <a href="/views/view_<?php echo htmlspecialchars($table); ?>.php" class="nav-link"><?php echo htmlspecialchars($label); ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
  <!-- Navigation horizontale -->
   <header>
      <nav class="horizontal-nav">
          <?php
          foreach ($tables as $table => $label): ?>
          <a href="views/view_<?php echo htmlspecialchars($table); ?>.php" class="nav-link"><?php echo htmlspecialchars($label); ?></a>
      <?php endforeach; ?>
      </nav>
  </header>

  <div class="main-content">
    <div class="titre bg-primary shadow-sm rounded mb-4">
      <div class="container-fluid">
        <span class="navbar-brand"><h3>Tableau de bord Employé</h3></span>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="row d-flex justify-content-between">
      <div class="col-sm-12 col-md-3 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-primary animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Clients</h6>
            <h4 class="card-title"><?php echo getTotal('customers'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-3 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-success animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Produits</h6>
            <h4 class="card-title"><?php echo getTotal('produits'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-3 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-indigo animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Utilisateurs</h6>
            <h4 class="card-title"><?php echo getTotal('users'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-3 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-pink animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Employés</h6>
            <h4 class="card-title"><?php echo getTotal('employees'); ?></h4>
          </div>
        </div>
      </div>
    </div>
    <div class="row  d-flex justify-content-between mb-4">
      <div class="col-sm-12 col-md-6 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-danger animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Fournisseurs</h6>
            <h4 class="card-title"><?php echo getTotal('suppliers'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-6 mt-2">
        <div class="card border-0 shadow-sm text-white bg-gradient-warning animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Entrepôts</h6>
            <h4 class="card-title"><?php echo getTotal('entrepots'); ?></h4>
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
    </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx1 = document.getElementById('statChart').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: ['Clients', 'Produits', 'Utilisateurs', 'Employés', 'Fournisseurs', 'Entrepôts'],
        datasets: [{
          label: 'Nombre',
          data: [
            <?php echo getTotal('customers'); ?>,
            <?php echo getTotal('produits'); ?>,
            <?php echo getTotal('users'); ?>,
            <?php echo getTotal('employees'); ?>,
<?php echo getTotal('suppliers'); ?>,
<?php echo getTotal('entrepots'); ?>
          ],
          backgroundColor: ['#0d6efd', '#198754', '#6610f2', '#d63384', '#dc3545', '#ffc107']
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
