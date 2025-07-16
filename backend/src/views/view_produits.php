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

    $message = [];

    // Filtrage et tri des produits
    $provenanceFilter    = $_GET['provenance'] ?? '';
    $disponibilityFilter = $_GET['disponibility'] ?? '';
    $categoryFilter      = $_GET['category'] ?? '';
    $sortBy              = $_GET['sort_by'] ?? 'name';
    $order               = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'name', 'created_at', 'price', 'supplier_id', 'entrepot_id'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'name'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validProvenances     = ['local', 'etranger'];
    $validDisponibilities = ['oui', 'non'];
    $validCategories      = [
        'Matériaux de construction', 'Matériel de chantier',
        'Outillages', 'Équipement de sécurité', 'Équipement de bureau',
        'Engins et équipements', 'Produits de finition', 'Équipements électriques',
        'Équipements de plomberie', 'Équipements de chauffage',
        'Équipements de climatisation', 'Équipements de ventilation',
        'Équipements sanitaires', 'Produits de second œuvre',
        'Voirie et assainissement', 'Produits de nettoyage',
        'Équipements de signalisation', 'Équipements de levage',
    ];

    // Construction de la requête SQL
    $query = "SELECT * FROM produits WHERE 1=1";

    $params = [];

    if ($provenanceFilter && in_array($provenanceFilter, $validProvenances)) {
        $query .= " AND provenance = ?";
        $params[] = $provenanceFilter;
    }

    if ($disponibilityFilter && in_array($disponibilityFilter, $validDisponibilities)) {
        $query .= " AND disponibility = ?";
        $params[] = $disponibilityFilter;
    }

    if ($categoryFilter && in_array($categoryFilter, $validCategories)) {
        $query .= " AND category = ?";
        $params[] = $categoryFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur PDO lors de la récupération des achats : " . $e->getMessage());
        $message = ['Erreur de serveur. Veuillez réessayer plus tard.', 'danger'];
    }

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Produits</title>
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
    <h2>Liste des produits</h2>
    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
        <!-- Champ caché pour le routeur -->
        <input type="hidden" name="route" value="produits">

        <div class="col-md-2">
            <label for="provenance"><b>Par provenance :</b></label>
            <select name="provenance" class="form-select">
                <option value="">Toutes les provenances</option>
                <option value="local"
                <?php echo($provenanceFilter === "local") ? 'selected' : ''; ?>>Local</option>
                <option value="etranger"
                <?php echo($provenanceFilter === "etranger") ? 'selected' : ''; ?>>Etranger</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="disponibility"><b>Par disponibilité :</b></label>
            <select name="disponibility" class="form-select">
                <option value="">Toutes les disponibilités</option>
                <option value="oui"
                <?php echo($disponibilityFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"
                <?php echo($disponibilityFilter === "non") ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="category"><b>Par catégorie :</b></label>
            <select name="category" class="form-select">
                <option value="">Toutes les catégories</option>
                <?php foreach ($validCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"
                        <?php echo($categoryFilter === $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="order"><b>Tri par ordre :</b></label>
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

    <!-- Tableau des produits -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Unité</th>
            <th>Prix</th>
            <th>Provenance</th>
            <th>Disponible</th>
            <th>Délai</th>
            <th>Catégorie</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($produits)): ?>
            <tr>
                <td colspan="9" class="text-center">Aucun produit trouvé.</td>
            </tr>
        <?php else: ?>
<?php
    $i = 1;
foreach ($produits as $row): ?>
                <tr>
                    <td data-label="#"><?php echo $i++ ?></td>
                    <td data-label="Nom"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td data-label="Unité"><?php echo htmlspecialchars($row['unit']); ?></td>
                    <td data-label="Prix"><?php echo htmlspecialchars($row['price']); ?></td>
                    <td data-label="Provenance"><?php echo htmlspecialchars($row['provenance']); ?></td>
                    <td data-label="Disponible"><?php echo htmlspecialchars($row['disponibility']); ?></td>
                    <td data-label="Délai"><?php echo htmlspecialchars($row['delai_livraison']); ?></td>
                    <td data-label="Catégorie"><?php echo htmlspecialchars($row['category']); ?></td>
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
