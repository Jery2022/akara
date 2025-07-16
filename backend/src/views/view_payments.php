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

    // Filtrage et tri des paiements
    $typeFilter     = $_GET['type'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $sortBy         = $_GET['sort_by'] ?? 'customer_id';
    $order          = $_GET['order'] ?? 'ASC';

                                                                                                                                // Validation des paramètres de tri
    $validSortColumns = ['id', 'contrat_id', 'user_id', 'customer_id', 'amount', 'customer_name', 'employee_name', 'category']; // Ajout de 'category' pour le tri
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'customer_id'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validTypes      = ['virement', 'chèque', 'espèces'];
    $validCategories = ['travaux', 'services'];

    // Construction de la requête SQL avec jointures
    $query = "
    SELECT
        p.*,
        c.name AS customer_name,
        e.name AS employee_name,
        co.ref AS contrat_ref -- CORRECTION ICI : Utilisez 'co.ref'
    FROM
        payments p
    INNER JOIN
        customers c ON p.customer_id = c.id
    INNER JOIN
        users u ON p.user_id = u.id
    INNER JOIN
        employees e ON u.employee_id = e.id
    INNER JOIN
        contrats co ON p.contrat_id = co.id
    WHERE 1=1
";

    $params = [];

    if ($typeFilter && in_array($typeFilter, $validTypes)) {
        $query .= " AND p.type = ?";
        $params[] = $typeFilter;
    }

    if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
        $query .= " AND p.category = ?";
        $params[] = $categoryFilter;
    }

    // Assurez-vous d'ajouter 'contrat_ref' à $validSortColumns si vous voulez trier par ce champ.
    $validSortColumns = ['id', 'contrat_id', 'user_id', 'customer_id', 'amount', 'customer_name', 'employee_name', 'category', 'contrat_ref'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'customer_id'; // Valeur par défaut
    }

    if ($sortBy === 'customer_name') {
        $query .= " ORDER BY c.name $order";
    } elseif ($sortBy === 'employee_name') {
        $query .= " ORDER BY e.name $order";
    } elseif ($sortBy === 'category') {
        $query .= " ORDER BY p.category $order";
    } elseif ($sortBy === 'contrat_ref') { // Condition de tri pour la référence du contrat
        $query .= " ORDER BY co.ref $order";
    } else {
        $query .= " ORDER BY p.$sortBy $order";
    }

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des paiements: " . $e->getMessage());
        $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
    }
?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Paiements</title>
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
    <h2>Liste des paiements</h2>

    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3" >
        <!-- Champ caché pour le routeur -->
        <input type="hidden" name="route" value="payments">

        <div class="col-md-3">
            <label for="type"><b>Par type :</b></label>
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <option value="virement"
                  <?php echo($typeFilter === "virement") ? 'selected' : ''; ?>>Virement</option>
                <option value="chèque"
                  <?php echo($typeFilter === "chèque") ? 'selected' : ''; ?>>Chèque</option>
                <option value="espèces"
                  <?php echo($typeFilter === "espèces") ? 'selected' : ''; ?>>Espèces</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="category"><b>Par catégorie :</b></label>
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <option value="travaux"
                  <?php echo($categoryFilter === "travaux") ? 'selected' : ''; ?>>Travaux</option>
                <option value="services"
                  <?php echo($categoryFilter === "services") ? 'selected' : ''; ?>>Services</option>
            </select>
        </div>

        <div class="col-md-2">
            <label for="order"><b>Trier par :</b></label>
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

    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Contrat</th>
                <th>Reçu par</th>
                <th>Client</th>
                <th>Montant</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Date paiement</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" class="text-center">Aucun paiement trouvé.</td>
                </tr>
            <?php else: ?>
<?php
    $i = 1;
foreach ($payments as $row): ?>
                    <tr>
                        <td data-label="#"><?php echo $i++ ?></td>
                        <td data-label="Contrat"><?php echo htmlspecialchars($row['contrat_ref']) ?></td>
                        <td data-label="Reçu par"><?php echo htmlspecialchars($row['employee_name']) ?></td>
                        <td data-label="Client"><?php echo htmlspecialchars($row['customer_name']) ?></td>
                        <td data-label="Montant"><?php echo htmlspecialchars($row['amount']) ?></td>
                        <td data-label="Type"><?php echo htmlspecialchars($row['type']) ?></td>
                        <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']) ?></td>
                        <td data-label="Date paiement"><?php echo htmlspecialchars($row['date_payment']) ?></td>
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