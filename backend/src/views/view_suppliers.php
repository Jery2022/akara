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

    // Filtrage et tri des fournisseurs
    $statusFilter     = $_GET['status'] ?? '';
    $black_listFilter = $_GET['black_list'] ?? '';
    $sortBy           = $_GET['sort_by'] ?? 'name';
    $order            = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validStatuses  = ['sérieux', 'à risque', 'à suivre'];
    $validBlackList = ['oui', 'non'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM suppliers WHERE 1=1";
    $params = [];

    if ($black_listFilter && in_array($black_listFilter, $validBlackList)) {
        $query .= " AND black_list = ?";
        $params[] = $black_listFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des quittances: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Fournisseurs</title>
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
    <h2>Liste des fournisseurs</h2>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <!-- Champ caché pour le routeur -->
    <input type="hidden" name="route" value="suppliers">

        <div class="col-md-3">
            <label for="black_list"><b>Black-listés ou non :</b></label>
            <select name="black_list" class="form-select">
                <option value="">Tous les black-listés ou non</option>
                <option value="oui"
                  <?php echo($black_listFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"
                <?php echo($black_listFilter === "non") ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="status"><b>Par statut :</b></label>
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="sérieux"
                  <?php echo($statusFilter === "sérieux") ? 'selected' : ''; ?>>Sérieux</option>
                <option value="à risque"
                  <?php echo($statusFilter === "à risque") ? 'selected' : ''; ?>>À risque</option>
                <option value="à suivre"
                  <?php echo($statusFilter === "à suivre") ? 'selected' : ''; ?>>À suivre</option>
            </select>
        </div>

        <div class="col-md-2">
            <label for="order"><b>Trier par ordre :</b></label>
            <select name="order" class="form-select">
                <option value="ASC"
                  <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"
                  <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Trier et Filtrer</button>
        </div>
    </form>

    <!-- Tableau des fournisseurs -->
    <div class="table-container bg-dark-subtle shadow gap-3 p-3 ">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Contact</th>
            <th>Téléphone</th>
            <th>Email</th>
            <th>Statut</th>
            <th>Bannis</th>
            <th>ID Contrat</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($suppliers)): ?>
            <tr>
                <td colspan="8" class="text-center">Aucun fournisseur trouvé.</td>
            </tr>
        <?php else: ?>
<?php
    $i = 1;
foreach ($suppliers as $row): ?>
                <tr>
                    <td data-label="#"><?php echo $i++; ?></td>
                    <td data-label="Nom"><?php echo htmlspecialchars($row['name']) ?></td>
                    <td data-label="Contact"><?php echo htmlspecialchars($row['refContact']) ?></td>
                    <td data-label="Téléphone"><?php echo htmlspecialchars($row['phone']) ?></td>
                    <td data-label="E-mail"><?php echo htmlspecialchars($row['email']) ?></td>
                    <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                    <td data-label="Banis"><?php echo htmlspecialchars($row['black_list']) ?></td>
                    <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_id']) ?></td>
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
