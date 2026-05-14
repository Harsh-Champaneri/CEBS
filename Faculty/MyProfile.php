<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
  $_SESSION["active_page"] = "MyProfile"; // Current Page Name

  if (isset($_POST["updateProfile"])) {
    $stmt = $connection->prepare("UPDATE users SET firstname = ?, lastname = ?,contact_number = ? WHERE user_id = ?");
    $stmt->bind_param("ssss", $_POST["firstname"], $_POST["lastname"], $_POST["contactNumber"], $_SESSION["user_id"]);
    if ($stmt->execute()) {
      $successMessage = "Profile Updated Successfully!";
    }
  }

  if (isset($_POST["saveChanges"])) {
    $password = password_hash($_POST["newPassword"], PASSWORD_DEFAULT);

    $stmt = $connection->prepare("UPDATE users SET firstname = ?, lastname = ?, password = ?,contact_number = ? WHERE user_id = ?");
    $stmt->bind_param("sssss", $_POST["firstname"], $_POST["lastname"], $password, $_POST["contactNumber"], $_SESSION["user_id"]);
    if ($stmt->execute()) {
      $successMessage = "Profile Updated Successfully!";
    }
  }

  $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
  $stmt->bind_param("s", $_SESSION["user_id"]);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  $email = $data["email"];
  $role = $data["role"];
  $firstname = $data["firstname"];
  $lastname = $data["lastname"];
  $department = $data["department"];
  $contact_number = $data["contact_number"];

  $name = $firstname . " " . $lastname;
  $name_initials = $firstname[0] . $lastname[0];
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>My Profile – CEBS</title>

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
  <link rel="stylesheet" href="../style.css" />
  <link rel="stylesheet" href="../auth.css" />
</head>

<body>
  <!-- 🔹 Top Bar -->
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
        <i class="bi bi-patch-check me-1"></i> Approved by AICTE &nbsp;|&nbsp;
        NAAC Accredited
      </span>
    </div>
  </div>

  <!-- 🔹 Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="Dashboard.php">
        <div class="web-logo">
          <i class="fa-solid fa-building-columns fa-xl" id="logo-icon"></i>
        </div>
        R.N.G Patel Institute of Technology
      </a>

      <button
        class="navbar-toggler border-0"
        data-bs-toggle="collapse"
        data-bs-target="#navMenu"
        style="color: #fff">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
          <li class="nav-item">
            <a class="nav-link" href="Dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item dropdown-center dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#events">Events</a>
            <ul class="dropdown-menu" style="border-radius: 14px; padding: 8px; min-width: 180px">
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="AddEvent.php"> <i class="fa-solid fa-calendar-plus"></i>
                  Add Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="UpcomingEvents.php"><i class="fa-solid fa-clock"></i>
                  Upcoming Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="ManageEvents.php"><i class="fa-solid fa-sliders"></i>
                  Manage Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="MyEvents.php"><i class="fa-solid fa-address-card"></i>
                  My Events</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="ViewParticipants.php">View Participants</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="ScanQR.php">Scan QR</a>
          </li>
          <li class="nav-item ms-lg-2  dropdown-center d-flex justify-content-center">
            <a
              class="profile-btn dropdown-toggle"
              role="button"
              data-bs-toggle="dropdown"
              href="#profile">
              <div class="profile-avatar"><?php echo $name_initials; ?></div>
              <span style="font-size: 0.85rem; font-weight: 500"><?php echo $name; ?></span>
            </a>

            <ul
              class="dropdown-menu shadow border-0"
              id="profile-dropdown-menu"
              style="border-radius: 14px; padding: 8px; min-width: 200px">
              <li>
                <a
                  class="dropdown-item d-flex align-items-center gap-3 py-2"
                  href="MyProfile.php"
                  id="edit-profile-opt">
                  <i class="fa-solid fa-user-pen"></i>
                  Edit Profile
                </a>
              </li>

              <li>
                <a
                  class="dropdown-item d-flex align-items-center gap-3 py-2 "
                  href="#"
                  id="logout-opt">
                  <i class="fa-solid fa-right-from-bracket"></i>
                  Logout
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="reg-card">
    <form action="MyProfile.php" method="post" class="needs-validation">
      <div class="screen active">
        <h2>My Profile</h2>
        <p class="form-hint">
          Update your personal information and change your password.
        </p>
        <div class="form-divider"></div>

        <!-- Email (Disabled) -->
        <label class="field-label">Email Address</label>
        <div class="input-wrap">
          <i class="bi bi-envelope icon-left"></i>
          <input
            type="email"
            class="form-ctrl"
            name="email"
            value="<?php echo $email; ?>"
            disabled
            required
            style="cursor: not-allowed" />
        </div>

        <!-- Role (Disabled) -->
        <label class="field-label">Role</label>
        <div class="input-wrap">
          <i class="bi bi-person-badge icon-left"></i>
          <input
            type="text"
            class="form-ctrl"
            value="<?php echo $role; ?>"
            disabled
            required
            style="cursor: not-allowed" />
        </div>

        <!-- Department -->
        <label class="field-label">Department</label>
        <div class="select-wrap">
          <i class="fa-solid fa-user-graduate icon-left"></i>
          <i class="bi bi-chevron-down chevron"></i>
          <select class="form-ctrl" id="departmentSelect" name="department" required disabled style="cursor: not-allowed">
            <option value="" selected>— Choose a Department —</option>
            <option value="Computer Science and Engineering" <?php if ($department === "Computer Science and Engineering") {
                                                                echo "selected";
                                                              } ?>>Computer Science and Engineering</option>
            <option value="Mechanical Engineering" <?php if ($department === "Mechanical Engineering") {
                                                      echo "selected";
                                                    } ?>>Mechanical Engineering</option>
            <option value="Civil Engineering" <?php if ($department === "Civil Engineering") {
                                                echo "selected";
                                              } ?>>Civil Engineering</option>
            <option value="Chemical Engineering" <?php if ($department === "Chemical Engineering") {
                                                    echo "selected";
                                                  } ?>>Chemical Engineering</option>
            <option value="Information Technology" <?php if ($department === "Information Technology") {
                                                      echo "selected";
                                                    } ?>>Information Technology</option>
            <option value="Electrical Engineering" <?php if ($department === "Electrical Engineering") {
                                                      echo "selected";
                                                    } ?>>Electrical Engineering</option>
          </select>
        </div>

        <!-- First Name -->
        <label class="field-label form-label">Firstname</label>
        <div class="input-wrap">
          <i class="bi bi-person icon-left"></i>
          <input
            type="text"
            class="form-ctrl form-control"
            id="firstName"
            name="firstname"
            value="<?php echo $firstname; ?>"
            placeholder="Enter your first name"
            required />
        </div>

        <!-- Last Name -->
        <label class="field-label form-label">Lastname</label>
        <div class="input-wrap">
          <i class="bi bi-person icon-left"></i>
          <input
            type="text"
            class="form-ctrl form-control"
            id="lastName"
            name="lastname"
            value="<?php echo $lastname; ?>"
            placeholder="Enter your last name"
            required />
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
            value="<?php echo $contact_number; ?>"
            required />
          <small class="d-none" id="contactNumberError"></small>
        </div>

        <div id="passwordFields"></div>

        <!-- Submit Button -->
        <button class="btn-submit mb-3" type="button" id="changePassword">Change Password</button>
        <button class="btn-submit" type="submit" name="updateProfile" id="updateProfile">Update Profile</button>
        <button class="btn-submit" type="submit" name="saveChanges" id="saveChanges" style="display: none;">Save Changes</button>
      </div>
    </form>
  </div>
  <?php include "../footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    // Toggle password visibility
    function togglePassword(inputId, icon) {
      const input = document.getElementById(inputId);
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
      }
    }

    // Sussess Message
    <?php
    if (!empty($successMessage)) {
      echo "alert('$successMessage');";
      echo "window.history.replaceState(null, '', window.location.pathname);";
    }
    ?>

    let changePassword = document.getElementById("changePassword"); // Change Password Button

    changePassword.addEventListener("click", function() {
      let passwordFields = document.getElementById("passwordFields"); // Div for adding Password Fields
      let updateProfile = document.getElementById("updateProfile"); // Update Profile Button
      let saveChanges = document.getElementById("saveChanges"); // Save Changes Button

      // Hiding Existing Buttons
      updateProfile.style.display = "none";
      changePassword.style.display = "none";

      // Showing Save Changes Button
      saveChanges.style.display = "block";

      // Adding Password Fields on clicking the Change Password Button
      passwordFields.innerHTML = `<section class="pwd-section">
          <label class="field-label form-label">New Password</label>
          <div class="input-wrap">
            <i class="bi bi-lock icon-left"></i>
            <input
              type="password"
              class="form-ctrl form-control"
              name="newPassword"
              id="newPassword"
              placeholder="New Password"
              required
              minlength="8" />
            <small class="d-none" id="newPasswordError"></small>
            <i
              class="bi bi-eye icon-right"
              onclick="togglePassword('newPassword', this)"></i>
          </div>

          <label class="field-label form-label">Confirm Password</label>
          <div class="input-wrap">
            <i class="bi bi-lock-fill icon-left"></i>
            <input
              type="password"
              class="form-ctrl form-control"
              id="confirmPassword"
              name="confirmPassword"
              placeholder="Confirm Password"
              required
              minlength="8" />
            <small class="d-none" id="confirmPasswordError">Hello</small>
            <i
              class="bi bi-eye icon-right"
              onclick="togglePassword('confirmPassword', this)"></i>
          </div>

          <p
            style="
                font-size: 0.75rem;
                color: #64748b;
                margin-bottom: 18px;
                text-align: center;
              ">
            <i class="bi bi-info-circle me-1"></i>Leave password fields blank
            if you don't want to change it
          </p>
        </section>`;

      // New Password and Confirm password Validations
      let passwordValid = false;
      let confirmPasswordValid = false;

      $("#newPassword").on("input", function() { // New Password Validation
        const password = $(this).val();

        let regex = {
          upper: /[A-Z]/,
          lower: /[a-z]/,
          digit: /[0-9]/,
          special: /[!@#$%^&*(),.?":{}|<>]/,
          length: /^.{8,}$/,
        };

        if (password.length === 0) {
          $("#newPasswordError").text("Password is required.").css("color", "red").removeClass("d-none");
        } else if (!regex.length.test(password)) {
          $("#newPasswordError").text("Password must be at least 8 characters.").css("color", "red").removeClass("d-none");
        } else if (!regex.upper.test(password)) {
          $("#newPasswordError").text("Password must contain at least ONE uppercase letter.").css("color", "red").removeClass("d-none");
        } else if (!regex.digit.test(password)) {
          $("#newPasswordError").text("Password must contain at least ONE number.").css("color", "red").removeClass("d-none");
        } else if (!regex.special.test(password)) {
          $("#newPasswordError").text("Password must contain at least ONE special character.").css("color", "red").removeClass("d-none");
        } else if (!regex.lower.test(password)) {
          $("#newPasswordError").text("Password must contain at least ONE lowercase letter.").css("color", "red").removeClass("d-none");
        } else {
          $("#newPasswordError").addClass("d-none");
          passwordValid = true;
        }
        passwordValid = false;

        let confirmPassword = $("#confirmPassField").val();
        if (confirmPassword === "") {
          $("#confirmPasswordError").text("");
          confirmPasswordValid = false;
        } else if (confirmPassword !== password) {
          $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
          confirmPasswordValid = false;
        } else {
          $("#confirmPasswordError").addClass("d-none");
          confirmPasswordValid = true;
          return;
        }
      });

      $("#confirmPassword").on("input", function() { // Confirm Password Validation
        let confirmPassword = $(this).val();
        let password = $("#newPassword").val();

        if (confirmPassword === "") {
          $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
          confirmPasswordValid = false;
        } else if (confirmPassword !== password) {
          $("#confirmPasswordError").text("Password Doesn't match.").css("color", "red").removeClass("d-none");
          confirmPasswordValid = false;
        } else {
          $("#confirmPasswordError").addClass("d-none");
          confirmPasswordValid = true;
          return;
        }
        confirmPasswordValid = false;
      });

      $(".btn-submit").on("click", function(e) {
        if ($("#passwordError").is(":visible") || $("#confirmPasswordError").is(":visible")) {
          e.preventDefault();
          alert("Please fix the highlighted errors");
        }
      });
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

    // Logout Toggle
    let logoutText = document.getElementById("logout-opt");
    logoutText.addEventListener("click", function(event) {
      event.preventDefault();
      let x = confirm("Are you sure you want to Logout?");

      if (x) {
        window.location = "../logout.php";
      }
    });
  </script>
</body>

</html>