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

// CSRF token (utile pour les formulaires POST )
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation de la variable de message comme un tableau vide pour la cohérence avec le Toast
$message = [];

// --- Gestion des messages Flash de session (pour les redirections) ---
if (isset($_SESSION['flash_message'])) {
    // Le message flash est un tableau ['texte', 'type']
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Supprime le message après l'avoir affiché
}

// --- Filtrage et tri des achats ---
$typeFilter     = $_GET['type'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$sortBy         = $_GET['sort_by'] ?? 'date_achat'; // Changement de la valeur par défaut
$order          = $_GET['order'] ?? 'DESC';         // Changement de la valeur par défaut pour un tri plus récent en premier

// Validation des paramètres de tri
$validSortColumns = ['contrat_id', 'user_id', 'suppliers_id', 'amount', 'date_achat'];
if (! in_array($sortBy, $validSortColumns)) {
    $sortBy = 'date_achat';
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

// Validation des filtres
$validTypes      = ['virement', 'chèque', 'espèces'];
$validCategories = [
    'fournitures',
    'équipement',
    'services',
    'maintenance',
    'logistique',
    'électricité',
    'plomberie',
    'chauffage',
    'climatisation',
    'ventilation',
    'sécurité',
    'informatique',
    'bureautique',
    'matériel de chantier',
    'outillage',
    'mobiliers',
    'matériaux de construction'
];

// Construction de la requête SQL avec LEFT JOIN pour récupérer les noms au lieu des IDs
$query = "
    SELECT
        a.*,
        co.name AS contrat_name,
        u.pseudo AS user_pseudo,
        s.name AS supplier_name
    FROM
        achats a
    LEFT JOIN
        contrats co ON a.contrat_id = co.id
    LEFT JOIN
        users u ON a.user_id = u.id
    LEFT JOIN
        suppliers s ON a.suppliers_id = s.id
    WHERE 1=1";

$params = [];

if ($typeFilter && in_array($typeFilter, $validTypes)) {
    $query .= " AND a.type = ?";
    $params[] = $typeFilter;
}

if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
    $query .= " AND a.category = ?";
    $params[] = $categoryFilter;
}

// Le tri utilise la colonne validée
$query .= " ORDER BY $sortBy $order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $achats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des achats : " . $e->getMessage());
    $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
}

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Achats</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/css/styles.css">
</head>

<body>
    <?php require_once 'partials/_navbar.php'; ?>

    <div class="position-fixed bottom-0 end-0 p-2" style="z-index: 1100">
        <!-- Toast Bootstrap pour les messages -->
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
        <h2>Liste des achats</h2>

        <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
            <input type="hidden" name="route" value="achats">

            <div class="col-md-2">
                <label for="type"><b>Par type :</b></label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="virement"
                        <?php echo ($typeFilter === "virement") ? 'selected' : ''; ?>>Virement</option>
                    <option value="chèque"
                        <?php echo ($typeFilter === "chèque") ? 'selected' : ''; ?>>Chèque</option>
                    <option value="espèces"
                        <?php echo ($typeFilter === "espèces") ? 'selected' : ''; ?>>Espèces</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category"><b>Par catégorie :</b></label>
                <select name="category" class="form-select">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($validCategories as $categoryValue): ?>
                        <option value="<?php echo htmlspecialchars($categoryValue); ?>"
                            <?php echo ($categoryFilter === $categoryValue) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($categoryValue)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="sort_by"><b>Trier par :</b></label>
                <select name="sort_by" class="form-select">
                    <option value="date_achat"
                        <?php echo ($sortBy === 'date_achat') ? 'selected' : ''; ?>>Date Achat</option>
                    <option value="amount"
                        <?php echo ($sortBy === 'amount') ? 'selected' : ''; ?>>Montant</option>
                    <option value="suppliers_id"
                        <?php echo ($sortBy === 'suppliers_id') ? 'selected' : ''; ?>>Fournisseur</option>
                    <option value="user_id"
                        <?php echo ($sortBy === 'user_id') ? 'selected' : ''; ?>>Utilisateur (Reçu par)</option>
                    <option value="contrat_id"
                        <?php echo ($sortBy === 'contrat_id') ? 'selected' : ''; ?>>Contrat</option>
                    <option value="id"
                        <?php echo ($sortBy === 'id') ? 'selected' : ''; ?>>ID Achat</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="order"><b>Par ordre :</b></label>
                <select name="order" class="form-select">
                    <option value="ASC"
                        <?php echo ($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                    <option value="DESC"
                        <?php echo ($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
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
                        <th>Contrat</th>
                        <th>Reçu par</th>
                        <th>Fournisseur</th>
                        <th>Montant</th>
                        <th>Type</th>
                        <th>Catégorie</th>
                        <th>Date achat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($achats)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Aucun achat trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach ($achats as $row): ?>
                            <tr>
                                <td data-label="#"><?php echo $i++; ?></td>
                                <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_name'] ?? 'N/A'); ?></td>
                                <td data-label="Reçu par"><?php echo htmlspecialchars($row['user_pseudo'] ?? 'N/A'); ?></td>
                                <td data-label="Fournisseur"><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                                <td data-label="Montant"><?php echo htmlspecialchars($row['amount']); ?></td>
                                <td data-label="Type"><?php echo htmlspecialchars($row['type']); ?></td>
                                <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td data-label="Date achat"><?php echo htmlspecialchars($row['date_achat']); ?></td>
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
                        // Couleur par défaut si le type n'est pas reconnu ou non défini
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