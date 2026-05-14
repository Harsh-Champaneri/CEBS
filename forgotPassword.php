<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

include "connection.php";

if (isset($_POST["submit"])) {
    $email = $_POST["email"];

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        $name = $data["firstname"] . " " . $data["lastname"];

        $token = bin2hex(random_bytes(32)); // Generating reset_token
        $expire_time = date("Y-m-d H:i:s", strtotime("+15 minutes"));   // Expire time of the token

        $hhmm = substr($expire_time, 11, 5);

        // Updating reset_token and reset_expire in Database
        $insert_token = $connection->prepare("UPDATE users SET reset_token = ?, reset_expire = ? WHERE email = ?");
        $insert_token->bind_param("sss", $token, $expire_time, $email);
        $insert_token->execute();

        $link = "http://localhost/SEM%206%20-%20WP/Project/setNewPassword.php?token={$token}";
        $success_token = bin2hex(random_bytes(32));

        $body = file_get_contents("ResetLinkTemplate.html");
        $body = str_replace(['{{USER_NAME}}', '{{RESET_URL}}', '{{EXPIRY_TIME}}', '{{RESET_URL}}'], [$name, $link, $hhmm, $link], $body);

        $subject = "Your Password Reset Link";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            $mail->Username   = '';

            $mail->Password   = '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            //Recipients
            $mail->setFrom("", "");
            $mail->addAddress($email, $name);

            //Content
            $mail->isHTML(true);

            $mail->Subject = $subject;
            $mail->Body    = $body;

            if ($mail->send()) {
                $update_reset_link_sent_token = $connection->prepare("UPDATE users SET reset_link_sent = ? WHERE email = ?");
                $update_reset_link_sent_token->bind_param("ss", $success_token, $email);
                $update_reset_link_sent_token->execute();

                header("location:linkSentSuccessful.php?token=$success_token");
                exit();
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        header("location:forgotPassword.php?errorMsg=Email does not exists.");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password – CEBS</title>

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
        <form action="forgotPassword.php" method="POST" autocomplete="off">

            <div class="screen active">
                <button type="button" class="back-btn" id="backToLogin" style="font-size: 0.78rem !important;">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </button>

                <h2>Forgot Password</h2>
                <p class="form-hint">Enter your registered email address. We'll send a link to reset your password.</p>
                <div class="form-divider"></div>

                <label class="field-label">Email Address</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope icon-left"></i>
                    <input type="email" name="email" class="form-ctrl" id="forgotEmail" placeholder="john.doe@abcit.edu" required>
                    <small class="d-none" id="emailError"></small>
                </div>

                <?php
                if (isset($_GET["errorMsg"])) {
                    echo "<span class='invalid-otp'>$_GET[errorMsg]</span>";
                }
                ?>

                <button class="btn-submit" name="submit" type="submit" id="sendOtp"> Submit</button>

                <p class="form-footer">Remember your password? <a href="login.php">Back to Login</a></p>
            </div>
    </div>
    </form>
    <?php include "footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        window.history.replaceState(null, '', window.location.pathname);

        let emailValid = false;

        $("#forgotEmail").on("input", function() {
            let emailText = $(this).val().toLowerCase();
            $(this).val(emailText);

        });

        $("#forgotEmail").on("input", function() {
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

        $(".btn-submit").on("click", function(e) {
            if ($("#emailError").is(":visible")) {
                e.preventDefault();
                alert("Please fix the highlighted errors.");
            }
        });
    </script>

    <script>
        let backToLogin = document.getElementById("backToLogin");
        backToLogin.addEventListener("click", function() {
            window.location = "login.php";
        });
    </script>
</body>

</html>