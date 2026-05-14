<?php

session_start();
include "../connection.php";

if (isset($_POST["searchData"])) {
    $colours = ["slate", "coral", "pine", "ocean", "azure", "indigo", "sky", "lavender", "violet", "orchid", "mauve", "rose", "blush", "cherry", "peach", "sunset", "tangerine", "apricot", "mint", "sage", "seafoam", "jade", "pearl"];
    $search = $_POST["searchData"];

    $stmt = null;

    if (isset($_POST["page"]) && $_POST["page"] === "MyEvents") {
        $email = $_POST["email"];
        $stmt = $connection->prepare("SELECT event.* FROM event INNER JOIN participants ON participants.event_id = event.event_id WHERE participants.email = ? AND (event.event_name LIKE CONCAT('%', ?, '%') OR event.event_start_date LIKE CONCAT('%', ?, '%') OR event.event_venue LIKE CONCAT('%', ?, '%') OR event.registration_fee LIKE CONCAT('%', ?, '%') OR event.event_start_time LIKE CONCAT('%', ?, '%') OR event.event_end_time LIKE CONCAT('%', ?, '%') OR event.organized_by LIKE CONCAT('%', ?, '%'))");
        $stmt->bind_param("ssssssss", $email, $search, $search, $search, $search, $search, $search, $search);
    } else if (isset($_POST["page"]) && $_POST["page"] === "AllEvents") {
        $stmt = $connection->prepare("SELECT * FROM event WHERE event_name LIKE CONCAT('%',?,'%') OR event_start_date LIKE CONCAT('%',?,'%') OR event_venue LIKE CONCAT('%',?,'%') OR registration_fee LIKE CONCAT('%',?,'%') OR event_start_time LIKE CONCAT('%',?,'%') OR event_end_time LIKE CONCAT('%',?,'%') OR organized_by LIKE CONCAT('%',?,'%') 
                                                    ORDER BY 
                                                        CASE 
                                                            WHEN(event_start_date > CURDATE() OR(event_start_date = CURDATE() AND event_start_time >= CURTIME()))
                                                                THEN 0
                                                            ELSE 1
                                                        END,
                                                    event_start_date ASC, event_start_time ASC");
        $stmt->bind_param("sssssss", $search, $search, $search, $search, $search, $search, $search);
    } else if (isset($_POST["page"]) && $_POST["page"] === "UpcomingEvents") {
        $stmt = $connection->prepare("SELECT * FROM event WHERE TIMESTAMP(event_start_date, event_start_time) >= NOW() AND(event_name LIKE CONCAT('%',?,'%') OR event_start_date LIKE CONCAT('%',?,'%') OR event_venue LIKE CONCAT('%',?,'%') OR registration_fee LIKE CONCAT('%',?,'%') OR event_start_time LIKE CONCAT('%',?,'%') OR event_end_time  LIKE CONCAT('%',?,'%') OR organized_by LIKE CONCAT('%',?,'%')) ORDER BY event_start_date");
        $stmt->bind_param("sssssss", $search, $search, $search, $search, $search, $search, $search);
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

            if (isset($_POST["page"]) && $_POST["page"] === "MyEvents") {
                $button = "";
            } else {
                $currentDateTime = date("Y-m-d H:i:s");

                $eventDateTimeStart = $data["event_start_date"] . ' ' . $data["event_start_time"];   // Event start date and time
                $eventDateTimeEnd = $data["event_end_date"] . ' ' . $data["event_end_time"];   // Event end date and time

                $eventRegistrationDateTimeStart = $data["registration_start_date"] . ' ' . $data["registration_start_time"];
                $eventRegistrationDateTimeEnd = $data["registration_end_date"] . ' ' . $data["registration_end_time"];

                $button = "";

                if (strtotime($currentDateTime) > strtotime($eventRegistrationDateTimeEnd)) {
                    $button = "";
                } else {
                    if (strtotime($currentDateTime) >= strtotime($eventRegistrationDateTimeStart)) {
                        $button = "<a href='BookEventForm.php?event_id=$event_id' class='btn-book mt-3' id='btn-red'>
                                        <i class='bi bi-calendar-plus'></i> Book Event
                                    </a>";
                    }
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
                                {$button}
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