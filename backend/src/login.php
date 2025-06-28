<?php
    session_start();
    include 'db.php';

    $message = '';

    // Vérification et génération du token CSRF
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation de l'email
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (! isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
        } elseif (! $email) {
            $message = '<div class="alert alert-danger">Format d\'email invalide.</div>';
        } else {
            try {
                // Préparation de la requête avec PDO
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                // Récupération du résultat
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Comparaison avec le mot de passe haché en base
                    if (password_verify($password, $user['password']) &&
                        ($user['role'] === 'admin' || $user['role'] === 'employe')) {

                        // Enregistrement des données de session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['pseudo']  = $user['pseudo'];
                        $_SESSION['role']    = $user['role'];
                        $_SESSION['statut']  = $user['statut'];

                        // var_dump($_SESSION['role']);

                        // Régénération de l'ID de session pour sécuriser la connexion
                        session_regenerate_id(true);

                        if ($user['role'] === 'admin' && $user['statut'] === 'actif') {
                            header('Location: admin_dashboard.php'); // Redirection pour les administrateurs
                            exit;
                        }

                        if ($user['role'] === 'employe' && $user['statut'] === 'actif') {
                            header('Location: employe_dashboard.php'); // Redirection pour les employés
                            exit;
                        }

                    } else {
                        $message = '<div class="alert alert-danger">Identifiants incorrects.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Identifiants incorrects.</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Erreur de base de données.</div>';
                // Log de l'erreur pour le développement
                error_log($e->getMessage());
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4 bg-body-secondary">
   <main class="container mt-5">
      <h2 class="text-center mb-4">Connexion au centre d'Administration</h2>
      <?php if ($message) {
              echo $message;
          }
      ?>
      <form method="post" class="col d-flex justify-content-center gap-5 mb-4 mt-5">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
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
