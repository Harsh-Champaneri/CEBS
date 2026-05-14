<?php

session_start();

if (isset($_SESSION["user_id"])) {
    if (isset($_POST["event_id"])) {
        $event_id = $_POST["event_id"];
        $_SESSION["event_id"] = $event_id;
        header("location:ViewDetails.php");
        exit();
    }
}

?>
