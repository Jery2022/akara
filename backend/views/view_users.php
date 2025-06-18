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

    // Filtrage et tri des utilisateurs
    $roleFilter   = $_GET['role'] ?? '';
    $statutFilter = $_GET['statut'] ?? '';
    $sortBy       = $_GET['sort_by'] ?? 'email';
    $order        = $_GET['order'] ?? 'ASC';

    // Construction de la requête SQL
    $query  = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if ($roleFilter) {
        $query .= " AND role = ?";
        $params[] = $roleFilter;
    }

    if ($statutFilter) {
        $query .= " AND statut = ?";
        $params[] = $statutFilter;
    }

    $query .= " ORDER BY $sortBy $order";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<title>Gestion Utilisateurs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-4">Gestion des utilisateurs</h2>
    <?php echo $message ?>

    <!-- Formulaire d'ajout d'un utilisateur
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
-->
    <div class="mb-3">
        <button
            class="btn btn-primary btn-md"
            data-bs-toggle="modal"
            data-bs-target="#addUserModal"
            data-id=""
            data-email=""
            data-role="employé"
            data-statut="actif"
          >Ajouter un utilisateur</button>
        </button>
    </div>
    <!-- Formulaire de filtre -->
    <form method="get" class="row g-5 mb-4">
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">Tous les rôles</option>
                <option value="admin"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <?php echo($roleFilter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="employe"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          <?php echo($roleFilter === 'employe') ? 'selected' : ''; ?>>Employé</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="statut" class="form-select">
                <option value="">Tous les statuts</option>
                <option value="actif"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <?php echo($statutFilter === 'actif') ? 'selected' : ''; ?>>Actif</option>
                <option value="désactivé"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      <?php echo($statutFilter === 'désactivé') ? 'selected' : ''; ?>>Désactivé</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="order" class="form-select">
                <option value="ASC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              <?php echo($order === 'ASC') ? 'selected' : ''; ?>>Ascendant</option>
                <option value="DESC"                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php echo($order === 'DESC') ? 'selected' : ''; ?>>Descendant</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Actions</th>
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
                      <a href="?delete=<?php echo $row['id'] ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</a>
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

<!-- Modal d'ajout d'un utilisateur -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id" id="add-id">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Modifier l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="add-email" class="form-label">Email</label>
                    <input type="email" name="email" id="add-email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="add-password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="add-password" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="add-role" class="form-label">Rôle</label>
                    <select name="role" id="add-role" class="form-select">
                        <option value="admin">Admin</option>
                        <option value="employé" selected>Employé</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="add-statut" class="form-label">Statut</label>
                    <select name="statut" id="add-statut" class="form-select">
                        <option value="actif" selected>Actif</option>
                        <option value="désactivé">Désactivé</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" name="add" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
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
  // Infos de l'utilisateur à ajouter
    var addUserModal = document.getElementById('addUserModal');
    addUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('add-id').value = button.getAttribute('data-id');
        document.getElementById('add-email').value = button.getAttribute('data-email');
        document.getElementById('add-role').value = button.getAttribute('data-role');
        document.getElementById('add-statut').value = button.getAttribute('data-statut');
        document.getElementById('add-password').value = button.getAttribute('data-password') || '';
    });

    // Infos de l'utilisateur à modifier
    var editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('edit-id').value = button.getAttribute('data-id');
        document.getElementById('edit-email').value = button.getAttribute('data-email');
        document.getElementById('edit-role').value = button.getAttribute('data-role');
        document.getElementById('edit-statut').value = button.getAttribute('data-statut');
        document.getElementById('edit-password').value = button.getAttribute('data-password') || '';
    });
</script>
<?php require_once 'partials/_footer.php'; ?>
