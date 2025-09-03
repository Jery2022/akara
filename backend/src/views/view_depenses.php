<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirection si l'utilisateur n'est pas admin ou employé
if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$pdo = getPDO();

// CSRF token (utile pour les formulaires POST sur cette page si vous en ajoutez)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation de la variable de message comme un tableau vide pour la cohérence avec le Toast
$message = ['', ''];

// --- Gestion des messages Flash de session (pour les redirections) ---
if (isset($_SESSION['flash_message'])) {
    // Le message flash est un tableau ['texte', 'type']
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Supprime le message après l'avoir affiché
}

// --- Filtrage et tri des dépenses ---
$natureFilter   = $_GET['nature'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$sortBy         = $_GET['sort_by'] ?? 'date_depense'; // Changement de la valeur par défaut pour une meilleure pertinence
$order          = $_GET['order'] ?? 'DESC';           // Changement de la valeur par défaut pour un tri plus récent en premier

// Validation des paramètres de tri
$validSortColumns = ['produit_id', 'user_id', 'contrat_id', 'suppliers_id', 'date_depense', 'quantity', 'price', 'total'];
if (! in_array($sortBy, $validSortColumns)) {
    $sortBy = 'date_depense'; // Valeur par défaut
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

// Validation des filtres
$validNatures    = ['achat', 'location'];
$validCategories = ['fournitures', 'équipement', 'services', 'maintenance', 'logistique'];

// Construction de la requête SQL avec LEFT JOIN pour récupérer les noms au lieu des IDs
$query = "
    SELECT
        d.*,
        p.name AS produit_name,
        u.pseudo AS user_pseudo,
        co.name AS contrat_number,
        s.name AS supplier_name
    FROM
        depenses d
    LEFT JOIN
        produits p ON d.produit_id = p.id
    LEFT JOIN
        users u ON d.user_id = u.id
    LEFT JOIN
        suppliers s ON d.suppliers_id = s.id
    LEFT JOIN
        contrats co ON d.contrat_id = co.id

    WHERE 1=1";

$params = [];

if ($natureFilter && in_array($natureFilter, $validNatures)) {
    $query .= " AND d.nature = ?";
    $params[] = $natureFilter;
}

if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
    $query .= " AND d.category = ?";
    $params[] = $categoryFilter;
}

// Le tri utilise la colonne validée
$query .= " ORDER BY $sortBy $order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $depenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des dépenses : " . $e->getMessage());
    $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
}

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Dépenses</title>
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
        <h2>Liste des dépenses</h2>

        <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
            <input type="hidden" name="route" value="depenses">

            <div class="col-md-2">
                <label for="nature"><b>Par nature :</b></label>
                <select name="nature" class="form-select">
                    <option value="">Toutes les natures</option>
                    <option value="achat" <?php echo ($natureFilter === "achat") ? 'selected' : ''; ?>>Achat</option>
                    <option value="location" <?php echo ($natureFilter === "location") ? 'selected' : ''; ?>>Location</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category"><b>Par catégorie :</b></label>
                <select name="category" class="form-select">
                    <option value="">Toutes les catégories</option>
                    <option value="fournitures" <?php echo ($categoryFilter === "fournitures") ? 'selected' : ''; ?>>Fournitures</option>
                    <option value="équipement" <?php echo ($categoryFilter === "équipement") ? 'selected' : ''; ?>>Équipement</option>
                    <option value="services" <?php echo ($categoryFilter === "services") ? 'selected' : ''; ?>>Services</option>
                    <option value="maintenance" <?php echo ($categoryFilter === "maintenance") ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="logistique" <?php echo ($categoryFilter === "logistique") ? 'selected' : ''; ?>>Logistique</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="sort_by"><b>Trier par :</b></label>
                <select name="sort_by" class="form-select">
                    <option value="date_depense"
                        <?php echo ($sortBy === 'date_depense') ? 'selected' : ''; ?>>Date</option>
                    <option value="user_id"
                        <?php echo ($sortBy === 'user_id') ? 'selected' : ''; ?>>Utilisateur</option>
                    <option value="produit_id"
                        <?php echo ($sortBy === 'produit_id') ? 'selected' : ''; ?>>Produit</option>
                    <option value="suppliers_id"
                        <?php echo ($sortBy === 'suppliers_id') ? 'selected' : ''; ?>>Fournisseur</option>
                    <option value="total"
                        <?php echo ($sortBy === 'total') ? 'selected' : ''; ?>>Total</option>
                    <option value="quantity"
                        <?php echo ($sortBy === 'quantity') ? 'selected' : ''; ?>>Quantité</option>
                    <option value="price"
                        <?php echo ($sortBy === 'price') ? 'selected' : ''; ?>>Prix</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="order"><b>Par ordre :</b></label>
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
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Total</th>
                        <th>Fournisseur</th>
                        <th>Nature</th>
                        <th>Catégorie</th>
                        <th>Date Dépense</th>
                        <th>Utilisateur</th>
                        <th>Contrat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($depenses)): ?>
                        <tr>
                            <td colspan="12" class="text-center">Aucune dépense trouvée.</td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; ?>
                        <?php foreach ($depenses as $row): ?>
                            <tr>
                                <td data-label="#"><?php echo $i++; ?></td>
                                <td data-label="Produit"><?php echo htmlspecialchars($row['produit_name'] ?? 'N/A') ?></td>
                                <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']) ?></td>
                                <td data-label="Prix"><?php echo htmlspecialchars($row['price']) ?></td>
                                <td data-label="Total"><?php echo htmlspecialchars($row['total']) ?></td>
                                <td data-label="Fournisseur"><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A') ?></td>
                                <td data-label="Nature"><?php echo htmlspecialchars($row['nature']) ?></td>
                                <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']) ?></td>
                                <td data-label="Date Dépense"><?php echo htmlspecialchars($row['date_depense']) ?></td>
                                <td data-label="Utilisateur"><?php echo htmlspecialchars($row['user_pseudo'] ?? 'N/A') ?></td>
                                <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_number'] ?? 'N/A') ?></td>
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
                    toastElement.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info'); // Ajout de warning/info si utilisés

                    if (toastType === 'success') {
                        toastElement.classList.add('text-bg-success');
                    } else if (toastType === 'danger') {
                        toastElement.classList.add('text-bg-danger');
                    } else if (toastType === 'warning') { // Ajout pour les messages d'avertissement
                        toastElement.classList.add('text-bg-warning');
                    } else if (toastType === 'info') { // Ajout pour les messages d'information
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