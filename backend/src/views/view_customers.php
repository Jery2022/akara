<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirection si l'utilisateur n'est pas admin ou employé
if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$pdo = getPDO();

// CSRF token (utile pour les formulaires POST sur cette page si vous en ajoutez)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation de la variable de message comme un tableau vide pour la cohérence avec le Toast
$message = [];

// --- Gestion des messages Flash de session (pour les redirections) ---
if (isset($_SESSION['flash_message'])) {
    // Le message flash est un tableau ['texte', 'type']
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the flash message after display
}

// Validation des paramètres de tri
$statusFilter     = $_GET['status'] ?? '';
$black_listFilter = $_GET['black_list'] ?? '';
$sortBy           = $_GET['sort_by'] ?? 'name';
$order            = $_GET['order'] ?? 'ASC';

$validSortColumns = [
    'id'         => 'ID Client',
    'name'       => 'Nom du client',
    'created_at' => 'Date de création',
    'email'      => 'Email',
    'phone'      => 'Téléphone',
    'refContact' => 'Signataire',
    'status'     => 'Statut',
    'contrat_id' => 'Référence Contrat',
    'ville'      => 'Ville',
];

// Validation des paramètres de tri
if (! array_key_exists($sortBy, $validSortColumns)) {
    $sortBy = 'name'; // Fallback to default if invalid
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

$validBlackListOptions = [
    'oui' => 'Oui',
    'non' => 'Non',
];

$validStatusOptions = [
    'sérieux'  => 'Sérieux',
    'à risque' => 'À risque',
    'à suivre' => 'À suivre',
];

// Construction de la requête SQL avec LEFT JOIN pour récupérer les noms au lieu des IDs
$query = "
    SELECT
        c.*,
        co.id AS contrat_ref_number
    FROM
        customers c
    LEFT JOIN
        contrats co ON c.contrat_id = co.id
    WHERE 1=1
";

$params = [];

if ($black_listFilter && array_key_exists($black_listFilter, $validBlackListOptions)) {
    $query .= " AND c.black_list = ?";
    $params[] = $black_listFilter;
}

if ($statusFilter && array_key_exists($statusFilter, $validStatusOptions)) {
    $query .= " AND c.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY $sortBy $order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des clients: " . $e->getMessage());
    // Pour le débogage, afficher le message d'erreur réel. À retirer en production.
    $message = ['Erreur de base de données. Veuillez réessayer plus tard. Détails: ' . $e->getMessage(), 'danger'];
}

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion Clients</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/styles.css">
</head>

<body>
    <?php require_once 'partials/_navbar.php'; ?>

    <div class="position-fixed bottom-0 end-0 p-2" style="z-index: 1100">
        <div id="mainToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true"
            <?php if (! empty($message) && is_array($message) && count($message) === 2): ?>
            data-toast-type="<?php echo htmlspecialchars($message[1]); ?>"
            <?php endif; ?>>
            <?php if (! empty($message) && is_array($message) && count($message) === 2): ?>
                <div class="d-flex">
                    <div class="toast-body" id="mainToastBody">
                        <?php echo htmlspecialchars($message[0]); // Affiche le texte du message 
                        ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <main class="container my-4">
        <h2>Liste des clients</h2>

        <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-2 p-3 ">
            <input type="hidden" name="route" value="customers">

            <div class="col-md-3">
                <label for="black_list"><b>Par black-list :</b></label>
                <select name="black_list" class="form-select">
                    <option value="">Tous les clients</option> <?php foreach ($validBlackListOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>"
                            <?php echo ($black_listFilter === $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status"><b>Par statut :</b></label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($validStatusOptions as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>"
                            <?php echo ($statusFilter === $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort_by"><b>Trier par :</b></label>
                <select name="sort_by" class="form-select">
                    <?php foreach ($validSortColumns as $value => $label): ?>
                        <option value="<?php echo htmlspecialchars($value); ?>"
                            <?php echo ($sortBy === $value) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="order"><b>Ordre :</b></label>
                <select name="order" class="form-select">
                    <option value="ASC" <?php echo ($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                    <option value="DESC" <?php echo ($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrer et Trier</button>
            </div>
        </form>

        <div class="table-container bg-dark-subtle shadow p-3">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Téléphone</th>
                        <th>E-mail</th>
                        <th>Statut</th>
                        <th>Bannis</th>
                        <th>Réf. Contrat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun client trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach ($customers as $row): ?>
                            <tr>
                                <td data-label="#"><?php echo $i++; ?></td>
                                <td data-label="Nom"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td data-label="Contact"><?php echo htmlspecialchars($row['refContact']); ?></td>
                                <td data-label="Téléphone"><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td data-label="E-mail"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td data-label="Statut"><?php echo htmlspecialchars($row['status']); ?></td>
                                <td data-label="Bannis"><?php echo htmlspecialchars($row['black_list']); ?></td>
                                <td data-label="Réf. Contrat"><?php echo htmlspecialchars($row['contrat_ref_number'] ?? 'N/A'); ?></td>
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
                if (toastBody && toastBody.innerHTML.trim() !== '') {
                    const toastType = toastElement.getAttribute('data-toast-type');

                    toastElement.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');

                    if (toastType === 'success') {
                        toastElement.classList.add('text-bg-success');
                    } else if (toastType === 'danger') {
                        toastElement.classList.add('text-bg-danger');
                    } else if (toastType === 'warning') {
                        toastElement.classList.add('text-bg-warning');
                    } else if (toastType === 'info') {
                        toastElement.classList.add('text-bg-info');
                    } else {
                        toastElement.classList.add('text-bg-primary');
                    }

                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: true,
                        delay: 4000
                    });
                    toast.show();
                }
            }
        });
    </script>
    <?php require_once 'partials/_footer.php'; ?>