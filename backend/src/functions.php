<?php
include 'db.php';

// vérification de la connexion PDO
if (! $pdo) {
    die("Échec de la connexion à la base de données.");
}

function getAll($table)
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getById($table, $id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getByField($table, $field, $value)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $field = ?");
    $stmt->execute([$value]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotal($table)
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
    return $stmt->fetchColumn();
}

function getRecettesTotal()
{
    global $pdo;
    $stmt  = $pdo->query("SELECT SUM(total) FROM recettes");
    $total = $stmt->fetchColumn();

    // Vérification si le total est null ou non numérique
    $total = is_null($total) ? 0 : $total;

    return number_format($total, 1);
}

function getDepensesTotal()
{
    global $pdo;
    $stmt  = $pdo->query("SELECT SUM(total) FROM depenses");
    $total = $stmt->fetchColumn();

    // Vérification si le total est null ou non numérique
    $total = is_null($total) ? 0 : $total;

    return number_format($total, 1);
}

function getPaymentsTotal()
{
    global $pdo;
    $stmt  = $pdo->query("SELECT SUM(amount) FROM payments");
    $total = $stmt->fetchColumn();

    // Vérification si le total est null ou non numérique
    $total = is_null($total) ? 0 : $total;

    return number_format($total, 1);
}

function getAchatsTotal()
{
    global $pdo;
    $stmt  = $pdo->query("SELECT SUM(amount) FROM achats");
    $total = $stmt->fetchColumn();

    // Vérification si le total est null ou non numérique
    $total = is_null($total) ? 0 : $total;

    return number_format($total, 1);
}

function getMonthlyRecettes()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(date_recette, '%Y-%m') AS month, SUM(total) AS total FROM recettes GROUP BY month ORDER BY month");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlyDepenses()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(date_depense, '%Y-%m') AS month, SUM(total) AS total FROM depenses GROUP BY month ORDER BY month");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlyAchats()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(date_achat, '%Y-%m') AS month, SUM(amount) AS total FROM achats GROUP BY month ORDER BY month");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlyPayments()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(date_payment, '%Y-%m') AS month, SUM(amount) AS total FROM payments GROUP BY month ORDER BY month");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDetailsFactures($id)
{
    global $pdo;

    if (! intval($id) || $id === '') {
        return [];
    } else {
        $sql = "SELECT * FROM details_facture WHERE facture_id = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

}

function getDatasTableByID($table, $id)
{
    global $pdo;

                                                                                                                                                                       // Validation du nom de la table contre une liste blanche
    $validTables = ['details_facture', 'achats', 'contrats', 'customers', 'suppliers', 'entrepots', 'factures', 'payments', 'produits', 'recettes', 'stock', 'users']; // Ajoutez d'autres noms de tables valides si nécessaire
    if (! in_array($table, $validTables) || ! is_numeric($id) || empty($id)) {
        return ['error' => 'Nom de table ou ID invalide.'];
    }

    $sql = "SELECT * FROM {$table} WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => 'Erreur de base de données : ' . $e->getMessage()];
    }
}

/**
 * Convertit une chaîne de caractères au format '10,000.00' en un nombre au format local 'fr-FR'.
 *
 * @param string $stringNumber La chaîne à convertir.
 * @return float|int Le nombre converti ou un message d'erreur.
 */
function convertToLocalNumber($stringNumber)
{
                                                            // Remplacer les virgules par des points pour le séparateur décimal
    $stringNumber1 = str_replace(',', '', $stringNumber);   // Supprime les virgules
    $stringNumber  = str_replace('.', ',', $stringNumber1); // Remplace le point par une virgule

    // Vérifier si la chaîne est un nombre valide
    if (! is_numeric($stringNumber)) {
        $floatNumber = floatval($stringNumber);
    }

    // Convertir la chaîne en float
    return floatval($floatNumber);
}

/**
 * Formate un nombre selon la locale spécifiée.
 *
 * @param float|int $number Le nombre à formater.
 * @param string $locale La locale à utiliser pour le formatage (ex: 'fr_FR').
 * @return string Le nombre formaté.
 */
function formatNumber($number, $locale = 'fr_FR')
{

    $num = convertToLocalNumber($number);

    $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
    return $formatter->format($num);
}

/**
 * Formate une date selon la locale spécifiée.
 *
 * @param DateTime $date La date à formater.
 * @param string $locale La locale à utiliser pour le formatage (ex: 'fr_FR').
 * @return string La date formatée.
 */
function formatDate($date, $locale = 'fr_FR')
{
    $dateFormatter = new IntlDateFormatter($locale, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    return $dateFormatter->format($date);
}
