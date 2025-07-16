<?php

    // Active l'affichage des erreurs pour le développement. Pensez à le désactiver ou le restreindre en production.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // --- Sécurité et Authentification ---
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once __DIR__ . '/../db.php';

    $pdo = getPDO();

    // Génération du jeton CSRF (bonne pratique pour les formulaires POST)
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Initialisation de la variable de message comme un tableau vide pour la cohérence avec le Toast de Bootstrap
    $message = [];

    // --- Gestion des messages Flash (pour les messages venant des redirections) ---
    if (isset($_SESSION['flash_message'])) {
        // Un message flash est attendu sous la forme d'un tableau : ['texte', 'type']
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']); // Efface le message flash après l'affichage
    }

    // --- Filtrage et Tri des données de stock ---
    $rentabilityFilter    = $_GET['rentability'] ?? '';
    $classificationFilter = $_GET['classification'] ?? '';
    $sortBy               = $_GET['sort_by'] ?? 'produit_name'; // Tri par défaut par le nom du produit
    $order                = $_GET['order'] ?? 'ASC';            // Ordre par défaut croissant

    // Définit les colonnes de tri valides comme un tableau associatif : 'nom_colonne_base' => 'Libellé à afficher'
    // Ajout des colonnes pour les noms de produit, fournisseur, entrepôt si elles sont jointes
    $validSortColumns = [
        'id'             => 'ID Stock',
        'min'            => 'Seuil d\'alerte',
        'quantity'       => 'Quantité actuelle',
        'produit_name'   => 'Nom du produit',
        'supplier_name'  => 'Nom du fournisseur',
        'entrepot_name'  => 'Nom de l\'entrepôt',
        'rentability'    => 'Rentabilité',
        'classification' => 'Classification',
    ];

    // Valide le paramètre sortBy en utilisant array_key_exists
    if (! array_key_exists($sortBy, $validSortColumns)) {
        $sortBy = 'produit_name'; // Retourne à la valeur par défaut si invalide
    }

    // S'assure que l'ordre est soit ASC soit DESC
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

    // Définit les options de filtre valides comme des tableaux associatifs pour une génération HTML plus propre
    $validRentabilities = [
        'forte'  => 'Forte',
        'bonne'  => 'Bonne',
        'faible' => 'Faible',
    ];

    $validClassifications = [
        'A' => 'Classification A',
        'B' => 'Classification B',
        'C' => 'Classification C',
    ];

    // --- Construction de la requête SQL ---
    $query = "
    SELECT
        s.*,
        p.name AS produit_name,
        sup.name AS supplier_name,
        ent.name AS entrepot_name
    FROM
        stock s
    LEFT JOIN
        produits p ON s.produit_id = p.id
    LEFT JOIN
        suppliers sup ON s.supplier_id = sup.id
    LEFT JOIN
        entrepots ent ON s.entrepot_id = ent.id
    WHERE 1=1
";

    $params = [];

    if ($rentabilityFilter && array_key_exists($rentabilityFilter, $validRentabilities)) {
        $query .= " AND s.rentability = ?";
        $params[] = $rentabilityFilter;
    }

    if ($classificationFilter && array_key_exists($classificationFilter, $validClassifications)) {
        $query .= " AND s.classification = ?";
        $params[] = $classificationFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des stocks: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Stocks</title>
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
                    <?php echo htmlspecialchars($message[0]); // Affiche le texte du message ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<main class="container my-4">
    <h2>Liste des stocks</h2>

    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-2 p-3">
        <input type="hidden" name="route" value="stock">

        <div class="col-md-2">
            <label for="rentability"><b>Par rentabilité :</b></label>
            <select name="rentability" class="form-select">
                <option value="">Toutes les rentabilités</option>
                <?php foreach ($validRentabilities as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>"
                        <?php echo($rentabilityFilter === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="classification"><b>Par classification :</b></label>
            <select name="classification" class="form-select">
                <option value="">Toutes les classifications</option>
                <?php foreach ($validClassifications as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>"
                        <?php echo($classificationFilter === $value) ? 'selected' : ''; ?>>
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
                        <?php echo($sortBy === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="order"><b>Ordre :</b></label>
            <select name="order" class="form-select">
                <option value="ASC"                                    <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                     <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
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
                    <th>Quantité actuelle</th>
                    <th>Unité</th>
                    <th>Seuil d'alerte</th>
                    <th>Rentabilité</th>
                    <th>Classification</th>
                    <th>Fournisseur</th>
                    <th>Entrepôt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stock)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Aucun stock trouvé.</td>
                    </tr>
                <?php else: ?>
<?php $i = 1; ?>
<?php foreach ($stock as $row): ?>
                        <tr>
                            <td data-label="#"><?php echo $i++; ?></td>
                            <td data-label="Produit"><?php echo htmlspecialchars($row['produit_name'] ?? 'N/A'); ?></td>
                            <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td data-label="Unité"><?php echo htmlspecialchars($row['unit']); ?></td>
                            <td data-label="Seuil d'alerte"><?php echo htmlspecialchars($row['min']); ?></td>
                            <td data-label="Rentabilité"><?php echo htmlspecialchars($row['rentability']); ?></td>
                            <td data-label="Classification"><?php echo htmlspecialchars($row['classification']); ?></td>
                            <td data-label="Fournisseur"><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                            <td data-label="Entrepôt"><?php echo htmlspecialchars($row['entrepot_name'] ?? 'N/A'); ?></td>
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

                const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 4000 });
                toast.show();
            }
        }
    });
</script>
<?php require_once 'partials/_footer.php'; ?>