<?php

session_start();
include "../connection.php";

// View Detail Page POST to SESSION
if (isset($_SESSION["user_id"])) {
    if (isset($_POST["event_id"])) {
        $event_id = $_POST["event_id"];
        $_SESSION["event_id"] = $event_id;
        header("location:ViewDetails.php");
        exit();
    }
}

// Checking if the entered user email is already registered in the event or not
if (isset($_POST["email"]) && isset($_POST["eventId"])) {
    $email = $_POST["email"];
    $event_id = $_POST["eventId"];

    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND role = 'Student'");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "not_found";
        exit();
    }

    $already_participated_stmt = $connection->prepare("SELECT * FROM participants WHERE email = ? AND event_id = ?");
    $already_participated_stmt->bind_param("ss", $email, $event_id);
    $already_participated_stmt->execute();
    $already_participated = $already_participated_stmt->get_result();

    if ($already_participated->num_rows > 0) {
        echo "already_registered";
        exit();
    }

    $data = $result->fetch_assoc();
    echo "✔ User found: {$data['firstname']} {$data['lastname']}";
}

?>