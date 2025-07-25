
<navbar class="navbar d-flex flex-row align-items-center justify-content-between bg-primary p-3">
   <div class="d-flex align-items-center">
      <img src="/images/logo-akara.jpeg" alt="Logo" class="me-2" style="width: 50px; height: 50px;">
      <span class="h5 mb-0">Akara</span>
      </div>
      <div class="d-flex align-items-center">
      <span class="me-3">Bienvenue,
         <?php echo htmlspecialchars($_SESSION['pseudo']); ?></span>
      <a href="/logout.php" class="btn btn-outline-danger text-black btn-sm">Déconnexion</a>
   </div>
</navbar>
