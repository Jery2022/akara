<?php

    // Active l'affichage des erreurs pour le développement. A désactiver ou restreindre en production.
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

    // Génération du jeton CSRF
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Initialisation de la variable de message comme un tableau vide
    $message = [];

    // --- Gestion des messages Flash
    if (isset($_SESSION['flash_message'])) {
        // Un message flash est attendu sous la forme d'un tableau : ['texte', 'type']
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']); // Efface le message flash après l'affichage
    }

    // Filtrage et Tri des données de recettes
    $natureFilter   = $_GET['nature'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'date_recette'; // Tri par défaut par la date de recette
    $order          = $_GET['order'] ?? 'DESC';           // Ordre par défaut décroissant pour les dates

    // Définit les colonnes de tri valides comme un tableau associatif : 'nom_colonne_base' => 'Libellé à afficher'
    $validSortColumns = [
        'id'            => 'ID Recette',
        'produit_name'  => 'Nom du produit',
        'quantity'      => 'Quantité',
        'price'         => 'Prix unitaire',
        'total'         => 'Total',
        'customer_name' => 'Nom du client',
        'user_name'     => 'Nom de l\'employé',
        'contrat_id'    => 'ID Contrat',
        'nature'        => 'Nature',
        'category'      => 'Catégorie',
        'date_recette'  => 'Date de la recette',
    ];

    // Valide le paramètre sortBy
    if (! array_key_exists($sortBy, $validSortColumns)) {
        $sortBy = 'date_recette'; // Retourne à la valeur par défaut si invalide
    }

    // S'assure que l'ordre est soit ASC soit DESC
    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

    // Définit les options de filtre valides
    $validNatures = [
        'vente'    => 'Vente',
        'location' => 'Location',
    ];

    $validCategories = [
        'construction' => 'Construction',
        'sécurité'     => 'Sécurité',
        'hygiène'      => 'Hygiène',
        'entretien'    => 'Entretien',
        'logistique'   => 'Logistique',
        'mobilité'     => 'Mobilité',
    ];

    // Construction de la requête SQL
    // Jointures pour récupérer les noms au lieu des IDs
    $query = "
    SELECT
        r.*,
        p.name AS produit_name,
        c.name AS customer_name,
        u.pseudo AS user_name,
        co.ref AS contrat_ref
    FROM
        recettes r
    LEFT JOIN
        produits p ON r.produit_id = p.id
    LEFT JOIN
        customers c ON r.customer_id = c.id
    LEFT JOIN
        users u ON r.user_id = u.id
    LEFT JOIN
        contrats co ON r.contrat_id = co.id
    WHERE 1=1
";

    $params = [];

    if ($natureFilter && array_key_exists($natureFilter, $validNatures)) {
        $query .= " AND r.nature = ?";
        $params[] = $natureFilter;
    }

    if ($categoryFilter && array_key_exists($categoryFilter, $validCategories)) {
        $query .= " AND r.category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $recettes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des recettes : " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Recettes</title>
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
                    <?php echo htmlspecialchars($message[0]); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>
    </div>
</div>

<main class="container my-4">
    <h2>Liste des recettes</h2>

    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-2 p-3">
        <input type="hidden" name="route" value="recettes">

        <div class="col-md-2">
            <label for="nature"><b>Par nature :</b></label>
            <select name="nature" class="form-select">
                <option value="">Toutes les natures</option>
                <?php foreach ($validNatures as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>"
                        <?php echo($natureFilter === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="category"><b>Par catégorie :</b></label>
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <?php foreach ($validCategories as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>"
                        <?php echo($categoryFilter === $value) ? 'selected' : ''; ?>>
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
                <option value="ASC"                                                                                                                                             <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                 <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
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
                    <th>Prix unitaire</th>
                    <th>Total</th>
                    <th>Client</th>
                    <th>Nature</th>
                    <th>Catégorie</th>
                    <th>Date Recette</th>
                    <th>Employé</th>
                    <th>Contrat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recettes)): ?>
                    <tr>
                        <td colspan="11" class="text-center">Aucune recette trouvée.</td>
                    </tr>
                <?php else: ?>
<?php $i = 1; ?>
<?php foreach ($recettes as $row): ?>
                        <tr>
                            <td data-label="#"><?php echo $i++; ?></td>
                            <td data-label="Produit"><?php echo htmlspecialchars($row['produit_name'] ?? 'N/A'); ?></td>
                            <td data-label="Quantité"><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td data-label="Prix"><?php echo htmlspecialchars($row['price']); ?></td>
                            <td data-label="Total"><?php echo htmlspecialchars($row['total']); ?></td>
                            <td data-label="Client"><?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></td>
                            <td data-label="Nature"><?php echo htmlspecialchars($row['nature']); ?></td>
                            <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td data-label="Date Recette"><?php echo htmlspecialchars($row['date_recette']); ?></td>
                            <td data-label="Employé"><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                            <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_ref'] ?? 'N/A'); ?></td>
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