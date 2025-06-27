<?php
    session_start();
    include 'db.php';

    $error = '';

    // Vérification si la session est déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (! isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Erreur de sécurité CSRF.";
        } else {
            $email    = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Préparation de la requête avec PDO
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Récupération du résultat
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $user['password']) && $user['role'] === 'admin') {
                    $_SESSION['admin'] = $user['id'];
                    header('Location: admin_dashboard.php');
                    exit;
                } else {
                    $error = "Identifiants incorrects ou accès refusé.";
                }
            } else {
                $error = "Utilisateur non trouvé.";
            }
        }
    }
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="container py-4 bg-body-secondary">
   <main class="container mt-5">
      <h2 class="text-center mb-4">Connexion au centre d'Administration</h2>
      <?php if ($error): ?><div style="color:red"><?php echo htmlspecialchars($error)?></div><?php endif; ?>
      <form method="post" class="col d-flex justify-content-center gap-5 mb-4 mt-5">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
            <div class="col-md-3 ">
                  <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-3">
                  <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <div class="col-md-3">
                  <button type="submit" class="btn btn-success w-100">Connexion</button>
            </div>
      </form>
   </main>
</body>
</html>