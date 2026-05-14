<?php

include "connection.php";

if (isset($_GET["token"])) {
  $stmt = $connection->prepare("SELECT * FROM users WHERE reset_link_sent = ?");
  $stmt->bind_param("s", $_GET["token"]);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $update_reset_link_sent_token  = $connection->prepare("UPDATE users SET reset_link_sent = NULL WHERE reset_link_sent = ?");
    $update_reset_link_sent_token->bind_param("s", $_GET["token"]);
    $update_reset_link_sent_token->execute();
  } else {
    header("location:forgotPassword.php");
    exit();
  }
} else {
  header("location:forgotPassword.php");
  exit();
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Link Sent-CEBS</title>

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
        <h2 style="margin-bottom: 10px">Link Sent Successfully!</h2>
        <p class="form-hint" style="margin-bottom: 0">
          Password Reset Link has been sent to your email. Click the link to Reset the Password.
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