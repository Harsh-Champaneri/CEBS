<?php

include "../connection.php";

session_start();

if (isset($_SESSION["user_id"])) {
  $limit = 5; // Maximum number of row in table

  // For Pagination
  $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

  if ($page < 1) {
    $page = 1;
  }

  // For Month Filter
  $month = $_POST['month'] ?? '';
  $monthNum = !empty($month) ? date("m", strtotime($month)) : '';

  // For Search Filter
  $search = $_POST['search'] ?? '';
  $searchLike = "%" . $search . "%";

  $offset = ($page - 1) * $limit;

  $table_data_query = $connection->prepare("SELECT
                                              event.*,
                                              participants.qrcode_url 
                                            FROM event 
                                            INNER JOIN participants 
                                              ON participants.event_id = event.event_id 
                                            WHERE participants.email = ?
                                            AND (? = '' OR MONTH(event.event_start_date) = ?)
                                            AND (? = '' OR event.event_name LIKE ? OR DATE_FORMAT(event.event_start_date, '%d %M %Y') LIKE ? OR event.event_venue LIKE ?)
                                            ORDER BY
                                              CASE 
                                                WHEN NOW() BETWEEN TIMESTAMP(event_start_date, event_start_time) AND TIMESTAMP(event_end_date, event_end_time)
                                                THEN 0

                                                WHEN NOW() < TIMESTAMP(event_start_date, event_start_time)
                                                THEN 1
                                                
                                                ELSE 2
                                              END,
                                              event_start_date ASC, event_start_time ASC
                                            LIMIT ? OFFSET ?");

  $table_data_query->bind_param("ssissssii", $_POST["email"], $month, $monthNum, $search, $searchLike, $searchLike, $searchLike, $limit, $offset);
  $table_data_query->execute();

  $table_data_result = $table_data_query->get_result();

  $table = "";
  $x = $offset + 1;

  if ($table_data_result->num_rows > 0) {
    while ($table_data = $table_data_result->fetch_assoc()) {
      $event_name = $table_data["event_name"];
      $event_start_date = date("jS F Y", strtotime($table_data["event_start_date"]));
      $event_venue = $table_data["event_venue"];

      // Upcoming, Active and Completed Event Logic
      $currentDateTime = date("Y-m-d H:i:s"); // System current date and time
      $eventDateTimeStart = $table_data["event_start_date"] . ' ' . $table_data["event_start_time"];   // Event start date and time
      $eventDateTimeEnd = $table_data["event_end_date"] . ' ' . $table_data["event_end_time"];   // Event end date and time

      if ($table_data["cancelled"] === "Yes") {
        $status = "cancelled";
        $qrcode = "<a class='qr-btn inactive' target='_blank' style='cursor: not-allowed; background: grey;'>
                    <i class='bi bi-qr-code'></i>
                  </a>";
      } else if (strtotime($currentDateTime) < strtotime($eventDateTimeStart)) {
        $status = "upcoming";
        $qrcode_url = $table_data["qrcode_url"];
        $qrcode = "<a href='{$qrcode_url}' class='qr-btn' target='_blank' title='View QR Code'>
                    <i class='bi bi-qr-code'></i>
                  </a>";
      } else if (strtotime($currentDateTime) > strtotime($eventDateTimeEnd)) {
        $status = "completed";
        $qrcode = "<a class='qr-btn inactive' target='_blank' style='cursor: not-allowed; background: grey;'>
                    <i class='bi bi-qr-code'></i>
                  </a>";
      } else {
        $status = "active";
        $qrcode_url = $table_data["qrcode_url"];
        $qrcode = "<a href='{$qrcode_url}' class='qr-btn' target='_blank' title='View QR Code'>
                    <i class='bi bi-qr-code'></i>
                  </a>";
      }

      $table .= "<tr>
                <td class='ticket-id' data-label='Ticket ID'>
                  {$x}
                </td>
                <td data-label='Event Name'>{$event_name}</td>
                <td class='event-date' data-label='Event Date'>{$event_start_date}</td>
                <td data-label='Venue'>
                  <div class='venue-info'>
                    <i class='bi bi-geo-alt-fill'></i>
                    <span>{$event_venue}</span>
                  </div>
                </td>
                <td data-label='Status'>
                  <span class='event-status {$status}'>{$status}</span>
                </td>
                <td class='text-center' data-label='QR Code'>
                  {$qrcode}
                </td>
              </tr>";
      $x++;
    }
  } else {
    $table .= "<tr>
                <td class='ticket-id' data-label='Ticket ID'>
                  -
                </td>
                <td data-label='Event Name'>-</td>
                <td data-label='Event Date'>-</td>
                <td data-label='Venue'>
                  <div class='venue-info'>
                    <span>-</span>
                  </div>
                </td>
                <td data-label='Status'>
                  <span>-</span>
                </td>
                <td class='text-center' data-label='QR Code'>
                  -
                </td>
              </tr>";
  }

  /* TOTAL COUNT */
  $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM participants INNER JOIN event ON participants.event_id = event.event_id WHERE email = ? 
                                      AND (? = '' OR MONTH(event.event_start_date) = ?)
                                      AND (? = '' OR event.event_name LIKE ? OR DATE_FORMAT(event.event_start_date, '%d %M %Y') LIKE ? OR event.event_venue LIKE ?)
                                    ");
  $totalQuery->bind_param("ssissss", $_POST["email"], $month, $monthNum, $search, $searchLike, $searchLike, $searchLike);
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
}

?>