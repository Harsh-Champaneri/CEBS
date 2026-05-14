<?php
session_start();

include "connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Generating Password
function generateTempPassword($length = 8)
{
    $uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $lowercase = "abcdefghijklmnopqrstuvwxyz";
    $digits    = "0123456789";
    $special   = "@#$%&*!?";

    $allChars = $uppercase . $lowercase . $digits . $special;

    $password = [];

    // Ensure at least one from each category
    $password[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password[] = $digits[random_int(0, strlen($digits) - 1)];
    $password[] = $special[random_int(0, strlen($special) - 1)];

    // Fill remaining characters
    for ($i = 4; $i < $length; $i++) {
        $password[] = $allChars[random_int(0, strlen($allChars) - 1)];
    }

    // Shuffle so order is unpredictable
    shuffle($password);

    return implode('', $password);
}

// Generating user id
function generateUserId()
{
    return 'CEBS' . date("His") . random_int(1000, 9999);   // CEBSHHMMSSXXXX
}

if (!isset($_SESSION['registration_successful'])) {
    $_SESSION['registration_successful'] = false;
}

if ($_SESSION['registration_successful'] === true) {
    header("location:registrationSuccessful.php");
    exit();
}

if (!isset($_SESSION["email"]) || !isset($_SESSION["lastname"]) || !isset($_SESSION["firstname"]) || !isset($_SESSION["role"])) {
    header("location:register.php");
    exit();
} else {
    if (isset($_POST["verifyOTP"]) && isset($_SESSION["registration_successful"]) && $_SESSION["registration_successful"] === false) {
        $entered_otp = implode('', $_POST["otp"]);

        $chech_otp_expire = $connection->prepare("SELECT otp_expire FROM users WHERE otp = ?");
        $chech_otp_expire->bind_param("i", $_SESSION["otp"]);
        $chech_otp_expire->execute();

        $otp_expire = $chech_otp_expire->get_result()->fetch_assoc()["otp_expire"];

        // Check for OTP expired or not
        if (strtotime($otp_expire) < time()) {
            header("location:enterOTP.php?errorMsg=OTP Expired.&expired=1");
            exit();
        }

        if (((string)$_SESSION["otp"] === (string)$entered_otp)) {
            $user_name = $_SESSION["firstname"] . " " . $_SESSION["lastname"];

            $user_id = generateUserId();

            // Inserting data into Database
            $insert_data = $connection->prepare("UPDATE users SET user_id = ?,role = ?,department = ?,firstname = ?,lastname = ?, contact_number = ?,email = ? WHERE otp = ?");
            $insert_data->bind_param("sssssssi", $user_id, $_SESSION["role"], $_SESSION["department"], $_SESSION["firstname"], $_SESSION["lastname"], $_SESSION["contact_number"], $_SESSION["email"], $_SESSION["otp"]);
            $insert_data->execute();

            // Making OTP and otp_expire null
            $delete_otp = $connection->prepare("UPDATE users SET otp = null, otp_expire = null WHERE otp = ?");
            $delete_otp->bind_param("i", $_SESSION["otp"]);
            $delete_otp->execute();

            $_SESSION['registration_successful'] = true;

            header("location:registrationSuccessful.php");
            exit();
        } else {
            header("location:enterOTP.php?errorMsg=Invalid OTP.");
            exit();
        }
    } else {
        header("location:register.php");
        exit();
    }
}
