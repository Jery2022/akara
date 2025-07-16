  <div class="container my-4">
    <?php
        $role = $_SESSION['role'] ?? '';

        // Définir le chemin en fonction du rôle
        if ($role === 'admin') {
            $dashboardLink = '../admin_dashboard.php';
        } elseif ($role === 'employe') {
            $dashboardLink = '../employe_dashboard.php';
        } else {
            $dashboardLink = '../login.php'; // Chemin par défaut si le rôle n'est pas reconnu
        }
    ?>
    <a href="<?php echo htmlspecialchars($dashboardLink); ?>"
     class="btn btn-secondary mt-2">Retour au dashboard</a>
  </div>
</div>
<footer class="bg-primary text-white text-center py-3">
   <p>&copy;
    <?php echo date("Y"); ?>Akara 2025. Tous droits réservés.</p>
   <p>Version 1.0.0</p>
   <p>Développé par <a href="#"  class="text-white">NovaTechnologies Sarl.</a></p>
</footer>
</body>
</html>
