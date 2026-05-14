<?php

include "connection.php";

// Checking Reset Password Link
if (isset($_GET["token"])) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $_GET["token"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($result->num_rows === 1) {
        if (strtotime($data["reset_expire"]) < time()) {
            exit("Link has Expired.");
        }
    } else {
        exit("Invalid Token.");
    }
} else {
    exit("Invalid or Missing Token.");
}

// Updating Password to Database
if (isset($_POST["resetPassword"])) {
    if ($_POST["newPassword"] === $_POST["confirmPassword"]) {
        $success_token = bin2hex(random_bytes(32));

        $email = $data["email"];

        $password = password_hash($_POST["confirmPassword"], PASSWORD_DEFAULT);

        $updatePassword = $connection->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expire = NULL, reset_success = ? WHERE email = ?");
        $updatePassword->bind_param("sss", $password, $success_token, $email);
        $updatePassword->execute();

        header("location:changePasswordSuccess.php?token=$success_token");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Set New Password – CEBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="auth.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    $showNavbar = false;
    include "header.php";
    ?>
    <div class="reg-card">
        <form method="POST" autocomplete="off">
            <div class="screen active">

                <h2>Set New Password</h2>
                <p class="form-hint">Create a strong new password for your account. Minimum 8 characters recommended.</p>
                <div class="form-divider"></div>

                <label class="field-label">New Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" name="newPassword" class="form-ctrl" id="newPassField" placeholder="Min. 8 characters" required>
                    <i class="bi bi-eye icon-right" onclick="togglePass('newPassField', this)"></i>
                    <small class="d-none" id="passwordError"></small>
                </div>

                <label class="field-label">Confirm New Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock icon-left"></i>
                    <input type="password" name="confirmPassword" class="form-ctrl" id="confirmPassField" placeholder="Re-enter password" required>
                    <i class="bi bi-eye icon-right" onclick="togglePass('confirmPassField', this)"></i>
                    <small class="d-none" id="confirmPasswordError"></small>
                </div>

                <button class="btn-submit btn-success-green" name="resetPassword" type="submit" id="resetPassword">Reset Password</button>
            </div>
    </div>
    </form>
    </div>
    <?php include "footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let passwordValid = false;
        let confirmPasswordValid = false;

        $("#newPassField").on("input", function() {
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

        $("#confirmPassField").on("input", function() {
            let confirmPassword = $(this).val();
            let password = $("#newPassField").val();

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
    </script>

    <script>
        /* ── Password toggle ── */
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
    </script>
</body>

</html>