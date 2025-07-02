<?php
    session_start();
    if (! isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'employe')) {
        header('Location: ../login.php');
        exit;
    }

    require_once '../../db.php';
    require_once '../functions.php';
    require_once 'partials/_header.php';

    $pdo = getPDO();

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

                                              // Validation des filtres
    $validStatus = ['fournisseur', 'client']; // Assurez-vous que les valeurs correspondent à celles de votre base de données

    // Filtrage
    $typeFilter = $_GET['type'] ?? '';

    if ($typeFilter !== '' && ! in_array($typeFilter, $validStatus)) {
        $message = '<div class="alert alert-danger">Erreur serveur.</div>';
    }

    // Récupération des quittances
    $sql = "SELECT * FROM quittances" . ($typeFilter ? " WHERE type = :type" : "");
    try {
        $stmt = $pdo->prepare($sql);
        if ($typeFilter) {
            $stmt->bindParam(':type', $typeFilter);
        }
        $stmt->execute();
        $quittances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des quittances.</div>';
    }

?>
    <title>Gestion des Quittances</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
<?php require_once 'partials/_navbar.php'; ?>
<main class="container my-4">
    <h2>Liste des quittances</h2>
    <?php echo $message; ?>

    <form class="row mb-5 mt-5 bg-dark-subtle shadow gap-3 p-3" id="filterForm" method="GET" action="">
        <div class="col-md-5 d-flex flex-column  gap-2">
            <label><input type="checkbox" name="sortBy" value="montant"> Trier par Montant</label>
            <label><input type="checkbox" name="sortBy" value="date_paiement"> Trier par Date de Paiement</label>
            <label><input type="checkbox" name="sortBy" value="date_emission"> Trier par Date d'Émission</label>
        </div>
        <div class="col-md-5 ">
            <select name="type" class="form-select">
                <option value="">Tous les types</option>
                <option value="fournisseur"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?php echo($typeFilter === "fournisseur") ? 'selected' : ''; ?>>Fournisseur</option>
                <option value="client"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       <?php echo($typeFilter === "client") ? 'selected' : ''; ?>>Client</option>
            </select>
        </div>
        <div class="col-md-2 d-flex flex-column gap-3">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            <button type="button" class="btn btn-md btn-primary w-100" onclick="applySort()">Trier</button>
        </div>
    </form>

    <!-- Tableau des quittances -->
    <div class="table-container bg-dark-subtle shadow p-3">
        <table class="table table-striped table-hover" id="quittanceTable">
            <thead>
                <tr>
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
<?php foreach ($quittances as $row): ?>
                        <tr>
                            <td data-label="Numéro"><?php echo htmlspecialchars($row['numero_quittance']); ?></td>
                            <td data-label="Type"><?php echo htmlspecialchars($row['type']); ?></td>
                            <td data-label="Montant"><?php echo htmlspecialchars($row['montant']); ?></td>
                            <td data-label="Période"><?php echo htmlspecialchars($row['periode_service']); ?></td>
                            <td data-label="Date de Paiement"><?php echo htmlspecialchars($row['date_paiement']); ?></td>
                            <td data-label="Date d'Émission"><?php echo htmlspecialchars($row['date_emission']); ?></td>
                            <td data-label="Traité par :"><?php echo htmlspecialchars($row['employee_id']); ?></td>
                        </tr>
                    <?php endforeach; ?>
<?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        function applySort() {
            const checkboxes = document.querySelectorAll('input[name="sortBy"]:checked');
            const sortCriteria = Array.from(checkboxes).map(cb => cb.value);

            const table = document.getElementById('quittanceTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.rows);

            // Tri des lignes en fonction des critères sélectionnés
            rows.sort((a, b) => {
                let comparison = 0;

                // On effectue le tri selon le premier critère sélectionné
                if (sortCriteria.length > 0) {
                    const criteria = sortCriteria[0]; // On prend le premier critère pour le tri

                    let aValue, bValue, cValue;

                    // Récupération des valeurs en fonction du critère
                    if (criteria === 'montant') {
                        aValue = parseFloat(a.cells[2].innerText);
                        bValue = parseFloat(b.cells[2].innerText);
                    } else if (criteria === 'date_paiement') {
                        aValue = new Date(a.cells[4].innerText);
                        bValue = new Date(b.cells[4].innerText);
                    } else if (criteria === 'date_emission') {
                        aValue = new Date(a.cells[5].innerText);
                        bValue = new Date(b.cells[5].innerText);
                    }

                    // Comparaison des valeurs principales
                    if (aValue < bValue) {
                        comparison = -1;
                    } else if (aValue > bValue) {
                        comparison = 1;
                    } else {
                        // Si les valeurs sont égales, on peut utiliser cValue pour un tri secondaire
                        cValue = parseInt(a.cells[0].innerText); // Numéro de quittance
                        const dValue = parseInt(b.cells[0].innerText); // Numéro de quittance

                        if (cValue < dValue) {
                            comparison = -1;
                        } else if (cValue > dValue) {
                            comparison = 1;
                        }
                    }
                }

                return comparison;
            });

            // Réinsertion des lignes triées dans le tbody
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once 'partials/_footer.php'; ?>
