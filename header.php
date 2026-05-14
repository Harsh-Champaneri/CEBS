<!-- 📹 Top Bar -->
<div class="top-bar py-2">
  <div
    class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>
      <i class="bi bi-envelope me-1"></i>
      <a href="#">cebs.tech.team@gmail.com</a>
      <span class="mx-2 opacity-50">|</span>
      <i class="bi bi-telephone me-1"></i> +91 98765 43210
    </span>
    <span class="badge-accred">
      <i class="bi bi-patch-check me-1"></i>
      Approved by AICTE &nbsp;|&nbsp; NAAC Accredited
    </span>
  </div>
</div>

<!-- 📹 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php if ($showNavbar) {
                                                                    echo "index.php";
                                                                  } else {
                                                                    echo "";
                                                                  } ?>">
      <div class="web-logo"><i class="fa-solid fa-building-columns fa-xl" id="logo-icon"></i></div>
      R.N.G Patel Institute of Technology
    </a>

    <button
      class="navbar-toggler border-0"
      data-bs-toggle="collapse"
      data-bs-target="#navMenu"
      style="color: #fff">
      <span class="navbar-toggler-icon"></span>
    </button>

    <?php if ($showNavbar): ?>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item">
            <a class="nav-link <?php if ($activePage == "home") {
                                  echo "active";
                                } else {
                                  echo "";
                                } ?>" href="index.php">Home</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php if ($activePage == "AboutUs") {
                                  echo "active";
                                } else {
                                  echo "";
                                } ?>" href="AboutUs.php">About Us</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php if ($activePage == "contactUs") {
                                  echo "active";
                                } else {
                                  echo "";
                                } ?>" href="ContactUs.php">Contact</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php if ($activePage == "login") {
                                  echo "active";
                                } else {
                                  echo "";
                                } ?>" href="login.php">Login</a>
          </li>

          <li class="nav-item ms-lg-2">
            <a class="btn-register" href="register.php">Register</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>