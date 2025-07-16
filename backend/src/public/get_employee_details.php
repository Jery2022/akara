// get_employee_details.php (exemple simple)
<?php
    session_start();
    require_once __DIR__ . '/db.php'; // Assurez-vous que le chemin est correct
    $pdo = getPDO();

    header('Content-Type: application/json');

    $user_id = $_GET['user_id'] ?? null;

    if ($user_id) {
        try {
            $stmt = $pdo->prepare("SELECT e.fonction, e.salary FROM users u INNER JOIN employees e ON u.employee_id = e.id WHERE u.id = ?");
            $stmt->execute([$user_id]);
            $employee_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee_details) {
                echo json_encode($employee_details);
            } else {
                echo json_encode(['error' => 'Employee details not found for this user.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'User ID is missing.']);
}
?>