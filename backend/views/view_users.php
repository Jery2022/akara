<?php
    session_start();
    if (! isset($_SESSION['admin'])) {
        header('Location: ../admin_login.php');
        exit;
    }

    require_once '../db.php';
    require_once 'partials/_header.php';

    // CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    // Ajout d'un utilisateur
    if (isset($_POST['add'])) {
        if (! isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
        } else {
            $email    = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role     = $_POST['role'] ?? 'employe';
            $statut   = $_POST['statut'] ?? 'actif';

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = '<div class="alert alert-danger">Email invalide.</div>';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt          = $pdo->prepare("INSERT INTO users (email, password, role, statut) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $password_hash, $role, $statut]);
                $message = '<div class="alert alert-success">Utilisateur ajouté avec succès.</div>';
            }
        }
    }

    // Suppression d'un utilisateur
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        if (! isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">Utilisateur supprimé avec succès.</div>';
        }
    }

    // Modification d'un utilisateur
    if (isset($_POST['edit'])) {
        if (! isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
        } else {
            $id     = intval($_POST['id']);
            $email  = $_POST['email'] ?? '';
            $role   = $_POST['role'] ?? 'employe';
            $statut = $_POST['statut'] ?? 'actif';

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = '<div class="alert alert-danger">Email invalide.</div>';
            } else {
                // Si le mot de passe est renseigné, on le met à jour
                if (! empty($_POST['password'])) {
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt          = $pdo->prepare("UPDATE users SET email=?, password=?, role=?, statut=? WHERE id=?");
                    $stmt->execute([$email, $password_hash, $role, $statut, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET email=?, role=?, statut=? WHERE id=?");
                    $stmt->execute([$email, $role, $statut, $id]);
                }
                $message = '<div class="alert alert-success">Utilisateur modifié avec succès.</div>';
            }
        }
    }

    // Liste des utilisateurs
    $stmt  = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<title>Gestion Utilisateurs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  rel="stylesheet">
</head>
<body>
  <div class="container my-4">
    <h2 class="mb-4">Gestion des utilisateurs</h2>
    <?php echo $message ?>

    <form method="post" class="row g-5 mb-4">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div class="col-md-3">
        <input type="email" name="email" placeholder="Email" required class="form-control">
      </div>
      <div class="col-md-3">
        <input type="password" name="password" placeholder="Mot de passe" required class="form-control">
      </div>
      <div class="col-md-2">
        <select name="role" class="form-select">
          <option value="admin">Admin</option>
          <option value="employe" selected>Employé</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="statut" class="form-select">
          <option value="actif" selected>Actif</option>
          <option value="désactivé">Désactivé</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" name="add" class="btn btn-success w-100">Ajouter</button>
      </div>
    </form>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Statut</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $row): ?>
          <tr>
            <td><?php echo $row['id'] ?></td>
            <td><?php echo htmlspecialchars($row['email']) ?></td>
            <td><?php echo htmlspecialchars($row['role']) ?></td>
            <td><?php echo htmlspecialchars($row['statut']) ?></td>
            <td>
              <a href="?delete=<?php echo $row['id'] ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
              <button
                class="btn btn-warning btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#editUserModal"
                data-id="<?php echo $row['id'] ?>"
                data-email="<?php echo htmlspecialchars($row['email']) ?>"
                data-role="<?php echo htmlspecialchars($row['role']) ?>"
                data-statut="<?php echo htmlspecialchars($row['statut']) ?>"
              >Modifier</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal de modification -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="id" id="edit-id">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Modifier l'utilisateur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-email" class="form-label">Email</label>
            <input type="email" name="email" id="edit-email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit-password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="password" id="edit-password" class="form-control">
          </div>
          <div class="mb-3">
            <label for="edit-role" class="form-label">Rôle</label>
            <select name="role" id="edit-role" class="form-select">
              <option value="admin">Admin</option>
              <option value="employe" selected>Employé</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-statut" class="form-label">Statut</label>
            <select name="statut" id="edit-statut" class="form-select">
              <option value="actif" selected>Actif</option>
              <option value="désactivé">Désactivé</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" name="edit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Infos de l'utilisateur à modifier
    var editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      document.getElementById('edit-id').value = button.getAttribute('data-id');
      document.getElementById('edit-email').value = button.getAttribute('data-email');
      document.getElementById('edit-role').value = button.getAttribute('data-role');
      document.getElementById('edit-statut').value = button.getAttribute('data-statut');
      document.getElementById('edit-password').value = '';
    });
  </script>
<?php require_once 'partials/_footer.php'; ?>