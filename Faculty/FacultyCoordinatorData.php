<?php

session_start();

include "../connection.php";

if (isset($_POST["department"])) {
    if ($_POST["department"] === "R.N.G. Patel Institute of Technology") {
        $stmt = $connection->prepare("SELECT * FROM users WHERE role = 'Faculty'");
    } else {
        $stmt = $connection->prepare("SELECT * FROM users WHERE department = ? AND role = 'Faculty'");
        $stmt->bind_param("s", $_POST["department"]);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $count = $result->num_rows;

    $options = "";

    while ($row = $result->fetch_assoc()) {
        $name = $row["firstname"] . " " . $row["lastname"];
        $options .= '<option value="' . $row["user_id"] . '">' . $name . '</option>';
    }

    echo $count . "|||" . $options;
}

?>