<?php
include "connection.php";

if (isset($_COOKIE["token"])) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE remember_me = ?");
    $stmt->bind_param("s", $_COOKIE["token"]);
    $stmt->execute(); 
    $result = $stmt->get_result();

    $data = $result->fetch_assoc();
    $email = $data["email"];
    $role = $data["role"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login – CEBS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    $showNavbar = true;
    $activePage = "login";
    include "header.php";
    ?>
    <div class="reg-card">
        <!-- Form Start -->
        <form action="loginVerify.php" method="POST" autocomplete="off">
            <div class="screen active">

                <h2>Welcome Back</h2>
                <p class="form-hint">Log in to your CEBS account to browse and book upcoming college events.</p>
                <div class="form-divider"></div>

                <!-- Role -->
                <label class="field-label">Select Your Role</label>
                <div class="select-wrap">
                    <i class="bi bi-person-badge icon-left"></i>
                    <i class="bi bi-chevron-down chevron"></i>
                    <select class="form-ctrl" id="roleSelect" name="role" required>
                        <option value="" selected>— Choose a role —</option>
                        <option value="student" <?php if (!empty($role)) {
                                                    if ($role === "Student") {
                                                        echo "selected";
                                                    }
                                                } else {
                                                    echo "";
                                                } ?>>🎓 Student</option>
                        <option value="faculty" <?php if (!empty($role)) {
                                                    if ($role === "Faculty") {
                                                        echo "selected";
                                                    }
                                                } else {
                                                    echo "";
                                                } ?>>👨‍🏫 Faculty</option>
                        <option value="admin" <?php if (!empty($role)) {
                                                    if ($role === "admin") {
                                                        echo "selected";
                                                    }
                                                } else {
                                                    echo "";
                                                } ?>>⚙️ Admin</option>
                    </select>
                </div>

                <!-- Email -->
                <label class="field-label">Email Address</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope icon-left"></i>
                    <input type="email" class="form-ctrl" id="loginEmail" name="email" value="<?php if (!empty($email)) {
                                                                                                    echo $email;
                                                                                                } else {
                                                                                                    echo "";
                                                                                                } ?>" placeholder="john.doe@abcit.edu" required>
                    <small class="d-none" id="emailError"></small>
                </div>

                <!-- Password -->
                <label class="field-label">Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" class="form-ctrl" name="password" id="loginPassword" placeholder="Enter your password" required>
                    <small class="d-none" id="passwordError"></small>
                    <i class="bi bi-eye icon-right" onclick="togglePass('loginPassword', this)"></i>
                </div>

                <!-- Remember me & Forgot password -->
                <div class="remember-row">
                    <div class="checkbox-wrap">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <label for="rememberMe">Remember me</label>
                    </div>
                    <a class="forgot-link" href="forgotPassword.php">Forgot Password?</a>
                </div>

                <!-- Error Message -->
                <?php
                if (isset($_GET["errorMsg"])) {
                    echo "<span class='invalid-otp' style='margin-bottom: 10px;'>{$_GET['errorMsg']}</span>";
                }
                ?>

                <!-- Submit -->
                <button type="submit" name="login" class="btn-submit">Log In</button>

                <!-- Footer -->
                <p class="form-footer">
                    Don't have an account? <a href="register.php">Register Now</a>
                </p>
            </div>
    </div>
    </form>
    <!-- Form End -->
    </div>
    <?php include "footer.php"; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
        let passwordValid = false;
        let emailValid = false;
        let roleValid = false;

        // Password Validation
        $("#loginPassword").on("input", function() {
            const password = $(this).val();

            let regex = {
                upper: /[A-Z]/,
                lower: /[a-z]/,
                digit: /[0-9]/,
                special: /[!@#$%^&*(),.?":{}|<>]/,
                length: /^.{8,}$/,
            };

            if (password.length === 0) {
                $("#passwordError").text("Password is required.").css("color", "red").removeClass("d-none");
            } else if (!regex.length.test(password)) {
                $("#passwordError").text("Password must be at least 8 characters.").css("color", "red").removeClass("d-none");
            } else if (!regex.upper.test(password)) {
                $("#passwordError").text("Password must contain at least ONE uppercase letter.").css("color", "red").removeClass("d-none");
            } else if (!regex.digit.test(password)) {
                $("#passwordError").text("Password must contain at least ONE number.").css("color", "red").removeClass("d-none");
            } else if (!regex.special.test(password)) {
                $("#passwordError").text("Password must contain at least ONE special character.").css("color", "red").removeClass("d-none");
            } else if (!regex.lower.test(password)) {
                $("#passwordError").text("Password must contain at least ONE lowercase letter.").css("color", "red").removeClass("d-none");
            } else {
                $("#passwordError").addClass("d-none");
                passwordValid = true;
                return;
            }
            passwordValid = false;
        });

        // Converting Uppercase of email to Lowercase
        $("#loginEmail").on("input", function() {
            let emailText = $(this).val().toLowerCase();
            $(this).val(emailText);

        });

        // Email Validation
        $("#loginEmail").on("input", function() {
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

        // Form Submission Handling
        $(".btn-submit").on("click", function(e) {
            if ($("#passwordError").is(":visible") || $("#emailError").is(":visible") || $("#roleError").is(":visible")) {
                e.preventDefault();
                alert("Please fix the highlighted errors.");
            }
        });
    </script>
</body>

</html>