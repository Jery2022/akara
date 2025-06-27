<?php
    session_start();
    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }

    require_once 'db.php';
    require_once 'functions.php';

    $recettes = getMonthlyRecettes();
    $depenses = getMonthlyDepenses();
    $achats   = getMonthlyAchats();
    $payments = getMonthlyPayments();

    $labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    // Créons quatre tableaux avec 12 mois initialisés à 0
    $recetteData = array_fill(0, 12, 0);
    $paymentData = array_fill(0, 12, 0);
    $achatData   = array_fill(0, 12, 0);
    $depenseData = array_fill(0, 12, 0);

    foreach ($recettes as $r) {
        $mois               = (int) date('n', strtotime($r['month'])) - 1;
        $recetteData[$mois] = (float) $r['total'];
    }

    foreach ($depenses as $p) {
        $mois               = (int) date('n', strtotime($p['month'])) - 1;
        $depenseData[$mois] = (float) $p['total'];
    }

    foreach ($achats as $a) {
        $mois             = (int) date('n', strtotime($a['month'])) - 1;
        $achatData[$mois] = (float) $a['total'];
    }
    foreach ($payments as $pa) {
        $mois               = (int) date('n', strtotime($pa['month'])) - 1;
        $paymentData[$mois] = (float) $pa['total'];
    }
?>

<?php require_once './views/partials/_header.php'; ?>
<title>Akara Administration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <?php require_once './views/partials/_navbar.php'; ?>
  <div class="sidebar d-flex flex-column align-items-start">
    <h4 class="px-3 mb-1">Dashboard</h4>
    <hr class="w-100 my-2 border-light">
    <nav class="nav flex-column w-100">
      <?php
          $tables = [
              'users'      => 'Utilisateurs',
              'suppliers'  => 'Fournisseurs',
              'customers'  => 'Clients',
              'employees'  => 'Employés',
              'produits'   => 'Produits',
              'contrats'   => 'Contrats',
              'stock'      => 'Stock',
              'entrepots'  => 'Entrepôts',
              'recettes'   => 'Recettes',
              'depenses'   => 'Dépenses',
              'payments'   => 'Paiements',
              'achats'     => 'Achats',
              'factures'   => 'Factures',
              'quittances' => 'Quittances',
          ];
      foreach ($tables as $table => $label): ?>
          <a href="views/view_<?php echo htmlspecialchars($table); ?>.php" class="nav-link"><?php echo htmlspecialchars($label); ?></a>
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

  <main class="main-content">
    <div class="titre bg-primary shadow-sm rounded mb-4">
      <div class="container-fluid">
        <span class="navbar-brand"><h3>Tableau de bord Administrateur</h3></span>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-primary animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Clients</h6>
            <h4 class="card-title"><?php echo getTotal('customers'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-success animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Produits</h6>
            <h4 class="card-title"><?php echo getTotal('produits'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-warning animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Recettes Totales</h6>
            <h4 class="card-title"><?php echo formatNumber(getRecettesTotal(), 'fr-FR'); ?> €</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-danger animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Dépenses Totales</h6>
            <h4 class="card-title"><?php echo formatNumber(getDepensesTotal(), 'fr_FR'); ?> €</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-indigo animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Utilisateurs</h6>
            <h4 class="card-title"><?php echo getTotal('users'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-pink animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Employés</h6>
            <h4 class="card-title"><?php echo getTotal('employees'); ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-blue animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Paiements Totals</h6>
            <h4 class="card-title"><?php echo formatNumber(getPaymentsTotal(), 'fr_FR'); ?> €</h4>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white bg-gradient-orange animate__animated animate__fadeInUp">
          <div class="card-body">
            <h6 class="card-subtitle mb-2">Achats Totals</h6>
            <h4 class="card-title"><?php echo formatNumber(getAchatsTotal(), 'fr_FR'); ?> €</h4>
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
    <div class="card row g-4 mb-4 mt-4">
      <div class="col-xl-12 col-md-6 shadow-sm mb-4 mt-4">
        <div class="card-body">
          <h5 class="card-title">Graphique des recettes vs dépenses mensuelles</h5>
          <canvas id="StatChart2" height="100"></canvas>
        </div>
      </div>
    </div>
    <div class="card row g-4 mb-4 mt-4">
      <div class="col-xl-12 col-md-6 shadow-sm mb-4 mt-4">
        <div class="card-body">
          <h5 class="card-title">Graphique des paiements vs achats mensuels</h5>
          <canvas id="StatChart3" height="100"></canvas>
        </div>
      </div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx1 = document.getElementById('statChart').getContext('2d');
    new Chart(ctx1, {
      type: 'bar',
      data: {
        labels: ['Clients', 'Produits', 'Utilisateurs', 'Employés'],
        datasets: [{
          label: 'Nombre',
          data: [
            <?php echo getTotal('customers'); ?>,<?php echo getTotal('produits'); ?>,<?php echo getTotal('users'); ?>,<?php echo getTotal('employees'); ?>
          ],
          backgroundColor: ['#0d6efd', '#198754', '#6610f2', '#d63384']
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
  const ctx2 = document.getElementById('StatChart2').getContext('2d');
  const chart2 = new Chart(ctx2, {
    type: 'bar',
    data: {
      labels:
      <?php echo json_encode($labels); ?>,
      datasets: [
        {
          label: 'Recettes',
          data:
          <?php echo json_encode($recetteData); ?>,
          backgroundColor: 'rgba(25, 135, 84, 0.6)',
          borderRadius: 6,
          borderSkipped: false
        },
        {
          label: 'Dépenses',
          data:
            <?php echo json_encode($depenseData); ?>,
          type: 'line',
          borderColor: '#dc3545',
          backgroundColor: 'rgba(220, 53, 69, 0.1)',
          pointBackgroundColor: '#dc3545',
          tension: 0.3,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true, // false
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'top',
          labels: {
            color: '#343a40',
            font: {
              weight: 'bold'
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let value = context.raw.toLocaleString('fr-FR', { minimumFractionDigits: 2 });
              return `${context.dataset.label} : ${value} €`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: false, //true
          ticks: {
            callback: function(value) {
              return value.toLocaleString('fr-FR');
            }
          },
          grid: {
            color: 'rgba(0,0,0,0.05)'
          }
        }
      }
    }
  });
</script>

<script>
  const ctx3 = document.getElementById('StatChart3').getContext('2d');
  const chart3 = new Chart(ctx3, {
    type: 'bar',
    data: {
      labels:
      <?php echo json_encode($labels); ?>,
      datasets: [
        {
          label: 'Paiements',
          data:
          <?php echo json_encode($paymentData); ?>,
          backgroundColor: 'rgba(25, 135, 84, 0.6)',
          borderRadius: 6,
          borderSkipped: false
        },
        {
          label: 'Achats',
          data:
            <?php echo json_encode($achatData); ?>,
          type: 'line',
          borderColor: '#dc3545',
          backgroundColor: 'rgba(220, 53, 69, 0.1)',
          pointBackgroundColor: '#dc3545',
          tension: 0.3,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true, // false
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        legend: {
          position: 'top',
          labels: {
            color: '#343a40',
            font: {
              weight: 'bold'
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let value = context.raw.toLocaleString('fr-FR', { minimumFractionDigits: 2 });
              return `${context.dataset.label} : ${value} €`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: false, //true
          ticks: {
            callback: function(value) {
              return value.toLocaleString('fr-FR');
            }
          },
          grid: {
            color: 'rgba(0,0,0,0.05)'
          }
        }
      }
    }
  });
</script>
</body>
</html>
