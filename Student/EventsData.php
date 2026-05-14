<?php

session_start();

include "../connection.php";

if (isset($_SESSION["user_id"])) {
    $colours = ["slate", "coral", "pine", "ocean", "azure", "indigo", "sky", "lavender", "violet", "orchid", "mauve", "rose", "blush", "cherry", "peach", "sunset", "tangerine", "apricot", "mint", "sage", "seafoam", "jade", "pearl"];

    $limit = 6;

    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $limit;

    // All Events
    if (isset($_POST["activePage"]) && $_POST["activePage"] === "AllEvents") {
        // For Search Filter
        $search = $_POST['search'] ?? '';
        $searchLike = "%" . $search . "%";

        // Fecting Data from event table
        $event_data = $connection->prepare("SELECT * FROM event 
                                                    WHERE (? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?)
                                                    ORDER BY 
                                                        CASE 
                                                            WHEN(event_start_date > CURDATE() OR(event_start_date = CURDATE() AND event_start_time >= CURTIME()))
                                                                THEN 0
                                                            ELSE 1
                                                        END,
                                                    event_start_date ASC, event_start_time ASC
                                                    LIMIT ? OFFSET ?");
        $event_data->bind_param("ssssssssii", $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $limit, $offset);
        $event_data->execute();
        $result = $event_data->get_result();

        $output = "";

        if ($result->num_rows > 0) {
            /* TOTAL COUNT */
            $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM event WHERE (? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?)");
            $totalQuery->bind_param("ssssssss", $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
            $totalQuery->execute();

            $totalResult = $totalQuery->get_result()->fetch_assoc();

            $totalPages = ceil($totalResult['total'] / $limit);

            $prev = $page - 1;
            $disabled = ($page <= 1) ? "disabled" : "";

            $output .= "<section class='pb-5' id='event-section'><div class='container pt-5'><div class='row g-4'>";

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

                $currentDateTime = date("Y-m-d H:i:s"); // Current Date and Time of the system

                $eventDateTimeStart = $data["event_start_date"] . ' ' . $data["event_start_time"];   // Event start date and time
                $eventDateTimeEnd = $data["event_end_date"] . ' ' . $data["event_end_time"];   // Event end date and time

                $eventRegistrationDateTimeStart = $data["registration_start_date"] . ' ' . $data["registration_start_time"]; // Event registration start date and time 
                $eventRegistrationDateTimeEnd = $data["registration_end_date"] . ' ' . $data["registration_end_time"]; // Event registration end date and time

                $book_event = "";
                $view_details = "<form action='script.php' method='post'>
                                    <input type='hidden' name='event_id' value='{$event_id}'>
                                    <button type='submit' class='btn-book mt-3'><i class='bi bi-eye'></i> View Details</button>
                                </form>";

                $temp = null;

                if ($data["cancelled"] === "Yes") {
                    $temp = "<div style='padding: 10px 18px;
                                            width: fit-content;
                                            margin-top: 10px;
                                            justify-content: center;
                                            font-size: 0.85rem; 
                                            display: inline-flex; 
                                            align-items: center;
                                            margin-left: 0px;
                                            gap: 6px; 
                                            background: rgba(239, 68, 68, 0.1); 
                                            color: #dc2626; 
                                            border-radius: 50px; 
                                            font-weight: 600; 
                                            border: 1px solid rgba(239, 68, 68, 0.2);'>
                                                    ❌ This Event has been Cancelled
                                </div>";
                } else if (strtotime($currentDateTime) > strtotime($eventRegistrationDateTimeEnd)) {
                    $temp = $view_details . $book_event;
                } else {
                    if (strtotime($currentDateTime) >= strtotime($eventRegistrationDateTimeStart)) {
                        $book_event = "<a href='BookEventForm.php?event_id=$event_id' class='btn-book mt-3' id='btn-red'>
                                            <i class='bi bi-calendar-plus'></i> Book Event
                                        </a>";

                        $temp = $view_details . $book_event;
                    }
                }

                $output .= "
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
                                        {$temp}
                                    </div>
                                </div>
                            </div>
                        </div>";
            }

            $output .= "<div class='pagination-div'><ul class='pagination' id='pagination'>";

            $output .= "<li class='page-item $disabled'>
                            <a class='page-link' href='#' data-page='$prev'>«</a>
                        </li>";

            /* NUMBERS */
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? "active" : "";

                $output .= "<li class='page-item'>
                                <a class='page-link $active' href='#' data-page='$i'>$i</a>
                            </li>";
            }

            /* NEXT */
            $next = $page + 1;
            $disabled = ($page >= $totalPages) ? "disabled" : "";

            $output .= "<li class='page-item $disabled'>
                            <a class='page-link' href='#' data-page='$next'>»</a>
                        </li>";

            $output .= "</ul></div></div></div></section>";
        } else {
            if (!empty($search)) {
                $output .= "<section>
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
            } else {
                $output .= "<section>
                                <div class='container mt-3 mb-5 p-0'>
                                    <div class='welcome-card '>
                                        <div class='row align-items-center'>
                                            <div class='col-lg-9'>
                                                <h2>No Events Available 🎟️</h2>
                                                <p class='greeting mb-4'>
                                                    There are no events available right now. Once faculty creates new events, they will appear here for registration.
                                                </p>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>";
            }
        }
    }

    // Upcoming Events
    if (isset($_POST["activePage"]) && $_POST["activePage"] === "UpcomingEvents") {
        $email = $_POST["email"];

        // For Search Filter 
        $search = $_POST['search'] ?? '';
        $searchLike = "%" . $search . "%";

        $event_data = $connection->prepare("SELECT event.* FROM event 
                                                                INNER JOIN participants 
                                                                ON participants.event_id = event.event_id 
                                                                WHERE participants.email = ? 
                                                                AND(TIMESTAMP(event_start_date, event_start_time) >= NOW())
                                                                AND cancelled = 'No'
                                                                AND(? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?) 
                                                                ORDER BY event_start_date
                                                                LIMIT ? OFFSET ?");
        $event_data->bind_param("sssssssssii", $email, $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $limit, $offset);
        $event_data->execute();
        $result = $event_data->get_result();

        $output = "";

        if ($result->num_rows > 0) {
            /* TOTAL COUNT */
            $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM event 
                                                                            INNER JOIN participants 
                                                                            ON participants.event_id = event.event_id 
                                                                            WHERE participants.email = ?
                                                                            AND cancelled = 'No'
                                                                            AND(? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?)");
            $totalQuery->bind_param("sssssssss", $email, $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
            $totalQuery->execute();

            $totalResult = $totalQuery->get_result()->fetch_assoc();

            $totalPages = ceil($totalResult['total'] / $limit);

            $prev = $page - 1;
            $disabled = ($page <= 1) ? "disabled" : "";

            $output .= "<section class='pb-5' id='event-section'><div class='container pt-5'><div class='row g-4'>";

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

                $currentDateTime = date("Y-m-d H:i:s"); // Current Date and Time of the system

                $eventDateTimeStart = $data["event_start_date"] . ' ' . $data["event_start_time"];   // Event start date and time
                $eventDateTimeEnd = $data["event_end_date"] . ' ' . $data["event_end_time"];   // Event end date and time

                $eventRegistrationDateTimeStart = $data["registration_start_date"] . ' ' . $data["registration_start_time"]; // Event registration start date and time 
                $eventRegistrationDateTimeEnd = $data["registration_end_date"] . ' ' . $data["registration_end_time"]; // Event registration end date and time

                $output .= "
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
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
            }
            $output .= "<div class='pagination-div'><ul class='pagination' id='pagination'>";

            $output .= "<li class='page-item $disabled'>
                                <a class='page-link' href='#' data-page='$prev'>«</a>
                            </li>";

            /* NUMBERS */
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? "active" : "";

                $output .= "<li class='page-item'>
                                    <a class='page-link $active' href='#' data-page='$i'>$i</a>
                                </li>";
            }

            /* NEXT */
            $next = $page + 1;
            $disabled = ($page >= $totalPages) ? "disabled" : "";

            $output .= "<li class='page-item $disabled'>
                                <a class='page-link' href='#' data-page='$next'>»</a>
                            </li>";

            $output .= "</ul></div></div></div></section>";
        } else {
            if (!empty($search)) {  // No searched events
                $output .= "<section>
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
            } else {    // No events
                $output .= "<section>
                                <div class='container mt-4 mb-2 p-0'>
                                    <div class='welcome-card '>
                                        <div class='row align-items-center'>
                                            <div class='col-lg-8'>
                                                <h2>No Upcoming Events Available 🎟️</h2>
                                                <p class='greeting mb-3'>
                                                    There are no upcoming events available right now. Explore available events and secure your spot today.
                                                </p>
                                                <div class='d-flex gap-2 flex-wrap'>
                                                    <a
                                                        href='AllEvents.php'
                                                        class='btn-hero'
                                                        style='padding: 10px 24px; font-size: 0.85rem'>
                                                        <i class='bi bi-calendar-plus'></i> Browse Events
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>";
            }
        }
    }

    // My Events
    if (isset($_POST["activePage"]) && $_POST["activePage"] === "MyEvents") {
        $email = $_POST["email"];

        // For Search Filter
        $search = $_POST['search'] ?? '';
        $searchLike = "%" . $search . "%";

        $event_data = $connection->prepare("SELECT event.* FROM event 
                                                                INNER JOIN participants ON participants.event_id = event.event_id 
                                                                WHERE participants.email = ?
                                                                AND(? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?)
                                                                ORDER BY event_start_date 
                                                                LIMIT ? OFFSET ?");
        $event_data->bind_param("sssssssssii", $email, $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $limit, $offset);
        $event_data->execute();
        $result = $event_data->get_result();

        $output = "";

        if ($result->num_rows > 0) {
            /* TOTAL COUNT */
            $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM event 
                                                                    INNER JOIN participants ON participants.event_id = event.event_id 
                                                                    WHERE participants.email = ?
                                                                    AND(? = '' OR event_name LIKE ? OR DATE_FORMAT(event_start_date, '%d %M %Y') LIKE ? OR event_venue LIKE ? OR registration_fee LIKE ? OR TIME_FORMAT(event_start_time, '%h:%i %p') LIKE ? OR TIME_FORMAT(event_end_time, '%h:%i %p') LIKE ? OR organized_by LIKE ?)");
            $totalQuery->bind_param("sssssssss", $email, $search, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike);
            $totalQuery->execute();

            $totalResult = $totalQuery->get_result()->fetch_assoc();

            $totalPages = ceil($totalResult['total'] / $limit);

            $prev = $page - 1;
            $disabled = ($page <= 1) ? "disabled" : "";

            $output .= "<section class='pb-5' id='event-section'><div class='container pt-5'><div class='row g-4'>";

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

                $temp = null;

                if ($data["cancelled"] === "Yes") {
                    $temp = "<div style='padding: 10px 18px;
                                            width: fit-content;
                                            margin-top: 10px;
                                            justify-content: center;
                                            font-size: 0.85rem; 
                                            display: inline-flex; 
                                            align-items: center;
                                            margin-left: 0px;
                                            gap: 6px; 
                                            background: rgba(239, 68, 68, 0.1); 
                                            color: #dc2626; 
                                            border-radius: 50px; 
                                            font-weight: 600; 
                                            border: 1px solid rgba(239, 68, 68, 0.2);'>
                                                    ❌ This Event has been Cancelled
                                </div>";
                } else {
                    $temp = "<form action='script.php' method='post'>
                                <input type='hidden' name='event_id' value='{$event_id}'>
                                <button type='submit' class='btn-book mt-3'><i class='bi bi-eye'></i> View Details</button>
                            </form>";
                }

                $output .= "
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
                            {$temp}
                        </div>
                    </div>
                </div>
                    ";
            }
            $output .= "<div class='pagination-div'><ul class='pagination' id='pagination'>";

            $output .= "<li class='page-item $disabled'>
                                <a class='page-link' href='#' data-page='$prev'>«</a>
                            </li>";

            /* NUMBERS */
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? "active" : "";

                $output .= "<li class='page-item'>
                                    <a class='page-link $active' href='#' data-page='$i'>$i</a>
                                </li>";
            }

            /* NEXT */
            $next = $page + 1;
            $disabled = ($page >= $totalPages) ? "disabled" : "";

            $output .= "<li class='page-item $disabled'>
                                <a class='page-link' href='#' data-page='$next'>»</a>
                            </li>";

            $output .= "</ul></div></div></div></section>";
        } else {
            if (!empty($search)) {  // No searched events
                $output .= "<section>
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
            } else {    // No events
                $output .= "<section>
                                <div class='container mt-4 mb-2 p-0'>
                                    <div class='welcome-card '>
                                        <div class='row align-items-center'>
                                            <div class='col-lg-8'>
                                                <h2>No Registered Events Yet 🎟️</h2>
                                                <p class='greeting mb-3'>
                                                    You haven’t registered for any events yet. Explore upcoming events and secure your spot today.
                                                </p>
                                                <div class='d-flex gap-2 flex-wrap'>
                                                    <a
                                                        href='AllEvents.php'
                                                        class='btn-hero'
                                                        style='padding: 10px 24px; font-size: 0.85rem'>
                                                        <i class='bi bi-calendar-plus'></i> Browse Events
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>";
            }
        }
    }

    echo json_encode([
        "output" => $output,
    ]);

    exit();
}

?>