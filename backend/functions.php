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
    $stmt = $pdo->query("SELECT SUM(total) FROM recettes");
    return number_format($stmt->fetchColumn(), 2);
}

function getPaymentsTotal()
{
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments");
    return number_format($stmt->fetchColumn(), 2);
}

function getMonthlyRecettes()
{
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(total) AS total FROM recettes GROUP BY month ORDER BY month");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
