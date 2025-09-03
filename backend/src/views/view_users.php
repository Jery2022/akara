<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../config/db.php';

$pdo = getPDO();

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
        $pseudo   = trim($_POST['pseudo'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'employe';
        $statut   = $_POST['statut'] ?? 'actif';

        // Nouvelles variables pour l'employé
        $fonction = trim($_POST['fonction'] ?? '');
        $salary   = floatval($_POST['salary'] ?? 0.00); // Assurez-vous que c'est un float

        if (empty($pseudo) || strlen($pseudo) < 3) {
            $message = '<div class="alert alert-danger">Le pseudo doit contenir au moins 3 caractères.</div>';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = '<div class="alert alert-danger">E-mail invalide.</div>';
        } elseif (empty($password) || strlen($password) < 8) {
            $message = '<div class="alert alert-danger">Mot de passe invalide. Il doit contenir au moins 8 caractères.</div>';
        } elseif (! in_array($role, ['admin', 'employe'])) {
            $message = '<div class="alert alert-danger">Rôle invalide. Choisissez entre admin et employe.</div>';
        } elseif ($role === 'employe' && (empty($fonction) || $salary <= 0)) { // Validation pour les employés
            $message = '<div class="alert alert-danger">Pour un rôle d\'employé, la fonction et le salaire doivent être renseignés et le salaire doit être positif.</div>';
        } else {
            try {
                $pdo->beginTransaction(); // Démarre une transaction pour garantir l'atomicité

                // Vérification de l'unicité de l'email et du pseudo
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR pseudo = ?");
                $stmt->execute([$email, $pseudo]);
                if ($stmt->fetch()) {
                    $message = '<div class="alert alert-danger">Cet e-mail ou pseudo est déjà utilisé.</div>';
                    $pdo->rollBack(); // Annule la transaction si l'utilisateur existe déjà
                } else {
                    // Hachage du mot de passe
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $user_id       = null; // Initialisation de l'ID utilisateur
                    $employee_id   = null; // Initialisation de l'ID employé

                    // Si le rôle est 'employe', insérer d'abord dans la table employees
                    if ($role === 'employe') {
                        $stmt_employee = $pdo->prepare("INSERT INTO employees (name, fonction, salary, email) VALUES (?, ?, ?, ?)");
                        if (! $stmt_employee->execute([$pseudo, $fonction, $salary, $email])) {
                            $errorInfo = $stmt_employee->errorInfo();
                            error_log("Erreur lors de l'ajout de l'employé : " . $errorInfo[2]);
                            $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'employé.</div>';
                            $pdo->rollBack(); // Annule la transaction en cas d'échec
                        } else {
                            $employee_id = $pdo->lastInsertId(); // Récupère l'ID du nouvel employé
                        }
                    }

                    // Si aucune erreur liée à l'employé, procéder à l'insertion de l'utilisateur
                    if (empty($message)) { // Ne continuer que si aucun message d'erreur n'a été défini
                        $stmt_user = $pdo->prepare("INSERT INTO users (pseudo, email, password, role, statut, employee_id) VALUES (?, ?, ?, ?, ?, ?)");
                        if (! $stmt_user->execute([$pseudo, $email, $password_hash, $role, $statut, $employee_id])) {
                            $errorInfo = $stmt_user->errorInfo();
                            error_log("Erreur lors de l'ajout de l'utilisateur : " . $errorInfo[2]);
                            $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'utilisateur.</div>';
                            $pdo->rollBack(); // Annule la transaction en cas d'échec
                        } else {
                            $user_id = $pdo->lastInsertId(); // Récupère l'ID du nouvel utilisateur

                            // Si l'utilisateur est un employé, mettre à jour l'ID utilisateur dans la table employees
                            if ($role === 'employe' && $employee_id !== null) {
                                $stmt_employee_update = $pdo->prepare("UPDATE employees SET user_id = ? WHERE id = ?");
                                if (! $stmt_employee_update->execute([$user_id, $employee_id])) {
                                    $errorInfo = $stmt_employee_update->errorInfo();
                                    error_log("Erreur lors de la mise à jour de l'employé : " . $errorInfo[2]);
                                    $message = '<div class="alert alert-danger">Erreur lors de la mise à jour de l\'employé.</div>';
                                    $pdo->rollBack(); // Annule la transaction en cas d'échec
                                }
                            }
                        }
                    }

                    if (empty($message)) {
                        $pdo->commit();
                        // Stockez le message de succès dans une variable de session
                        $_SESSION['flash_message'] = '<div class="alert alert-success">Utilisateur et employé ajoutés/mis à jour avec succès.</div>';
                        header('Location: index.php?route=users'); // Exemple de redirection
                        exit;
                    }
                }
            } catch (PDOException $e) {
                error_log("Erreur de base de données : " . $e->getMessage());
                $message = '<div class="alert alert-danger">Erreur de base de données.</div>';
                // Toujours essayer de faire un rollback dans le bloc catch si une transaction a été démarrée
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            }
        }
    }
}

// Suppression d'un utilisateur

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if (! isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
    } else {
        try {
            $pdo->beginTransaction(); // Démarre une transaction pour la suppression

            // Récupérer le employee_id de l'utilisateur avant de le supprimer
            $stmt_get_employee_id = $pdo->prepare("SELECT employee_id, role FROM users WHERE id = ?");
            $stmt_get_employee_id->execute([$id]);
            $user_to_delete = $stmt_get_employee_id->fetch(PDO::FETCH_ASSOC);

            $employee_id_to_delete = null;
            if ($user_to_delete && $user_to_delete['role'] === 'employe' && ! empty($user_to_delete['employee_id'])) {
                $employee_id_to_delete = $user_to_delete['employee_id'];
            }

            // Supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                if ($stmt->rowCount() > 0) {
                    $message = '<div class="alert alert-success">Utilisateur supprimé avec succès.</div>';

                    // Si l'utilisateur était un employé, supprimez l'entrée correspondante dans la table 'employees'
                    if ($employee_id_to_delete) {
                        $stmt_delete_employee = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                        $stmt_delete_employee->execute([$employee_id_to_delete]);
                        // Vous pouvez ajouter une vérification ici si vous voulez être sûr que l'employé a été supprimé
                    }
                    $pdo->commit(); // Valide la transaction
                } else {
                    error_log("Aucun utilisateur trouvé avec l'ID : $id");
                    $message = '<div class="alert alert-warning">Utilisateur introuvable.</div>';
                    $pdo->rollBack(); // Annule la transaction
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Erreur lors de la suppression de l'utilisateur : " . $errorInfo[2]);
                $message = '<div class="alert alert-danger">Erreur de serveur.</div>';
                $pdo->rollBack(); // Annule la transaction
            }
        } catch (PDOException $e) {
            error_log("Erreur de base de données : " . $e->getMessage());
            $message = '<div class="alert alert-danger">Erreur de base de données.</div>';
            $pdo->rollBack(); // Annule la transaction
        }
    }
}

// Modification d'un utilisateur
if (isset($_POST['edit'])) {
    if (! isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = '<div class="alert alert-danger">Erreur de sécurité CSRF.</div>';
    } else {
        $id       = intval($_POST['id']);
        $pseudo   = trim($_POST['pseudo'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'employe';
        $statut   = $_POST['statut'] ?? 'actif';

        // Nouvelles variables pour l'employé lors de la modification
        $fonction = trim($_POST['fonction'] ?? '');
        $salary   = floatval($_POST['salary'] ?? 0.00);

        if (empty($pseudo) || strlen($pseudo) < 3) {
            $message = '<div class="alert alert-danger">Le pseudo doit contenir au moins 3 caractères.</div>';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = '<div class="alert alert-danger">E-mail invalide.</div>';
        } elseif (! empty($password) && strlen($password) < 8) {
            $message = '<div class="alert alert-danger">Mot de passe invalide. Il doit contenir au moins 8 caractères.</div>';
        } elseif (! in_array($role, ['admin', 'employe'])) {
            $message = '<div class="alert alert-danger">Rôle invalide. Choisissez entre admin et employe.</div>';
        } elseif ($role === 'employe' && (empty($fonction) || $salary <= 0)) { // Validation pour les employés
            $message = '<div class="alert alert-danger">Pour un rôle d\'employé, la fonction et le salaire doivent être renseignés et le salaire doit être positif.</div>';
        } else {
            try {
                $pdo->beginTransaction(); // Démarre une transaction

                // Vérification de l'unicité de l'email et du pseudo (hors utilisateur courant)
                $stmt = $pdo->prepare("SELECT id, employee_id, role FROM users WHERE (email = ? OR pseudo = ?) AND id != ?");
                $stmt->execute([$email, $pseudo, $id]);
                $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing_user) {
                    $message = '<div class="alert alert-danger">Cet e-mail ou pseudo est déjà utilisé par un autre utilisateur.</div>';
                    $pdo->rollBack(); // Annule la transaction
                } else {
                    // Récupérer l'employee_id actuel de l'utilisateur en cours de modification
                    $stmt_current_user = $pdo->prepare("SELECT employee_id, role FROM users WHERE id = ?");
                    $stmt_current_user->execute([$id]);
                    $current_user_data   = $stmt_current_user->fetch(PDO::FETCH_ASSOC);
                    $current_employee_id = $current_user_data['employee_id'] ?? null;
                    $current_user_role   = $current_user_data['role'] ?? null;

                    $new_employee_id = null;

                    if ($role === 'employe') {
                        // Si l'utilisateur devient ou reste un employé
                        if ($current_user_role === 'employe' && $current_employee_id) {
                            // L'utilisateur est déjà un employé, mettez à jour l'entrée existante
                            $stmt_employee = $pdo->prepare("UPDATE employees SET name = ?, fonction = ?, salary = ? WHERE id = ?");
                            $stmt_employee->execute([$pseudo, $fonction, $salary, $current_employee_id]);
                            $new_employee_id = $current_employee_id; // Conserver le même employee_id
                        } else {
                            // L'utilisateur n'était pas un employé ou n'avait pas de lien, insérez un nouvel employé
                            $stmt_employee = $pdo->prepare("INSERT INTO employees (name, fonction, salary) VALUES (?, ?, ?)");
                            $stmt_employee->execute([$pseudo, $fonction, $salary]);
                            $new_employee_id = $pdo->lastInsertId(); // Récupérer l'ID du nouvel employé
                        }
                    } else {
                        // Si l'utilisateur NE doit PAS être un employé (admin, par ex)
                        // Et s'il était précédemment un employé, supprimez l'entrée de l'employé
                        if ($current_user_role === 'employe' && $current_employee_id) {
                            $stmt_delete_employee = $pdo->prepare("DELETE FROM employees WHERE id = ?");
                            $stmt_delete_employee->execute([$current_employee_id]);
                        }
                        $new_employee_id = null; // S'assurer que employee_id est NULL pour les non-employés
                    }

                    // Mise à jour de l'utilisateur
                    $update_sql    = "UPDATE users SET pseudo=?, email=?, role=?, statut=?, employee_id=? ";
                    $update_params = [$pseudo, $email, $role, $statut, $new_employee_id];

                    if (! empty($password)) {
                        $update_params[] = $password_hash;
                    }
                    $update_sql .= "WHERE id=?";
                    $update_params[] = $id;

                    $stmt_user_update = $pdo->prepare($update_sql);

                    if ($stmt_user_update->execute($update_params)) {
                        $message = '<div class="alert alert-success">Utilisateur modifié avec succès.</div>';
                        $pdo->commit(); // Valide la transaction
                    } else {
                        $errorInfo = $stmt_user_update->errorInfo();
                        $message   = '<div class="alert alert-danger">Erreur lors de la modification de l\'utilisateur : ' . $errorInfo[2] . '</div>';
                        $pdo->rollBack(); // Annule la transaction
                    }
                }
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Erreur de base de données : ' . $e->getMessage() . '</div>';
                $pdo->rollBack(); // Annule la transaction
            }
        }
    }
}

// Filtrage et tri des utilisateurs
$roleFilter   = $_GET['role'] ?? '';
$statutFilter = $_GET['statut'] ?? '';
$sortBy       = $_GET['sort_by'] ?? 'email';
$order        = $_GET['order'] ?? 'ASC';

// Validation des paramètres de tri
$validSortColumns = ['id', 'email', 'created_at', 'pseudo', 'role', 'statut']; // Ajout de pseudo, role, statut
if (! in_array($sortBy, $validSortColumns)) {
    $sortBy = 'email'; // Valeur par défaut
}

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC'; // Assure que l'ordre est valide

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
<?php require_once 'partials/_header.php'; ?>
<title>Gestion Utilisateurs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php require_once 'partials/_navbar.php'; ?>
    <!-- Toast Bootstrap pour les messages -->
    <div class="position-fixed bottom-0   end-0  p-2" style="z-index: 1100">
        <div id="mainToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <?php
            // Afficher les messages flash stockés en session
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message']; // Affiche le message
                unset($_SESSION['flash_message']);     // Supprime le message de la session après l'avoir affiché
            }

            if (isset($message)): ?>
                <div class="d-flex">
                    <div class="toast-body" id="mainToastBody">
                        <!-- Le message sera injecté ici -->
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fermer"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <main class="container my-4">
        <h2 class="mb-4">Gestion des utilisateurs</h2>

        <!-- Bouton pour ajouter un utilisateur -->
        <div class="mb-3 mt-5">
            <button
                class="btn btn-primary btn-md"
                data-bs-toggle="modal"
                data-bs-target="#addUserModal"
                data-id=""
                data-email=""
                data-role="employé"
                data-statut="actif">Ajouter un utilisateur</button>
        </div>
        <!-- Formulaire de filtre -->
        <form method="get" class="row mb-5 mt-3 bg-dark-subtle shadow gap-3 p-3" action="/index.php">
            <!-- Champ caché pour le routeur -->
            <input type="hidden" name="route" value="users">

            <div class="col-md-2">
                <select name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <option value="admin" <?php echo ($roleFilter === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="employe" <?php echo ($roleFilter === 'employe') ? 'selected' : '' ?>>Employé</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="actif" <?php echo ($statutFilter === 'actif') ? 'selected' : '' ?>>Actif</option>
                    <option value="desactive" <?php echo ($statutFilter === 'desactive') ? 'selected' : '' ?>>Désactivé</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="sort_by" class="form-select">
                    <option value="email" <?php echo ($sortBy === 'email') ? 'selected' : '' ?>>Email</option>
                    <option value="id" <?php echo ($sortBy === 'id') ? 'selected' : '' ?>>ID</option>
                    <option value="statut" <?php echo ($sortBy === 'statut') ? 'selected' : '' ?>>Statut</option>
                    <option value="created_at" <?php echo ($sortBy === 'created_at') ? 'selected' : '' ?>>Date création</option>
                    <option value="role" <?php echo ($sortBy === 'role') ? 'selected' : '' ?>>Rôle</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="order" class="form-select">
                    <option value="ASC" <?php echo ($order === 'ASC') ? 'selected' : '' ?>>Ascendant</option>
                    <option value="DESC" <?php echo ($order === 'DESC') ? 'selected' : '' ?>>Descendant</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Trier et Filtrer</button>
            </div>
        </form>


        <!-- Tableau des utilisateurs -->
        <div class="table-container bg-dark-subtle shadow gap-3 p-3">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pseudo</th>
                        <th>E-mail</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="10" class="text-center">Aucun utilisateur trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $i = 1;
                        foreach ($users as $row): ?>

                            <tr>
                                <td data-label="#"><?php echo $i++ ?></td>
                                <td data-label="Pseudo"><?php echo htmlspecialchars($row['pseudo']) ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($row['email']) ?></td>
                                <td data-label="Rôle"><?php echo htmlspecialchars($row['role']) ?></td>
                                <td data-label="Statut"><?php echo htmlspecialchars($row['statut']) ?></td>
                                <td>
                                    <a href="../index.php?route=users&delete=<?php echo intval($row['id']) ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer
                                    </a>
                                    <button
                                        class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-id="<?php echo $row['id'] ?>"
                                        data-pseudo="<?php echo htmlspecialchars($row['pseudo']) ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']) ?>"
                                        data-role="<?php echo htmlspecialchars($row['role']) ?>"
                                        data-statut="<?php echo htmlspecialchars($row['statut']) ?>">Modifier</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal d'ajout d'un utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" class="modal-content" id="addUserForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" id="add-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="notification" style="display:none;"></div>
                    <div class="mb-3">
                        <label for="add-pseudo" class="form-label">Pseudo</label>
                        <input type="text" name="pseudo" id="add-pseudo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-email" class="form-label">Email</label>
                        <input type="email" name="email" id="add-email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" id="add-password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-role" class="form-label">Rôle</label>
                        <select name="role" id="add-role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="employe" selected>Employé</option>
                        </select>
                    </div>
                    <div id="employeeFieldsAdd" class="needs-validation">
                        <div class="mb-3">
                            <label for="add-fonction" class="form-label">Fonction (pour les employés)</label>
                            <input type="text" name="fonction" id="add-fonction" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="add-salary" class="form-label">Salaire (pour les employés)</label>
                            <input type="number" name="salary" id="add-salary" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-statut" class="form-label">Statut</label>
                        <select name="statut" id="add-statut" class="form-select" required>
                            <option value="actif" selected>Actif</option>
                            <option value="desactive">Désactivé</option>
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
                        <label for="edit-pseudo" class="form-label">Pseudo</label>
                        <input type="text" name="pseudo" id="edit-pseudo" class="form-control" required>
                    </div>
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
                        <select name="role" id="edit-role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="employe">Employé</option>
                        </select>
                    </div>
                    <div id="employeeFieldsEdit" class="needs-validation">
                        <div class="mb-3">
                            <label for="edit-fonction" class="form-label">Fonction (pour les employés)</label>
                            <input type="text" name="fonction" id="edit-fonction" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit-salary" class="form-label">Salaire (pour les employés)</label>
                            <input type="number" name="salary" id="edit-salary" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-statut" class="form-label">Statut</label>
                        <select name="statut" id="edit-statut" class="form-select" required>
                            <option value="actif">Actif</option>
                            <option value="desactive">Désactivé</option>
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
        addUserModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            document.getElementById('add-id').value = button.getAttribute('data-id');
            document.getElementById('add-pseudo').value = button.getAttribute('data-pseudo') || ''; // Initialiser à vide
            document.getElementById('add-email').value = button.getAttribute('data-email') || ''; // Initialiser à vide
            document.getElementById('add-role').value = button.getAttribute('data-role');
            document.getElementById('add-statut').value = button.getAttribute('data-statut');
            document.getElementById('add-password').value = button.getAttribute('data-password') || '';

            // Initialiser les champs fonction/salaire
            document.getElementById('add-fonction').value = '';
            document.getElementById('add-salary').value = '';

            // Appeler la fonction pour gérer l'affichage des champs d'employé
            toggleEmployeeFields('add');
        });

        // Écouter le changement de rôle dans le modal d'ajout
        document.getElementById('add-role').addEventListener('change', function() {
            toggleEmployeeFields('add');
        });


        // Infos de l'utilisateur à modifier
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', async function(event) { // Utilisation de async pour await la récupération des données
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');

            document.getElementById('edit-id').value = userId;
            document.getElementById('edit-pseudo').value = button.getAttribute('data-pseudo');
            document.getElementById('edit-email').value = button.getAttribute('data-email');
            document.getElementById('edit-role').value = button.getAttribute('data-role');
            document.getElementById('edit-statut').value = button.getAttribute('data-statut');
            document.getElementById('edit-password').value = ''; // Toujours vide pour la modification du mot de passe

            // Récupérer les données de l'employé si le rôle est "employe"
            if (document.getElementById('edit-role').value === 'employe') {
                try {
                    const response = await fetch(`/get_employee_details.php?user_id=${userId}`);
                    const employeeDetails = await response.json();

                    if (employeeDetails && employeeDetails.fonction && employeeDetails.salary) {
                        document.getElementById('edit-fonction').value = employeeDetails.fonction;
                        document.getElementById('edit-salary').value = employeeDetails.salary;
                    } else {
                        // Si l'utilisateur est un employé mais n'a pas de détails d'employé (nouvellement converti ou erreur)
                        document.getElementById('edit-fonction').value = '';
                        document.getElementById('edit-salary').value = '';
                    }
                } catch (error) {
                    console.error("Erreur lors de la récupération des détails de l'employé:", error);
                    document.getElementById('edit-fonction').value = '';
                    document.getElementById('edit-salary').value = '';
                }
            } else {
                document.getElementById('edit-fonction').value = '';
                document.getElementById('edit-salary').value = '';
            }

            // Appeler la fonction pour gérer l'affichage des champs d'employé
            toggleEmployeeFields('edit');
        });

        // Écouter le changement de rôle dans le modal de modification
        document.getElementById('edit-role').addEventListener('change', function() {
            toggleEmployeeFields('edit');
        });

        /**
         * Gère l'affichage des champs 'fonction' et 'salary' en fonction du rôle sélectionné.
         * @param {string} modalType 'add' ou 'edit'
         */
        function toggleEmployeeFields(modalType) {
            const roleSelect = document.getElementById(`${modalType}-role`);
            const employeeFieldsContainer = document.getElementById(`employeeFields${modalType === 'add' ? 'Add' : 'Edit'}`);
            const fonctionInput = document.getElementById(`${modalType}-fonction`);
            const salaryInput = document.getElementById(`${modalType}-salary`);

            if (roleSelect.value === 'employe') {
                employeeFieldsContainer.style.display = 'block';
                fonctionInput.setAttribute('required', 'required'); // Rendez ces champs requis
                salaryInput.setAttribute('required', 'required');
            } else {
                employeeFieldsContainer.style.display = 'none';
                fonctionInput.removeAttribute('required'); // Retirez la contrainte required
                salaryInput.removeAttribute('required');
                // Réinitialiser les valeurs si les champs sont cachés
                fonctionInput.value = '';
                salaryInput.value = '';
            }
        }


        // Validation du formulaire d'ajout (côté client)
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            let errors = [];
            let pseudo = document.getElementById('add-pseudo').value.trim();
            let email = document.getElementById('add-email').value.trim();
            let password = document.getElementById('add-password').value;
            let role = document.getElementById('add-role').value;
            let statut = document.getElementById('add-statut').value;
            let fonction = document.getElementById('add-fonction').value.trim();
            let salary = parseFloat(document.getElementById('add-salary').value);


            if (pseudo.length < 3) errors.push("Le pseudo doit contenir au moins 3 caractères.");
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push("L'e-mail n'est pas valide.");
            if (password.length < 8) errors.push("Le mot de passe doit contenir au moins 8 caractères.");
            if (role !== "admin" && role !== "employe") errors.push("Le rôle doit être admin ou employé.");
            if (statut !== "actif" && statut !== "desactive") errors.push("Le statut doit être actif ou désactivé.");

            if (role === 'employe') {
                if (fonction.length === 0) errors.push("La fonction est requise pour un rôle d'employé.");
                if (isNaN(salary) || salary <= 0) errors.push("Le salaire doit être un nombre positif pour un rôle d'employé.");
            }


            if (errors.length > 0) {
                e.preventDefault();
                let notif = document.getElementById('notification');
                notif.innerHTML = '<div class="alert alert-danger">' + errors.join('<br>') + '</div>';
                notif.style.display = 'block';
            }
        });

        document.querySelector('#editUserModal form').addEventListener('submit', function(e) { // Cible le formulaire dans le modal d'édition
            let errors = [];
            let pseudo = document.getElementById('edit-pseudo').value.trim();
            let email = document.getElementById('edit-email').value.trim();
            let password = document.getElementById('edit-password').value;
            let role = document.getElementById('edit-role').value;
            let statut = document.getElementById('edit-statut').value;
            let fonction = document.getElementById('edit-fonction').value.trim();
            let salary = parseFloat(document.getElementById('edit-salary').value);

            if (pseudo.length < 3) errors.push("Le pseudo doit contenir au moins 3 caractères.");
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push("L'e-mail n'est pas valide.");
            if (!empty(password) && password.length < 8) errors.push("Le mot de passe doit contenir au moins 8 caractères s'il est renseigné.");
            if (role !== "admin" && role !== "employe") errors.push("Le rôle doit être admin ou employé.");
            if (statut !== "actif" && statut !== "desactive") errors.push("Le statut doit être actif ou désactivé.");

            if (role === 'employe') {
                if (fonction.length === 0) errors.push("La fonction est requise pour un rôle d'employé.");
                if (isNaN(salary) || salary <= 0) errors.push("Le salaire doit être un nombre positif pour un rôle d'employé.");
            }

            if (errors.length > 0) {
                e.preventDefault();
                // Utilisez le même div de notification que pour l'ajout, ou créez-en un spécifique pour l'édition
                let notif = document.querySelector('#editUserModal #notification_edit') || document.getElementById('notification');
                notif.innerHTML = '<div class="alert alert-danger">' + errors.join('<br>') + '</div>';
                notif.style.display = 'block';
            }
        });
    </script>

    <?php if (isset($message) && ! empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var toastBody = document.getElementById('mainToastBody');
                toastBody.innerHTML =
                    <?php echo json_encode(strip_tags($message, '<div><br>')); ?>;

                var toast = document.getElementById('mainToast');
                // Change la couleur selon le type de message
                if (toastBody.innerHTML.includes('alert-success')) {
                    toast.classList.remove('text-bg-danger');
                    toast.classList.add('text-bg-success');
                } else {
                    toast.classList.remove('text-bg-success');
                    toast.classList.add('text-bg-danger');
                }
                var bsToast = new bootstrap.Toast(toast, {
                    delay: 4000
                });
                bsToast.show();
            });
        </script>
    <?php endif; ?>
    <?php require_once 'partials/_footer.php'; ?>