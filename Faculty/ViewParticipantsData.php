<?php
include "../connection.php";

session_start();

$limit = 5;

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

// For Search Filter
$search = $_POST['search'] ?? '';
$searchLike = "%" . $search . "%";

/* MAIN QUERY WITH LIMIT */
$query = $connection->prepare("SELECT 
                                e.event_id,
                                e.event_name,
                                e.event_start_date,
                                e.event_start_time,
                                e.event_end_date,
                                e.event_end_time,
                                e.event_venue,
                                e.cancelled,

                                (SELECT COUNT(*) FROM participants p WHERE p.event_id = e.event_id) AS participant_count,

                                (SELECT COALESCE(SUM(amount), 0)
                                FROM payments pay 
                                WHERE pay.event_id = e.event_id 
                                AND (pay.status = 'FREE' OR pay.status = 'captured')) AS revenue

                                FROM faculty_coordinators fc
                                JOIN event e ON fc.event_id = e.event_id
                                WHERE fc.user_id = ?
                                AND (? = '' OR e.event_name LIKE ? OR DATE_FORMAT(e.event_start_date, '%d %M %Y') LIKE ? OR e.event_venue LIKE ?)

                                ORDER BY 
                                    CASE 
                                        WHEN (
                                            TIMESTAMP(e.event_start_date, e.event_start_time) > NOW()
                                            OR TIMESTAMP(e.event_end_date, e.event_end_time) >= NOW()
                                        )
                                        THEN 0
                                        ELSE 1
                                    END,
                                    e.event_start_date ASC,
                                    e.event_start_time ASC

                                LIMIT ? OFFSET ?
");

$query->bind_param("sssssii", $_SESSION["user_id"], $search, $searchLike, $searchLike, $searchLike, $limit, $offset);
$query->execute();
$result = $query->get_result();

$table = "";
$x = $offset + 1;

if ($result->num_rows > 0) {
    /* LOOP */
    while ($data = $result->fetch_assoc()) {

        $event_id = $data["event_id"];
        $event_name = $data["event_name"];
        $event_start_date = date("d F Y", strtotime($data["event_start_date"]));
        $event_venue = $data["event_venue"];
        $participants_count = $data["participant_count"];
        $revenue = $data["revenue"] ?? 0;

        $currentDateTime = date("Y-m-d H:i:s");
        $eventStart = $data["event_start_date"] . ' ' . $data["event_start_time"];
        $eventEnd = $data["event_end_date"] . ' ' . $data["event_end_time"];

        if ($data["cancelled"] === "Yes") { // Cancelled Event
            $status = "cancelled";
            $edit = "-";    // Edit Button
            $view_details = "";     // View Details Button
        } elseif (strtotime($currentDateTime) < strtotime($eventStart)) {   // Upcoming Event
            $status = "upcoming";
            $edit = "<a href='AddEvent.php?event_id=$event_id' class='action-icon edit' title='Edit Event'>
                        <i class='bi bi-pencil'></i>
                    </a>";
            // View Details Button
            $view_details = "<form action='script.php' method='post'>
                                <input type='hidden' name='event_id' value='{$event_id}'>
                                <button type='submit' class='action-icon'><i class='bi bi-eye'></i></button>
                            </form>";
        } elseif (strtotime($currentDateTime) > strtotime($eventEnd)) { // Completed Event
            $status = "completed";
            $edit = ""; // Edit Button
            // View Details Button
            $view_details = "<form action='script.php' method='post'>
                                <input type='hidden' name='event_id' value='{$event_id}'>
                                <button type='submit' class='action-icon'><i class='bi bi-eye'></i></button>
                            </form>";
        } else {    // Active Event
            $status = "active";
            $edit = ""; // Edit Button
            // View Details Button
            $view_details = "<form action='script.php' method='post'>
                                <input type='hidden' name='event_id' value='{$event_id}'>
                                <button type='submit' class='action-icon'><i class='bi bi-eye'></i></button>
                            </form>";
        }

        $table .= "<tr>
                                <td class='event-id' data-label='Event ID'>{$x}</td>
                                <td data-label='Event Name'>{$event_name}</td>
                                <td data-label='Date & Time'>
                                    <div>{$event_start_date}</div>
                                </td>
                                <td data-label='Venue'>{$event_venue}</td>
                                <td data-label='Participants'>
                                    <div class='participant-count'>
                                        <i class='bi bi-people-fill'></i>
                                        <span>{$participants_count}</span>
                                    </div>
                                </td>
                                <td class='revenue-info' data-label='Revenue'>₹{$revenue}</td>
                                <td data-label='Status'><span class='event-status {$status}'>{$status}</span></td>
                                <td class='text-center' data-label='Actions'>
                                    <div class='action-icons'>
                                        {$view_details}
                                        {$edit} 
                                    </div>
                                </td>
                                <td class='text-center' data-label='Export Data'>
                                    <form method='post' action='ParticipantsList.php'>
                                    <div class='export-buttons'>
                                        <input type='hidden' name='event_id' value='{$event_id}'>
                                        <button type='submit' name='excel' class='export-btn export-excel' title='Download Excel'>
                                            <i class='bi bi-file-earmark-excel'></i>
                                            Excel
                                        </button>
                                        <button type='submit' name='pdf' class='export-btn export-pdf' title='Download PDF'>
                                            <i class='bi bi-file-earmark-pdf'></i>
                                            PDF
                                        </button>
                                    </div>
                                    </form>
                                </td>
                            </tr>";

        $x++;
    }
} else {
    $table .= "<tr>
                                <td class='event-id' data-label='Event ID'>-</td>
                                <td data-label='Event Name'>-</td>
                                <td data-label='Date & Time'>
                                    <div>-</div>
                                </td>
                                <td data-label='Venue'>-</td>
                                <td data-label='Participants'>
                                    <div class='participant-count'>
                                        <i class='bi bi-people-fill'></i>
                                        <span>-</span>
                                    </div>
                                </td>
                                <td class='revenue-info' data-label='Revenue'>-</td>
                                <td data-label='Status'><span>-</span></td>
                                <td class='text-center' data-label='Actions'>
                                    <div class='action-icons'>-</div>
                                </td>
                                <td class='text-center' data-label='Export Data'>
                                    <div class='export-buttons'>-</div>
                                </td>
                            </tr>";
}

/* TOTAL COUNT */
$totalQuery = $connection->prepare("SELECT COUNT(*) as total
    FROM faculty_coordinators fc
    JOIN event e ON fc.event_id = e.event_id
    WHERE fc.user_id = ?
    AND (? = '' OR e.event_name LIKE ? OR DATE_FORMAT(e.event_start_date, '%d %M %Y') LIKE ? OR e.event_venue LIKE ?)
");
$totalQuery->bind_param("sssss", $_SESSION["user_id"], $search, $searchLike, $searchLike, $searchLike,);
$totalQuery->execute();
$totalResult = $totalQuery->get_result()->fetch_assoc();

$totalPages = ceil($totalResult['total'] / $limit);

/* PAGINATION */
$pagination = "";

/* PREV */
$prev = $page - 1;
$disabled = ($page <= 1) ? "disabled" : "";

$pagination .= "<li class='page-item $disabled'>
                    <a class='page-link' href='#' data-page='$prev'>«</a>
                </li>";

/* NUMBERS */
for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $page) ? "active" : "";

    $pagination .= "<li class='page-item'>
                        <a class='page-link $active' href='#' data-page='$i'>$i</a>
                    </li>";
}

/* NEXT */
$next = $page + 1;
$disabled = ($page >= $totalPages) ? "disabled" : "";

$pagination .= "<li class='page-item $disabled'>
                    <a class='page-link' href='#' data-page='$next'>»</a>
                </li>";

echo json_encode([
    "table" => $table,
    "pagination" => $pagination
]);

?>