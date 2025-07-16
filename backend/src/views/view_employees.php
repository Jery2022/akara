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

    // Filtrage et tri des employés
    $statusFilter  = $_GET['status'] ?? '';
    $qualityFilter = $_GET['quality'] ?? '';
    $sortBy        = $_GET['sort_by'] ?? 'name';
    $order         = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'email', 'fonction', 'category', 'salary'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validStatuses  = ['actif', 'inactif'];
    $validQualities = ['ouvrier', 'technicien', 'ingenieur', 'ceo'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM employees WHERE 1=1";
    $params = [];

    if ($qualityFilter && in_array($qualityFilter, $validQualities)) {
        $query .= " AND quality = ?";
        $params[] = $qualityFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des employés: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }
?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Employés</title>
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
    <h2>Liste des employés</h2>

    <!-- Formulaire de filtre et tri-->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <!-- Champ caché pour le routeur -->
        <input type="hidden" name="route" value="employees">

        <div class="col-md-3">
            <label for="quality"><b>Par qualité :</b></label>
            <select name="quality" class="form-select">
                <option value="">Toutes les qualifications</option>
                <option value="ouvrier"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?php echo($qualityFilter === "ouvrier") ? 'selected' : ''; ?>>Ouvrier</option>
                <option value="technicien"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo($qualityFilter === "technicien") ? 'selected' : ''; ?>>Technicien</option>
                <option value="ingenieur"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              <?php echo($qualityFilter === "ingenieur") ? 'selected' : ''; ?>>Ingénieur</option>
                <option value="ceo"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?php echo($qualityFilter === "ceo") ? 'selected' : ''; ?>>CEO</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="status"><b>Par statut :</b></label>
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="actif"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          <?php echo($statusFilter === "actif") ? 'selected' : ''; ?>>Actif</option>
                <option value="inactif"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?php echo($statusFilter === "inactif") ? 'selected' : ''; ?>>Inactif</option>
            </select>
        </div>

        <div class="col-md-2">
            <label for="sort_by"><b>Par ordre :</b></label>
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrer et Trier</button>
        </div>
    </form>

    <!-- Tableau des employés -->
     <div class="table-container bg-dark-subtle shadow p-3 ">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Fonction</th>
            <th>Salaire</th>
            <th>Téléphone</th>
            <th>E-mail</th>
            <th>Statut</th>
            <th>Qualité</th>
            <th>ID Contrat</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($employees)): ?>
            <tr>
                <td colspan="9" class="text-center">Aucun employé trouvé.</td>
            </tr>
        <?php else: ?>
<?php
    $i = 1;
foreach ($employees as $row): ?>
                <tr>
                    <td data-label="#"><?php echo $i++; ?></td>
                    <td data-label="Nom"><?php echo htmlspecialchars($row['name']) ?></td>
                    <td data-label="Fonction"><?php echo htmlspecialchars($row['fonction']) ?></td>
                    <td data-label="Salaire"><?php echo htmlspecialchars($row['salary']) ?></td>
                    <td data-label="Téléphone"><?php echo htmlspecialchars($row['phone']) ?></td>
                    <td data-label="E-mail"><?php echo htmlspecialchars($row['email']) ?></td>
                    <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                    <td data-label="Qualité"><?php echo htmlspecialchars($row['quality']) ?></td>
                    <td data-label="ID Contrat"><?php echo htmlspecialchars($row['contrat_id'] ?? '') ?></td>
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
