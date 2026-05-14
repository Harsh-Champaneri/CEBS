<?php

include "connection.php";

if (isset($_GET["token"])) {
  $stmt = $connection->prepare("SELECT * FROM users WHERE reset_success = ?");
  $stmt->bind_param("s", $_GET["token"]);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $update_reset_success = $connection->prepare("UPDATE users SET reset_success = NULL");
    $update_reset_success->execute(); 
  } else {
    header("location:login.php");
    exit();
  }
} else {
  header("location:login.php");
  exit();
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Password Change Success – CEBS</title>

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
    <form action="" method="POST">
      <div class="screen active">
        <div style="text-align: center; padding: 18px 0 10px">
          <div class="success-icon-wrap">✅</div>
          <h2 style="margin-bottom: 10px">Password Reset!</h2>
          <p class="form-hint" style="margin-bottom: 0">
            Your password has been successfully updated. You can now log in
            with your new credentials.
          </p>
        </div>

        <div class="form-divider" style="margin: 24px auto"></div>

        <button
          class="btn-submit btn-success-green"
          type="button" id="login">
          Back to Login
        </button>
      </div>
    </form>
  </div>
  <?php include "footer.php"; ?>

  <script>
    let login = document.getElementById("login");
    login.addEventListener("click", function() {
      window.location = "login.php";
    });
  </script>
</body>

</html>