<?php

    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once __DIR__ . '/../db.php';

    $pdo = getPDO();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Filtrage et tri des entrepôts
    $quality_stockageFilter = $_GET['quality_stockage'] ?? '';
    $black_listFilter       = $_GET['black_list'] ?? '';
    $sortBy                 = $_GET['sort_by'] ?? 'name';
    $order                  = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email', 'capacity'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validBlackList       = ['oui', 'non'];
    $validQualityStockage = ['bonne', 'moyenne', 'mauvaise'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM entrepots WHERE 1=1";
    $params = [];

    if ($black_listFilter && in_array($black_listFilter, $validBlackList)) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($quality_stockageFilter && in_array($quality_stockageFilter, $validQualityStockage)) {
        $query .= " AND quality_stockage = ?";
        $params[] = $quality_stockageFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $entrepots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des entrepôts: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion Entrepôts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<!-- Toast Bootstrap pour les messages -->
<div class="position-fixed bottom-0 end-0 p-2" style="z-index: 1100">
  <div id="mainToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true"
       <?php if (! empty($message) && is_array($message) && count($message) === 2): ?>
           data-toast-type="<?php echo htmlspecialchars($message[1]); ?>"
       <?php endif; ?>>
    <?php if (! empty($message) && is_array($message) && count($message) === 2): ?>
        <div class="d-flex">
            <div class="toast-body" id="mainToastBody">
                <?php echo htmlspecialchars($message[0]); // Affiche le texte du message ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>
  </div>
</div>
<main class="container my-4">
    <h2>Liste des entrepôts</h2>

    <!-- Formulaire de filtre et tri -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <!-- Champ caché pour le routeur -->
        <input type="hidden" name="route" value="entrepots">

        <div class="col-md-3">
            <label for="black_list"><b>Par Statut :</b></label>
            <select name="black_list" class="form-select">
                <option value="">Tous les black-listés ou non</option>
                <option value="oui"
                  <?php echo($black_listFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"
                  <?php echo($black_listFilter === "non") ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="quality_stockage"><b>Par qualité de stockage :</b></label>
            <select name="quality_stockage" class="form-select">
                <option value="">Toutes les qualités</option>
                <option value="bonne"
                  <?php echo($quality_stockageFilter === "bonne") ? 'selected' : ''; ?>>Bonne</option>
                <option value="moyenne"
                  <?php echo($quality_stockageFilter === "moyenne") ? 'selected' : ''; ?>>Moyenne</option>
                <option value="mauvaise"
                  <?php echo($quality_stockageFilter === "mauvaise") ? 'selected' : ''; ?>>Mauvaise</option>
            </select>
        </div>

        <div class="col-md-2 ">
            <label for="sort_by"><b>Par ordre:</b></label>
            <select name="order" class="form-select">
                <option value="ASC"
                  <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"
                  <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrer et Trier</button>
        </div>
    </form>

    <!-- Tableau des entrepôts -->
<div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Responsable</th>
                <th>E-mail</th>
                <th>Téléphone</th>
                <th>Capacité</th>
                <th>Stockage</th>
                <th>Bannis</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entrepots)): ?>
                <tr>
                    <td colspan="9" class="text-center">Aucun entrepôt trouvé.</td>
                </tr>
            <?php else: ?>
<?php
    $i = 1;
foreach ($entrepots as $row): ?>
                    <tr>
                        <td data-label="#"><?php echo $i++; ?> </td>
                        <td data-label="Nom"><?php echo htmlspecialchars($row['name']) ?> </td>
                        <td data-label="Adresse"><?php echo htmlspecialchars($row['adresse']) ?> </td>
                        <td data-label="Responsable"><?php echo htmlspecialchars($row['responsable']) ?> </td>
                        <td data-label="E-mail"><?php echo htmlspecialchars($row['email']) ?> </td>
                        <td data-label="Téléphone"><?php echo htmlspecialchars($row['telephone']) ?> </td>
                        <td data-label="Capacité"><?php echo htmlspecialchars($row['capacity']) ?> </td>
                        <td data-label="Stockage"><?php echo htmlspecialchars($row['quality_stockage']) ?> </td>
                        <td data-label="Bannis"><?php echo htmlspecialchars($row['black_list']) ?> </td>
                    </tr>
                <?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
        const toastElement = document.getElementById('mainToast');
        if (toastElement) {
            const toastBody = document.getElementById('mainToastBody');
            // Vérifie si le corps du toast contient du texte et n'est pas vide
            if (toastBody && toastBody.innerHTML.trim() !== '') {
                const toastType = toastElement.getAttribute('data-toast-type');

                // Supprime toutes les classes de couleur existantes pour éviter les conflits
                toastElement.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger');

                if (toastType === 'success') {
                    toastElement.classList.add('text-bg-success');
                } else if (toastType === 'danger') {
                    toastElement.classList.add('text-bg-danger');
                } else {
                    // Couleur par défaut si le type n'est pas reconnu ou non défini
                    toastElement.classList.add('text-bg-primary');
                }

                const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 4000 });
                toast.show();
            }
        }
    });
</script>
<?php
    require_once 'partials/_footer.php';
?>
