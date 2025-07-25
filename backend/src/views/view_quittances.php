<?php

    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/functions.php';

    $pdo = getPDO();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = ['', ''];

    // Filtrage et tri des quittances
    $typeFilter      = $_GET['type'] ?? '';
    $periode_service = $_GET['periode_service'] ?? '';
    $sortBy          = $_GET['sort_by'] ?? 'montant';
    $order           = $_GET['order'] ?? 'ASC';

    // Validation des filtres
    $validTypes = ['fournisseur', 'client'];

    $currentYear = date("Y"); // "Y" retourne l'année sur 4 chiffres (ex: 2025)

    $monthNames = [
        'janvier'   => 'Janvier',
        'février'   => 'Février',
        'mars'      => 'Mars',
        'avril'     => 'Avril',
        'mai'       => 'Mai',
        'juin'      => 'Juin',
        'juillet'   => 'Juillet',
        'août'      => 'Août',
        'septembre' => 'Septembre',
        'octobre'   => 'Octobre',
        'novembre'  => 'Novembre',
        'décembre'  => 'Décembre',
    ];

    // Tableau final pour les options qui combinent le nom du mois et l'année
    $periodsForDisplay = [];
    foreach ($monthNames as $value => $name) {
        // Le libellé affiché sera "Janvier 2025", "Février 2025", etc.
        $periodsForDisplay[$value] = $name . ' ' . $currentYear;

    }

    if ($periode_service && ! array_key_exists($periode_service, $monthNames)) {
        $periode_service = ''; // Réinitialiser si la période n'est pas valide
    }

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'montant', 'id_utilisateur', 'periode_service', 'date_paiement', 'date_emission'];

    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'montant'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Construction de la requête SQL pour récupération des quittances

    $query = "
    SELECT
        q.*,
        e.name AS employee_name
    FROM
        quittances q
    INNER JOIN
        employees e ON q.employee_id = e.id
    WHERE 1=1
";

    $params = [];

    if ($typeFilter && in_array($typeFilter, $validTypes)) {
        $query .= " AND q.type = ?"; // Utilise l'alias 'q.' pour éviter l'ambiguïté
        $params[] = $typeFilter;
    }

    if ($periode_service) {
        $periodeFilter = $periode_service . ' ' . $currentYear; // Combine le mois et l'année
        $query .= " AND q.periode_service = ?";                 // Utilise l'alias 'q.'
        $params[] = $periodeFilter;
    }

    // Ajout de la logique de tri pour le nom de l'employé
    if ($sortBy === 'employee_name') {
        $query .= " ORDER BY e.name $order"; // Trie par la colonne 'name' de la table 'employees'
    } else {
        $query .= " ORDER BY q.$sortBy $order"; // Trie par les colonnes de la table 'quittances'
    }

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $quittances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En production, vous devriez logguer $e->getMessage() et afficher un message générique.
        error_log("Erreur PDO lors de la récupération des quittances: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
    <title>Gestion des Quittances</title>
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
    <h2>Liste des quittances</h2>
   <!-- Formulaire de filtre et tri -->
    <form class="row mb-5 mt-5 bg-dark-subtle shadow gap-2 p-3" id="filterForm" method="GET" action="">
        <!-- Champ caché pour le routeur -->
        <input type="hidden" name="route" value="quittances">

        <div class="col-md-3">
            <label for="periode_service"><b>Période de service :</b></label>
            <select name="periode_service" class="form-select">
                <option value="">Toutes les périodes</option>
                <?php foreach ($periodsForDisplay as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>"
                        <?php echo($periode_service === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label for="type"><b>Par type :</b></label>
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <option value="fournisseur"
                  <?php echo($typeFilter === "fournisseur") ? 'selected' : ''; ?>>Fournisseur</option>
                <option value="client"
                  <?php echo($typeFilter === "client") ? 'selected' : ''; ?>>Client</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort_by"><b>Trier par :</b></label>
            <select name="sort_by" class="form-select">
                <option value="montant"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($sortBy === 'montant') ? 'selected' : ''; ?>>Montant</option>
                <option value="periode_service"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($sortBy === 'periode_service') ? 'selected' : ''; ?>>Période de service</option>
                <option value="date_paiement"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($sortBy === 'date_paiement') ? 'selected' : ''; ?>>Date de paiement</option>
                <option value="date_emission"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($sortBy === 'date_emission') ? 'selected' : ''; ?>>Date d'émission</option>
                <option value="employee_name"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($sortBy === 'employee_name') ? 'selected' : ''; ?>>Nom de l'employé</option>
                <!-- Ajoutez d'autres options de tri si nécessaire -->
            </select>
        </div>
        <div class="col-md-2">
            <label for="order"><b>Par ordre :</b></label>
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

    <!-- Tableau des quittances -->
    <div class="table-container bg-dark-subtle shadow p-3">
        <table class="table table-striped table-hover" id="quittanceTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Numéro</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Période</th>
                    <th>Date de Paiement</th>
                    <th>Date d'Émission</th>
                    <th>Traité par :</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quittances)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Aucune quittance trouvée.</td>
                    </tr>
                <?php else: ?>
<?php
    $i = 1;
foreach ($quittances as $row): ?>
                        <tr>
                            <td data-label="#"><?php echo $i++; ?></td>
                            <td data-label="Numéro"><?php echo htmlspecialchars($row['numero_quittance']); ?></td>
                            <td data-label="Type"><?php echo htmlspecialchars($row['type']); ?></td>
                            <td data-label="Montant"><?php echo htmlspecialchars($row['montant']); ?></td>
                            <td data-label="Période"><?php echo htmlspecialchars($row['periode_service']); ?></td>
                            <td data-label="Date de Paiement"><?php echo htmlspecialchars($row['date_paiement']); ?></td>
                            <td data-label="Date d'Émission"><?php echo htmlspecialchars($row['date_emission']); ?></td>
                            <!-- Affichage du nom de l'employé -->
                            <td data-label="Traité par :">
                                <?php echo htmlspecialchars($row['employee_name'] ?? 'N/A'); ?>
                            </td>
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
<?php require_once 'partials/_footer.php'; ?>
