<?php

session_start();

include "connection.php";
 
if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($result->num_rows === 1) {
        // Remember Me
        if (isset($_POST["rememberMe"])) {
            $token = bin2hex(random_bytes(32)); // Generating remember_me token

            setcookie("token", $token, time() + 60 * 60);   // Creating token cookie with value

            // Updating remember_me in Database
            $insert_token = $connection->prepare("UPDATE users SET remember_me = ? WHERE email = ?");
            $insert_token->bind_param("ss", $token, $email);
            $insert_token->execute();
        } else {
            setcookie("token", "", time() - (60 * 60)); // Creating token cookie without value

            // Updating remember_me in Database
            $insert_token = $connection->prepare("UPDATE users SET remember_me = NULL WHERE email = ?");
            $insert_token->bind_param("s", $email);
            $insert_token->execute();
        }

        // Verify Password
        if (password_verify($password, $data["password"])) {
            if ($data["role"] === "Student") {
                $_SESSION["user_id"] = $data["user_id"];    // Storing user_id into Session
                header("location:Student/Dashboard.php");
                exit();
            }
            if ($data["role"] === "Faculty") {
                $_SESSION["user_id"] = $data["user_id"];    // Storing user_id into Session
                header("location:Faculty/Dashboard.php");
                exit();
            }
        } else {
            header("location:login.php?errorMsg=Incorrect Password.");
            exit();
        }
    } else {
        header("location:login.php?errorMsg=Invalid Email or Role.");
        exit();
    }
} else {
    header("location:login.php");
    exit();
}
