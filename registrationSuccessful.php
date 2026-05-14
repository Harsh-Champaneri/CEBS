<?php

session_start();

if (!isset($_SESSION["email"], $_SESSION["lastname"], $_SESSION["firstname"], $_SESSION["role"])) {
  header("location:register.php");
  exit();
} else {
  session_unset();
  session_destroy();
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Registration Successful-CEBS</title>

  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="auth.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <?php
  $showNavbar = false;
  include "header.php";
  ?>
  <div class="reg-card">
    <!-- <form action="" method="POST"> -->
    <div class="screen active">
      <div style="text-align: center; padding: 18px 0 10px">
        <div class="success-icon-wrap">✅</div>
        <h2 style="margin-bottom: 10px">Registration Successful!</h2>
        <p class="form-hint" style="margin-bottom: 0">
          Your account has been successfully created. Your login credentials
          has been sent to your email.
        </p>
      </div>

      <div class="form-divider" style="margin: 24px auto"></div>

      <button class="btn-submit btn-success-green" id="backToLogin">Back to Login</button>
    </div>
    <!-- </form> -->
  </div>
  <?php include "footer.php"; ?>

  <script>
    let backToLogin = document.getElementById("backToLogin");
    backToLogin.addEventListener("click", function() {
      window.location = "login.php";
    });
  </script>
</body>

</html>