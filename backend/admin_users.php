<?php
    session_start();
    if (! isset($_SESSION['admin'])) {
        header('Location: admin_login.php');
        exit;
    }
    include 'db.php';

    // Ajout d'un utilisateur
    if (isset($_POST['add'])) {
        $email  = $_POST['email'];
        $pseudo = $_POST['pseudo'];
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("Email invalide");
        }
        if (empty($_POST['password']) || strlen($_POST['password']) < 8) {
            die("Mot de passe invalide. Il doit contenir au moins 8 caractères.");
        }
        // Vérification de l'unicité de l'email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);    // Passer $email dans un tableau
        $result = $stmt->fetchAll(); // Récupérer les résultats

        if (count($result) > 0) {
            die("Cet email est déjà utilisé.");
        }
        // Vérification de l'unicité du pseudo
        $stmt = $pdo->prepare("SELECT * FROM users WHERE pseudo = ?");
        $stmt->execute([$pseudo]);   // Passe $pseudo dans un tableau
        $result = $stmt->fetchAll(); // Récupérer les résultats

        if (count($result) > 0) {
            die("Cet pseudo est déjà utilisé.");
        }

        // Hachage du mot de passe
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pseudo   = $_POST['pseudo'];
        $role     = $_POST['role'];
        $stmt     = $pdo->prepare("INSERT INTO users (pseudo, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pseudo, $email, $password, $role]);
    }

    // Suppression d'un utilisateur
    if (isset($_GET['delete'])) {
        $id   = intval($_GET['delete']);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=$id");
        $stmt->execute([$id]);
    }

    // Liste des utilisateurs
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <header class="bg-primary text-white text-center py-3">
      <h1>Bienvenue dans le Centre d'Administration - Les Compagnons du BTP</h1>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
         <div class="container-fluid">
               <a class="navbar-brand" href="#">Compagnons du BTP</a>
               <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
               </button>
               <div class="collapse navbar-collapse" id="navbarNav">
                  <ul class="navbar-nav">
                     <li class="nav-item">
                        <a class="nav-link btn btn-success" href="#">Gestion des utilisateurs</a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link btn btn-success" href="#">Gestion des stocks</a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link btn btn-success" href="#">Paramètres</a>
                     </li>
                     <li class="nav-item">
                        <a class="nav-link btn btn-danger" href="admin_logout.php">Déconnexion</a>
                     </li>
                  </ul>
               </div>
         </div>
      </nav>
   </header>
   <main class="container mt-5">
      <h2 class="mb-4">Gestion des utilisateurs</h2>
      <form method="post" class="row gap-3 mb-4 bg-dark-subtle shadow border-dark-subtle  p-3">
         <div class="col-md-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
         </div>
         <div class="col-md-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
         </div>
         <div class="col-md-3">
            <label for="role" class="form-label">Rôle</label>
            <select name="role" class="form-select">
               <option value="admin">Admin</option>
               <option value="employe" selected>Employé</option>
            </select>
         </div>
         <div class="col-md-2 d-flex justify-content-center
               align-items-end">
            <button type="submit" name="add" class="
               btn btn-outline-success  w-100">Ajouter
            </button>
         </div>
      </form>
      <table class="table table-bordered table-striped">
         <thead>
            <tr><th>ID</th><th>Pseudo</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>
         </thead>
         <tbody>
         <?php if (empty($users)): ?>
              <tr>
                  <td colspan="10" class="text-center">Aucun utilisateur trouvé.</td>
              </tr>
          <?php else: ?>
<?php foreach ($users as $row): ?>
               <tr>
                  <td><?php echo $row['id'] ?></td>
                  <td><?php echo htmlspecialchars($row['pseudo']) ?></td>
                  <td><?php echo htmlspecialchars($row['email']) ?></td>
                  <td><?php echo htmlspecialchars($row['role']) ?></td>
                  <td><?php echo htmlspecialchars($row['statut']) ?></td>
                  <td>
                     <a href="?delete=<?php echo $row['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
                  </td>
               </tr>
        <?php endforeach; ?>
<?php endif; ?>
      </tbody>
      </table>
      <br>
   </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>