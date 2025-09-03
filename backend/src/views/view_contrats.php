<?php

if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$pdo = getPDO();

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Filtrage et tri des contrats
$typeFilter   = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$sortBy       = $_GET['sort_by'] ?? 'montant';
$order        = $_GET['order'] ?? 'ASC';

// Validation des paramètres de tri
$validSortColumns = ['montant', 'id', 'date_debut', 'date_fin', 'status', 'type'];
if (! in_array($sortBy, $validSortColumns)) {
    $sortBy = 'montant'; // Valeur par défaut
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

// Validation des filtres
$validTypes    = ['client', 'fournisseur', 'employe'];
$validStatuses = ['en cours', 'terminé', 'annulé'];

// Construction de la requête SQL
$query  = "SELECT * FROM contrats WHERE 1=1";
$params = [];

if ($typeFilter && in_array($typeFilter, $validTypes)) {
    $query .= " AND type = ?";
    $params[] = $typeFilter;
}

if ($statusFilter && in_array($statusFilter, $validStatuses)) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY $sortBy $order";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $contrats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur PDO lors de la récupération des contrats: " . $e->getMessage());
    $message = ['Erreur de base de données. Veuillez réessayer plus tard.', 'danger'];
}

?>
<?php require_once 'partials/_header.php'; ?>
<title>Gestion des Contrats</title>
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
                        <?php echo htmlspecialchars($message[0]); // Affiche le texte du message 
                        ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <main class="container my-4">
        <h2>Liste des contrats</h2>

        <!-- Formulaire de filtre -->
        <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3">
            <!-- Champ caché pour le routeur -->
            <input type="hidden" name="route" value="contrats">

            <div class="col-md-3">
                <label for="type"><b>Par type :</b></label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="client"
                        <?php echo ($typeFilter === 'client') ? 'selected' : ''; ?>>Client</option>
                    <option value="fournisseur"
                        <?php echo ($typeFilter === 'fournisseur') ? 'selected' : ''; ?>>Fournisseur</option>
                    <option value="employe"
                        <?php echo ($typeFilter === 'employe') ? 'selected' : ''; ?>>Employé</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status"><b>Par statut :</b></label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="en cours"
                        <?php echo ($statusFilter === "en cours") ? 'selected' : ''; ?>>En cours</option>
                    <option value="terminé"
                        <?php echo ($statusFilter === 'terminé') ? 'selected' : ''; ?>>Terminé</option>
                    <option value="annulé"
                        <?php echo ($statusFilter === 'annulé') ? 'selected' : ''; ?>>Annulé</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="order"><b>Trier par ordre :</b></label>
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

        <!-- Tableau des contrats -->
        <div class="table-container bg-dark-subtle shadow p-3">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Objet</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Statut</th>
                        <th>Montant</th>
                        <th>Signé le</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contrats)): ?>
                        <tr>
                            <td colspan="10" class="text-center">Aucun contrat trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $i = 1;
                        foreach ($contrats as $row): ?>
                            <tr>
                                <td data-label="#"><?php echo $i++; ?></td>
                                <td data-label="Référence"><?php echo htmlspecialchars($row['name'] ?? '') ?></td>
                                <td data-label="Objet"><?php echo htmlspecialchars($row['objet']) ?></td>
                                <td data-label="Date début"><?php echo htmlspecialchars($row['date_debut']) ?></td>
                                <td data-label="Date fin"><?php echo htmlspecialchars($row['date_fin']) ?></td>
                                <td data-label="Statut"><?php echo htmlspecialchars($row['status']) ?></td>
                                <td data-label="Montant"><?php echo htmlspecialchars($row['montant']) ?></td>
                                <td data-label="Signé le"><?php echo htmlspecialchars($row['date_signature']) ?></td>
                                <td data-label="Type"><?php echo htmlspecialchars($row['type']) ?></td>
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

                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: true,
                        delay: 4000
                    });
                    toast.show();
                }
            }
        });
    </script>
    <?php
    require_once 'partials/_footer.php';
    ?>