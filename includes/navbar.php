<!-- responsive navbar  -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">        <li class="nav-item">
          <span class="nav-link user-name">
            <i class="fas fa-user me-2"></i><?php echo $_SESSION['user_name']; ?>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>