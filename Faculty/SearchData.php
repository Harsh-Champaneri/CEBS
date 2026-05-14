<?php

session_start();
include "../connection.php";

if (isset($_POST["searchData"])) {
    $colours = ["slate", "coral", "pine", "ocean", "azure", "indigo", "sky", "lavender", "violet", "orchid", "mauve", "rose", "blush", "cherry", "peach", "sunset", "tangerine", "apricot", "mint", "sage", "seafoam", "jade", "pearl"];

    if (isset($_SESSION["active_page"]) && $_SESSION["active_page"] === "ManageEvents") {
        $stmt = $connection->prepare("SELECT * FROM faculty_coordinators INNER JOIN event ON event.event_id = faculty_coordinators.event_id WHERE user_id = ? AND(event_name LIKE CONCAT('%',?,'%') OR event_start_date LIKE CONCAT('%',?,'%') OR event_venue LIKE CONCAT('%',?,'%') OR registration_fee LIKE CONCAT('%',?,'%') OR event_start_time LIKE CONCAT('%',?,'%') OR event_end_time  LIKE CONCAT('%',?,'%') OR organized_by LIKE CONCAT('%',?,'%'))");
        $stmt->bind_param("ssssssss", $_POST["user_id"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"]);
    } else if (isset($_SESSION["active_page"]) && $_SESSION["active_page"] === "MyEvents") {
        $stmt = $connection->prepare("SELECT * FROM event WHERE created_by = ? AND(event_name LIKE CONCAT('%',?,'%') OR event_start_date LIKE CONCAT('%',?,'%') OR event_venue LIKE CONCAT('%',?,'%') OR registration_fee LIKE CONCAT('%',?,'%') OR event_start_time LIKE CONCAT('%',?,'%') OR event_end_time  LIKE CONCAT('%',?,'%') OR organized_by LIKE CONCAT('%',?,'%'))");
        $stmt->bind_param("ssssssss", $_POST["user_id"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"]);
    } else {
        $stmt = $connection->prepare("SELECT * FROM event WHERE event_name LIKE CONCAT('%',?,'%') OR event_start_date LIKE CONCAT('%',?,'%') OR event_venue LIKE CONCAT('%',?,'%') OR registration_fee LIKE CONCAT('%',?,'%') OR event_start_time LIKE CONCAT('%',?,'%') OR event_end_time  LIKE CONCAT('%',?,'%') OR organized_by LIKE CONCAT('%',?,'%')");
        $stmt->bind_param("sssssss", $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"], $_POST["searchData"]);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<section class='pb-5'><div class='container pt-5'><div class='row g-4'>";
        while ($data = $result->fetch_assoc()) {
            $event_id = $data["event_id"];
            $date = $data["event_start_date"];

            $day = date("d", strtotime($date));
            $month = date("F", strtotime($date));

            $event_name = $data["event_name"];

            $event_start_time = date("H:i A", strtotime($data["event_start_time"]));
            $event_end_time = date("H:i A", strtotime($data["event_end_time"]));

            $event_venue = $data["event_venue"];

            $registration_fee = $data["registration_fee"];

            if (isset($_SESSION["active_page"])) {
                if ($_SESSION["active_page"] === "ManageEvents" || $_SESSION["active_page"] === "MyEvents") {

                    $current_date = date("Y-m-d");

                    if ($current_date < $data["event_start_date"]) {
                        $edit = "<a href='AddEvent.php?event_id={$event_id}' class='btn-book mt-3' id='btn-red'>
                                    <i class='bi bi-calendar-plus'></i> Edit Event
                                </a>";
                    } else {
                        $edit = "<button class='btn-book mt-3' id='btn-red' onclick='showAlert()'><i class='bi bi-calendar-plus'></i> Edit Event</button>";
                    }
                } else {
                    $edit = "";
                }
            }

            echo "
                        <div class='col-md-4'>
                    <div class='event-card'>
                        <div class='event-header eh-{$colours[array_rand($colours)]}'>
                            <div class='date-badge'>
                                <div class='day'>{$day}</div>
                                <div class='mon'>{$month}</div>
                            </div>
                        </div>
                        <div class='event-body'>
                            <h6>{$event_name}</h6>
                            <div class='event-meta'><i class='bi bi-clock'></i> {$event_start_time} – {$event_end_time}</div>
                            <div class='event-meta'><i class='bi bi-geo-alt'></i> {$event_venue}</div>
                            <div class='event-meta'><i class='bi bi-ticket'></i> ₹ {$registration_fee}</div>
                            <div class='btn-container'>
                                <form action='script.php' method='post'>
                                    <input type='hidden' name='event_id' value='{$event_id}'>
                                    <button type='submit' class='btn-book mt-3'><i class='bi bi-eye'></i> View Details</button>
                                </form>
                                {$edit}
                            </div>
                        </div>
                    </div>
                </div>
                    ";
        }
        echo "</div></section>";
    } else {
        echo "<section>
            <div class='container mt-4 mb-2 p-0'>
                <div class='welcome-card'>
                    <div class='row align-items-center'>
                        <div class='col-lg-8'>
                            <h2>No Results Found ❌</h2>
                            <p class='greeting mb-3'>We couldn't find any events matching your search.</p>
                            <div style=' padding: 10px 18px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.1); color: #dc2626; border-radius: 50px; font-weight: 600; border: 1px solid rgba(239, 68, 68, 0.2);'>
                                <i class='bi bi-search'></i>Try searching with different keywords
                            </div>
                        </div>   
                    </div>
                </div>
            </div>
        </section>";
    }
}

?>