<?php
    session_start();
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once '../db.php';
    require_once '../functions.php';
    require_once 'partials/_header.php';

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message         = '';
    $detailsFactures = [];

    // Filtrage et tri des factures
    $statusFilter = $_GET['status'] ?? '';
    $avanceFilter = $_GET['avance_status'] ?? '';
    $sortBy       = $_GET['sort_by'] ?? 'date_facture';
    $order        = $_GET['order'] ?? 'ASC';

    // Validation des paramètres de tri
    $validSortColumns = ['id', 'date_facture', 'customer_id', 'amount_total'];
    if (! in_array($sortBy, $validSortColumns)) {
        $sortBy = 'date_facture'; // Valeur par défaut
    }

    $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

    // Validation des filtres
    $validStatus      = ['payée', 'en attente', 'annulée'];
    $avanceStatusList = ['oui', 'non'];

    // Construction de la requête SQL
    $query  = "SELECT * FROM factures WHERE 1=1";
    $params = [];

    if ($avanceFilter && in_array($avanceFilter, $avanceStatusList)) {
        $query .= " AND avance_status = ?";
        $params[] = $avanceFilter;
    }

    if ($statusFilter && in_array($statusFilter, $validStatus)) {
        $query .= " AND status = ?";
        $params[] = $statusFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des factures.</div>';
    }

?>
<title>Gestion Factures</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des factures</h2>
    <?php echo $message ?>

    <!-- Formulaire de filtre -->
    <form method="get" class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3" >
        <div class="col-md-3">
            <select name="avance_status" class="form-select">
                <option value="">Toutes les avances</option>
                <option value="oui"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo($avanceFilter === "oui") ? 'selected' : ''; ?>>Oui</option>
                <option value="non"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo($avanceFilter === "non") ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="payée"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     <?php echo($statusFilter === "payée") ? 'selected' : ''; ?>>Payée</option>
                <option value="en attente"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($statusFilter === "en attente") ? 'selected' : ''; ?>>En attente</option>
                <option value="annulée"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo($statusFilter === "annulée") ? 'selected' : ''; ?>>Annulée</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <!-- Tableau des factures -->
    <div class="table-container bg-dark-subtle shadow p-3">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Montant HT</th>
            <th>TVA</th>
            <th>CSS</th>
            <th>Montant TTC</th>
            <th>Avance</th>
            <th>Statut</th>
            <th>Voir détails</th>
        </tr>
        </thead>
         <tbody>
        <?php if (empty($factures)): ?>
            <tr>
                <td colspan="9" class="text-center">Aucune facture trouvée.</td>
            </tr>
        <?php else: ?>
<?php foreach ($factures as $row): ?>
                <tr>
                    <td data-label="ID"><?php echo htmlspecialchars($row['id']) ?></td>
                    <td data-label="Date"><?php echo htmlspecialchars($row['date_facture']) ?></td>
                    <td data-label="Montant HT"><?php echo htmlspecialchars($row['amount_total']) ?></td>
                    <td data-label="TVA"><?php echo htmlspecialchars($row['amount_tva']) ?></td>
                    <td data-label="CSS"><?php echo htmlspecialchars($row['amount_css']) ?></td>
                    <td data-label="Montant TTC"><?php echo htmlspecialchars($row['amount_ttc']) ?></td>
                    <td data-label="Avance"><?php echo htmlspecialchars($row['avance_status'] ?? '') ?></td>
                    <td data-label="Statut"><?php echo htmlspecialchars($row['status'] ?? '') ?></td>
                    <td>
                        <button
                            class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#factureModal"
                            data-id="<?php echo $row['id'] ?>"
                            >Voir détails
                        </button>
                    </td>
                </tr>
<?php endforeach; ?>
<?php endif; ?>
        </tbody>
    </table>
</div>

        <!-- Génération des modales en dehors du tableau -->
<?php if (! empty($factures)): ?>
<?php foreach ($factures as $row): ?>
        <!-- Modal pour afficher les détails de la facture -->
        <div class="modal fade custom-modal" id="factureModal" tabindex="-1" aria-labelledby="factureModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="factureModalLabel">Détails </h5>
                        <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php
                            $detailsFactures = getDetailsFactures($row['id']);
                            if (! is_array($detailsFactures)) {
                                $detailsFactures = [];
                            }
                            $tableClients         = 'customers';
                            $tableFournisseur     = 'suppliers';
                            $datasTablesCustomers = getDatasTableByID($tableClients, $row['id']);
                            $datasTablesSuppliers = getDatasTableByID($tableFournisseur, $row['id']);

                            if (! is_array($datasTablesCustomers)) {
                                $datasTablesCustomers = [];
                            }
                            if (! is_array($datasTablesSuppliers)) {
                                $datasTablesSuppliers = [];
                            }
                        ?>
                        <div class="container py-1">
                            <div class="d-flex justify-content-between mb-3">
                                <?php if (empty($datasTablesSuppliers)): ?>
                                    <div>
                                        <span class="text-center">Aucune données disponibles.</span>
                                    </div>
                                <?php else: ?>
<?php foreach ($datasTablesSuppliers as $suppliers): ?>
                                <div class="d-flex flex-column justify-content-start p-1">
                                    <h5><?php echo htmlspecialchars($suppliers['refContact']); ?></h5>
                                    <div>Entreprise:
                                        <?php echo htmlspecialchars($suppliers['name']); ?></div>
                                    <div>Adresse :
                                        <?php echo htmlspecialchars($suppliers['address']); ?></div>
                                    <div>Tél. :
                                        <?php echo htmlspecialchars($suppliers['phone']); ?></div>
                                    <div>E-mail :
                                        <?php echo htmlspecialchars($suppliers['email']); ?></div>
                                </div>
                                <div class="d-flex flex-row justify-content-end p-1"><h3>FACTURE</h3></div>
                                <?php endforeach; ?>
<?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-end mb-3 gap-1">
                                <div class="col-6 d-flex flex-column">
                                    <div class="row d-flex bg-primary">
                                        <div class="col-6 d-flex justify-content-center text-white p-1"><h6>N° FACTURE</h6></div>
                                        <div class="col-6 d-flex justify-content-center text-white p-1"><h6>DATE</h6></div>
                                    </div>
                                    <div class="row d-flex">
                                        <div class="col-6 d-flex justify-content-center">
                                            <?php echo htmlspecialchars($row['id']); ?></div>
                                        <div class="col-6 d-flex justify-content-center">
                                            <?php echo htmlspecialchars($row['date_facture']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-3 gap-1">
                                <div  class="col-5 d-flex flex-column">
                                    <div class=" bg-primary">
                                        <div class="d-flex justify-content-center text-white p-1"><h6>FACTURE A</h6></div>
                                    </div>
                                    <?php if (empty($datasTablesSuppliers)): ?>
                                    <div>
                                        <span class="text-center">Aucune données disponibles.</span>
                                    </div>
                                <?php else: ?>
<?php foreach ($datasTablesCustomers as $customers): ?>
                                    <div class="d-flex flex-column">
                                        <div>Nom :
                                            <?php echo htmlspecialchars($customers['refContact']); ?></div>
                                        <div>Entreprise :
                                            <?php echo htmlspecialchars($customers['name']); ?></div>
                                        <div>Adresse :
                                            <?php echo htmlspecialchars($customers['address']); ?></div>
                                        <div>Ville :
                                            <?php echo htmlspecialchars($customers['ville']); ?></div>
                                        <div>Tél. :
                                            <?php echo htmlspecialchars($customers['phone']); ?></div>
                                        <div>E-mail :
                                            <?php echo htmlspecialchars($customers['email']); ?></div>
                                    </div>
                                    <?php endforeach; ?>
<?php endif; ?>
                                </div>
                                <div class="col-6 d-flex flex-column">
                                    <div class="row d-flex bg-primary">
                                        <div class="col-6 d-flex justify-content-center text-white p-1"><h6>REF. CLIENT</h6></div>
                                        <div class="col-6 d-flex justify-content-center text-white p-1"><h6>CONDITIONS</h6></div>
                                    </div>
                                    <div class="row d-flex">
                                        <div class="col-6 d-flex justify-content-center">
                                            <?php echo htmlspecialchars($row['customer_id']); ?></div>
                                        <div class="col-6 d-flex justify-content-center">Paiement dû à la réception</div>
                                    </div>
                                </div>
                            </div>
                            <!-- <h2>Détails de la facture</h2> -->
                            <table class="table table-border mt-2">
                                <thead class="table-primary ">
                                    <tr>
                                        <th>ID</th>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>P.U.</th>
                                        <th>Montant HT</th>
                                        <th>TVA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($detailsFactures)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun détail trouvé pour cette facture.</td>
                                    </tr>
                                <?php else: ?>
<?php foreach ($detailsFactures as $details): ?>
                                        <tr>
                                            <td data-label="ID"><?php echo htmlspecialchars($details['id']) ?></td>
                                            <td data-label="Produit"><?php echo htmlspecialchars($details['produit_id']) ?></td>
                                            <td data-label="Quantité"><?php echo htmlspecialchars($details['quantity']) ?></td>
                                            <td data-label="P.U."><?php echo htmlspecialchars($details['price_unit']) ?></td>
                                            <td data-label="Montant HT"><?php echo htmlspecialchars($details['amount']) ?></td>
                                            <td data-label="TVA"><?php echo htmlspecialchars($details['amount_tva']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
<?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column gap-2 p-2">
                            <div class="d-flex justify-content-end">Montant Total HT :
                                <?php echo htmlspecialchars($row['amount_total']); ?> XAF</div>
                            <div class="d-flex justify-content-end">TVA (18%) :
                                <?php echo htmlspecialchars($row['amount_tva']); ?> XAF</div>
                            <div class="d-flex justify-content-end">CSS (1%) :
                                <?php echo htmlspecialchars($row['amount_css']); ?> XAF</div>
                            <div class="d-flex justify-content-end"><h6>Montant Total TTC :
                                <?php echo htmlspecialchars($row['amount_ttc']); ?> XAF</h6></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
                            </main>
    <?php endforeach; ?>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'partials/_footer.php'; ?>
