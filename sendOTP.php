<?php

session_start();
include "connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Function for Generating OTP
function generateOTP()
{
    return random_int(100000, 999999);
}

// Function for Sending Mail
function sendOTP($email, $user_name, $body, $subject)
{
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        $mail->Username   = '';

        $mail->Password   = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom("cebs.tech.team@gmail.com", "CEBS Team");
        $mail->addAddress($email, $user_name);

        //Content
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Resend OTP Functionality
if (isset($_POST["resendOTP"])) {
    $user_name = $_SESSION["firstname"] . " " . $_SESSION["lastname"];

    $otp = generateOTP();

    $body = file_get_contents("OTPTemplate.html");
    $body = str_replace(['{{USER_NAME}}', '{{OTP_CODE}}'], [$user_name, $otp], $body);

    $subject = 'Your CEBS OTP for Secure Registration';

    $mail = new PHPMailer(true);

    if (sendOTP($_SESSION["email"], $user_name, $body, $subject)) {
        $otp_expire = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $stmt = $connection->prepare("UPDATE users SET otp = ?, otp_expire = ? WHERE otp = ?");
        $stmt->bind_param("isi", $otp, $otp_expire, $_SESSION["otp"]);
        $stmt->execute();

        $_SESSION["otp"] = $otp;
        $_SESSION["resend_otp_time"] = time() + 60; // 60 seconds expiry

        echo "success";
        exit();
    }
}

// Send OTP Normally while register 
if (isset($_POST["sendOtp"])) {

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST["email"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("location:register.php?errorMsg=Account already exists.");
        exit();
    } else {
        if ($_POST["createPassword"] != $_POST["confirmPassword"]) {
            header("location:register.php");
            exit();
        }

        $_SESSION["role"] = $_POST["role"];
        $_SESSION["department"] = $_POST["department"];
        $_SESSION["firstname"] = $_POST["firstname"];
        $_SESSION["lastname"] = $_POST["lastname"];
        $_SESSION["contact_number"] = $_POST["contactNumber"];
        $_SESSION["email"] = $_POST["email"];
        $_SESSION["registration_successful"] = false;

        $password = $_POST["createPassword"];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $user_name = $_SESSION["firstname"] . " " . $_SESSION["lastname"];

        $otp = generateOTP();
        $_SESSION["otp"] = $otp;
        $_SESSION["resend_otp_time"] = time() + 60; // 60 seconds expiry

        $body = file_get_contents("OTPTemplate.html");
        $body = str_replace(['{{USER_NAME}}', '{{OTP_CODE}}'], [$user_name, $otp], $body);

        $subject = 'Your CEBS OTP for Secure Registration';

        if (sendOTP($_SESSION["email"], $user_name, $body, $subject)) {
            $now = date("Y-m-d H:i:s");
            $otp_expire = date("Y-m-d H:i:s", strtotime("+10 minutes")); // OTP expires in 10 Minutes
            $stmt = $connection->prepare("INSERT INTO users(password,otp,otp_expire,created_time) VALUES(?,?,?,?)");
            $stmt->bind_param("siss", $hashed_password, $otp, $otp_expire, $now);
            $stmt->execute();

            header("location:enterOTP.php");
            exit();
        }
    }
} else {
    header("location:register.php");
    exit();
}

?>