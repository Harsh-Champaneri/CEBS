<?php

session_start();

if (!isset($_SESSION["resend_otp_time"], $_SESSION["otp"])) {
  header("location:register.php");
  exit();
} else {
  $remainingTime = 0;

  if (isset($_SESSION["resend_otp_time"])) {
    $remainingTime = $_SESSION["resend_otp_time"] - time();
    if ($remainingTime < 0) {
      $remainingTime = 0;
    }
  }
}
$otpExpired = isset($_GET["expired"]);

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Enter OTP – CEBS</title>

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
    <form action="verifyOTP.php" method="POST">
      <div class="screen active">

        <button class="back-btn" id="back" type="button">
          <i class="bi bi-arrow-left"></i> Back
        </button>

        <h2>Verify OTP</h2>
        <p class="form-hint">
          We've sent a 6-digit OTP to
          <strong style="color:#0ea5e9;" id="otpEmailDisplay"><?php echo $_SESSION["email"]; ?></strong>. Enter it below to continue.
        </p>
        <div class="form-divider"></div>

        <label class="field-label">Enter OTP</label>
        <div class="otp-row" id="otpRow">
          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 0)" />

          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 1)" />

          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 2)" />

          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 3)" />

          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 4)" />

          <input
            class="otp-box"
            type="number"
            maxlength="1"
            name="otp[]"
            required
            oninput="moveOtp(this, 5)" />
        </div>

        <!-- Error Message -->
        <?php
        if (isset($_GET["errorMsg"])) {
          echo "<span class='invalid-otp'>$_GET[errorMsg]</span>";
        }
        ?>

        <button
          class="btn-submit"
          id="verifyOtp"
          type="submit"
          name="verifyOTP"
          style="margin-top: 14px">
          Verify OTP
        </button>

        <button
          class="btn-submit"
          id="resendOTP"
          type="button"
          name="resendOTP"
          style="margin-top: 14px">
          <span id="resendText">Resend OTP in </span><span id="countdown"></span>
        </button>
      </div>
    </form>
  </div>
  <?php include "footer.php"; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    window.history.replaceState(null, '', window.location.pathname);

    function moveOtp(el, idx) {
      const boxes = document.querySelectorAll('.otp-box');
      // keep only single digit
      if (el.value.length > 1) el.value = el.value.slice(-1);

      if (el.value && idx < boxes.length - 1) {
        boxes[idx + 1].focus();
      }
      // backspace: go back
      if (!el.value && idx > 0) {
        boxes[idx - 1].focus();
      }
    }

    // Resend OTP Button Countdown
    let otpExpired = <?php echo $otpExpired ? 'true' : 'false'; ?>;
    let timeLeft = <?php echo $remainingTime; ?>;
    let timerInterval = null;

    const resendButton = $("#resendOTP");
    const resendText = $("#resendText");
    const countdownElement = $("#countdown");

    function startTimer() {
      if (timeLeft <= 0) {
        resendText.text("Resend OTP");
        resendButton.prop("disabled", false);
        resendButton.css("cursor", "pointer");
        countdownElement.hide();
        return;
      }

      resendButton.prop("disabled", true);
      resendButton.css("cursor", "not-allowed");

      resendText.text("Resend OTP in ");
      countdownElement.text(timeLeft).show();

      timerInterval = setInterval(function() {

        timeLeft--;
        countdownElement.text(timeLeft);

        if (timeLeft <= 0) {

          clearInterval(timerInterval);

          resendButton.prop("disabled", false);
          resendButton.css("cursor", "pointer");

          resendText.text("Resend OTP");
          countdownElement.hide();
        }

      }, 1000);
    }

    // Start timer initially
    if (!otpExpired) {
      startTimer();
    } else {
      resendText.text("Resend OTP");
      resendButton.prop("disabled", false);
      resendButton.css("cursor", "pointer");
    }

    // Resend Button Click
    resendButton.on("click", function() {
      resendButton.prop("disabled", true);
      resendButton.css("cursor", "not-allowed");

      // Show loader
      resendText.html(`<span class="spinner-border spinner-border-sm"></span> Sending...`);
      countdownElement.hide();

      $.ajax({
        url: "sendOTP.php",
        type: "POST",
        data: {
          resendOTP: true,
        },
        cache: false,

        success: function(res) {
          res = res.trim();
          if (res === "success") {
            alert("OTP Sent Successfully!");
            timeLeft = 60;
            startTimer(); // restart countdown
          } else {
            alert("Something went wrong!");
            resendText.text("Resend OTP");
            resendButton.prop("disabled", false);
            resendButton.css("cursor", "pointer");
          }
        },
      });
    });

    // Resend OTP success alert  
    <?php
    if (isset($_GET["successMsg"])) {
      echo "alert('$_GET[successMsg]');";
      echo "window.history.replaceState(null, '', window.location.pathname);";
    }
    ?>

    // Back Arrow Toggle
    let back = document.getElementById("back");
    back.addEventListener("click", function() {
      let x = confirm("Are you sure you want to go back?");
      if (x) {
        window.location = "register.php";
      }
    });
  </script>
</body>

</html>