<?php

// For Back button clicked from enterOTP page
session_start();
include "connection.php";

if (isset($_SESSION["resend_otp_time"]) || isset($_SESSION["otp"])) {
  $stmt = $connection->prepare("DELETE FROM users WHERE otp = ?");
  $stmt->bind_param("i", $_SESSION["otp"]);
  $stmt->execute();

  session_unset();
  session_destroy(); 
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Register – CEBS</title>

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
  $showNavbar = true;
  include "header.php";
  ?>
  <div class="reg-card">
    <!-- Form -->
    <form action="sendOTP.php" method="POST" autocomplete="off">

      <div class="screen active">

        <h2>Create Account</h2>
        <p class="form-hint">
          Fill in the details below to register for the College Event Booking
          System.
        </p>
        <div class="form-divider"></div>

        <!-- Role -->
        <label class="field-label">Select Your Role</label>
        <div class="select-wrap">
          <i class="bi bi-person-badge icon-left"></i>
          <i class="bi bi-chevron-down chevron"></i>
          <select class="form-ctrl" id="roleSelect" name="role" required>
            <option value="" selected>— Choose a role —</option>
            <option value="Student">🎓 Student</option>
            <option value="Faculty">👨‍🏫 Faculty</option>
            <option value="admin">⚙️ Admin</option>
          </select>
        </div>

        <!-- Department -->
        <label class="field-label">Select Your Department</label>
        <div class="select-wrap">
          <i class="fa-solid fa-user-graduate icon-left"></i>
          <i class="bi bi-chevron-down chevron"></i>
          <select class="form-ctrl" id="departmentSelect" name="department" required>
            <option value="" selected>— Choose a Department —</option>
            <option value="Computer Science and Engineering">Computer Science and Engineering</option>
            <option value="Mechanical Engineering">Mechanical Engineering</option>
            <option value="Civil Engineering">Civil Engineering</option>
            <option value="Chemical Engineering">Chemical Engineering</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Electrical Engineering">Electrical Engineering</option>
          </select>
        </div>

        <!-- First Name -->
        <div class="row-2">
          <div>
            <label class="field-label">First Name</label>
            <div class="input-wrap">
              <i class="bi bi-person icon-left"></i>
              <input
                type="text"
                class="form-ctrl"
                placeholder="John"
                id="firstname"
                name="firstname" required />
              <small class="d-none" id="firstnameError"></small>
            </div>
          </div>

          <!-- Last Name -->
          <div>
            <label class="field-label">Last Name</label>
            <div class="input-wrap">
              <i class="bi bi-person icon-left"></i>
              <input
                type="text"
                class="form-ctrl"
                placeholder="Doe"
                id="lastname"
                name="lastname" required />
              <small class="d-none" id="lastnameError"></small>
            </div>
          </div>
        </div>

        <!-- Contact Number -->
        <label class="field-label">Contact Number</label>
        <div class="input-wrap">
          <i class="bi bi-telephone icon-left"></i>
          <input
            type="tel"
            class="form-ctrl"
            placeholder="9876543210"
            name="contactNumber"
            id="contactNumber"
            required />
          <small class="d-none" id="contactNumberError"></small>
        </div>

        <!-- Email -->
        <label class="field-label">Email Address</label>
        <div class="input-wrap">
          <i class="bi bi-envelope icon-left"></i>
          <input
            type="email"
            class="form-ctrl"
            placeholder="john.doe@abcit.edu"
            name="email"
            id="email"
            required />
          <small class="d-none" id="emailError"></small>
        </div>

        <label class="field-label">Create Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock icon-left"></i>
          <input type="password" class="form-ctrl" name="createPassword" id="createPassword" placeholder="Create a strong password" required>
          <small class="d-none" id="passwordError"></small>
          <i class="bi bi-eye icon-right" onclick="togglePass('createPassword', this)"></i>
        </div>

        <label class="field-label">Confirm Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock icon-left"></i>
          <input type="password" class="form-ctrl" name="confirmPassword" id="confirmPassword" placeholder="Re - enter your password" required>
          <small class="d-none" id="confirmPasswordError"></small>
          <i class="bi bi-eye icon-right" onclick="togglePass('confirmPassword', this)"></i>
        </div>

        <!-- Error Message -->
        <?php
        if (isset($_GET["errorMsg"])) {
          echo "<span class='invalid-otp' style='margin-bottom: 10px;'>$_GET[errorMsg]</span>";
        }
        ?>

        <!-- Button -->
        <button class="btn-submit" name="sendOtp" type="submit" id="sendOTP">
          Send OTP on Registered Email
        </button>

        <p class="form-footer">Already have an account? <a href="login.php">Log in</a></p>
      </div>
    </form>
    <!-- Form end -->
  </div>

  <?php include "footer.php"; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    window.history.replaceState(null, '', window.location.pathname);

    // Password show/hide Toggle
    function togglePass(inputId, iconEl) {
      const input = document.getElementById(inputId);
      if (input.type === 'password') {
        input.type = 'text';
        iconEl.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        input.type = 'password';
        iconEl.classList.replace('bi-eye-slash', 'bi-eye');
      }
    }
 
    // Flag variables
    let firstnameValid = false;
    let emailValid = false;
    let lastnameValid = false;
    let contactNumberValid = false;
    let passwordValid = false;
    let confirmPasswordValid = false;

    $("#createPassword").on("input", function() {

      const password = $(this).val();

      let regex = {
        upper: /[A-Z]/,
        lower: /[a-z]/,
        digit: /[0-9]/,
        special: /[!@#$%^&*(),.?":{}|<>]/,
        length: /^.{8,}$/
      };

      passwordValid = false;

      if (password.length === 0) {
        $("#passwordError").text("Password is required.").css("color", "red").removeClass("d-none");
      } else if (!regex.length.test(password)) {
        $("#passwordError").text("Password must be at least 8 characters.").css("color", "red").removeClass("d-none");
      } else if (!regex.upper.test(password)) {
        $("#passwordError").text("Password must contain at least ONE uppercase letter.").css("color", "red").removeClass("d-none");
      } else if (!regex.lower.test(password)) {
        $("#passwordError").text("Password must contain at least ONE lowercase letter.").css("color", "red").removeClass("d-none");
      } else if (!regex.digit.test(password)) {
        $("#passwordError").text("Password must contain at least ONE number.").css("color", "red").removeClass("d-none");
      } else if (!regex.special.test(password)) {
        $("#passwordError").text("Password must contain at least ONE special character.").css("color", "red").removeClass("d-none");
      } else {
        $("#passwordError").addClass("d-none");
        passwordValid = true;
      }

      // Confirm Password Validation
      let confirmPassword = $("#confirmPassword").val();

      if (confirmPassword === "") {
        $("#confirmPasswordError").text("");
        confirmPasswordValid = false;
      } else if (confirmPassword !== password) {
        $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
        confirmPasswordValid = false;
      } else {
        $("#confirmPasswordError").addClass("d-none");
        confirmPasswordValid = true;
      }
    });


    $("#confirmPassword").on("input", function() {

      let confirmPassword = $(this).val();
      let password = $("#createPassword").val();

      if (confirmPassword === "") {
        $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
        confirmPasswordValid = false;
      } else if (confirmPassword !== password) {
        $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
        confirmPasswordValid = false;
      } else {
        $("#confirmPasswordError").addClass("d-none");
        confirmPasswordValid = true;
      }
    });

    // Converting Uppercase of email to Lowercase
    $("#email").on("input", function() {
      let emailText = $(this).val().toLowerCase();
      $(this).val(emailText);

    });

    // Email Validation
    $("#email").on("input", function() {
      const email = $(this).val().trim();

      const basicPattern = /^[a-zA-Z0-9]+([._%+-][a-zA-Z0-9]+)*@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

      if (email.length === 0) {
        $("#emailError").text("Email address is required.").css("color", "red").removeClass("d-none");
      } else if (!email.includes("@")) {
        $("#emailError").text("Email must contain '@' symbol.").css("color", "red").removeClass("d-none");
      } else if (!email.includes(".")) {
        $("#emailError").text("Email must contain a domain extension (e.g. .com).").css("color", "red").removeClass("d-none");
      } else if (!basicPattern.test(email)) {
        $("#emailError").text("Please enter a valid email address.").css("color", "red").removeClass("d-none");
      } else {
        $("#emailError").addClass("d-none");
        emailValid = true;
        return;
      }
      emailValid = false;
    });

    // Contact Number Validation
    $("#contactNumber").on("input", function() {
      const contact = $(this).val();
      const onlyDigits = /^[0-9]{10}$/; // Exactly 10 digits

      if (contact.length === 0) {
        $("#contactNumberError").text("Contact Number is required.").css("color", "red").removeClass("d-none");
        contactNumberValid = false;
      } else if (!onlyDigits.test(contact)) {
        $("#contactNumberError").text("Contact number must be exactly 10 digits and contain only numbers.").css("color", "red").removeClass("d-none");
        contactNumberValid = false;
      } else {
        $("#contactNumberError").addClass("d-none");
        contactNumberValid = true;
      }
    });

    // Firstname Validation
    $("#firstname").on("input", function() {
      const firstname = $(this).val();
      const lettersOnly = /^[A-Za-z]+$/;

      if (firstname === "") {
        $("#firstnameError").text("Firstname is required.").css("color", "red").removeClass("d-none");
      } else if (!lettersOnly.test(firstname)) {
        $("#firstnameError").text("Only letters are allowed.").css("color", "red").removeClass("d-none");
      } else if (firstname.length < 2) {
        $("#firstnameError").text("Too short.").css("color", "red").removeClass("d-none");
      } else {
        $("#firstnameError").addClass("d-none");
        firstnameValid = true;
        return;
      }
      firstnameValid = false;
    });

    // Lastname Validation
    $("#lastname").on("input", function() {
      const lastname = $(this).val();
      const lettersOnly = /^[A-Za-z]+$/;

      if (lastname === "") {
        $("#lastnameError").text("Lastname is required.").css("color", "red").removeClass("d-none");
      } else if (!lettersOnly.test(lastname)) {
        $("#lastnameError").text("Only letters are allowed.").css("color", "red").removeClass("d-none");
      } else if (lastname.length < 2) {
        $("#lastnameError").text("Too short.").css("color", "red").removeClass("d-none");
      } else {
        $("#lastnameError").addClass("d-none");
        lastnameValid = true;
        return;
      }
      lastnameValid = false;
    });

    // Form Submission Handling
    $(".btn-submit").on("click", function(e) {
      if ($("#lastnameError").is(":visible") || $("#emailError").is(":visible") || $("#firstnameError").is(":visible") || $("#contactNumberError").is(":visible") || $("#confirmPasswordError").is(":visible") || $("#passwordError").is(":visible")) {
        e.preventDefault();
        alert("Please fix the highlighted errors.");
      }
    });
  </script>
</body>

</html>