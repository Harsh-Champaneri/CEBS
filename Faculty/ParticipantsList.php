<?php

session_start();
include "../connection.php";

require '../dompdf/autoload.inc.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

if (isset($_SESSION["user_id"])) {

  if (isset($_POST["event_id"])) {
    $event_id = $_POST["event_id"]; // event_id

    // Participants Data from participants and users Table
    $paricipants_data_query = $connection->prepare("SELECT 
    users.firstname, 
    users.lastname, 
    users.email, 
    users.department, 
    users.contact_number, 
    participants.student_enrollment, 
    participants.team_name, 
    participants.attended,
    payments.payment_id
  FROM participants 
  INNER JOIN users ON participants.email = users.email 
  LEFT JOIN payments 
    ON payments.email = participants.email 
    AND payments.event_id = participants.event_id 
    AND (payments.status = 'captured' OR payments.status = 'FREE')
  WHERE participants.event_id = ?
");

    $paricipants_data_query->bind_param("s", $event_id);
    $paricipants_data_query->execute();
    $paricipants_data_result = $paricipants_data_query->get_result();

    // Event Data from event Table
    $event_data_query = $connection->prepare("SELECT event_type, event_name, event_category, event_venue, event_start_date, event_start_time, organized_by, cancelled FROM event WHERE event_id = ?");
    $event_data_query->bind_param("s", $event_id);
    $event_data_query->execute();
    $event_data_result = $event_data_query->get_result();
    $event_data = $event_data_result->fetch_assoc();

    $event_type = $event_data["event_type"];

    $event_name = $event_data["event_name"];
    $event_category = $event_data["event_category"];
    $venue = $event_data["event_venue"];
    $event_start_date = date("d F Y", strtotime($event_data["event_start_date"]));
    $event_start_time = date("h:i A", strtotime($event_data["event_start_time"]));
    $date_and_time = $event_start_date . ", " . $event_start_time;
    $organized_by = $event_data["organized_by"];

    $event_cancelled = $event_data["cancelled"];

    // Faculty Coordinator Data from faculty_coordinators and users Table
    $faculty_coordinator_data_query = $connection->prepare("SELECT firstname, lastname FROM users INNER JOIN faculty_coordinators ON faculty_coordinators.user_id = users.user_id WHERE faculty_coordinators.event_id = ?");
    $faculty_coordinator_data_query->bind_param("s", $event_id);
    $faculty_coordinator_data_query->execute();
    $faculty_coordinator_data_result = $faculty_coordinator_data_query->get_result();

    $faculty_coordinator = [];
    while ($faculty_coordinator_data = $faculty_coordinator_data_result->fetch_assoc()) {
      $faculty_coordinator[] = "Prof. " . $faculty_coordinator_data["firstname"] . " " . $faculty_coordinator_data["lastname"];
    }
    $faculty_coordinator_name = implode(", ", $faculty_coordinator);

    // Student Coordinator Data from student_coordinators Table
    $student_coordinator_data_query = $connection->prepare("SELECT student_name FROM student_coordinators WHERE event_id = ?");
    $student_coordinator_data_query->bind_param("s", $event_id);
    $student_coordinator_data_query->execute();
    $student_coordinator_data_result = $student_coordinator_data_query->get_result();

    $student_coordinator = [];
    while ($student_coordinator_data = $student_coordinator_data_result->fetch_assoc()) {
      $student_coordinator[] = $student_coordinator_data["student_name"];
    }
    $student_coordinator_name = implode(", ", $student_coordinator);

    // For PDF
    if (isset($_POST["pdf"])) {
      if ($event_type === "Team") {
        $th = "<th>Name</th>
              <th>PEN</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Department</th>
              <th>Team Name</th>
              <th>Attended</th>";

        $style = "";
      } else {
        $th = "<th>Name</th>
              <th>PEN</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Department</th>
              <th>Attended</th>";
      }

      $html =  "<!doctype html>
                  <html>
                    <head>
                      <meta charset='UTF-8' />
                      <title>Participants List</title>

                  <style>
                    @page {
                      size: A4 portrait;
                      margin: 20px;
                    }

                    body {
                      font-family: DejaVu Sans, sans-serif;
                      font-size: 11px;
                      line-height: 1.4;
                    }

                    .container {
                      width: 100%;
                    }

                    h2,
                    h3 {
                      text-align: center;
                      margin-bottom: 8px;
                    }

                    h3 {
                      padding: 5px;
                    }

                    .section {
                      margin-bottom: 8px;
                    }

                    .label {
                      font-weight: bold;
                    }

                    table {
                      width: 100%;
                      border-collapse: collapse;
                      table-layout: fixed;
                      margin-top: 0px;
                    }

                    th,
                    td {
                      border: 1px solid #000;
                      padding: 5px;
                      word-wrap: break-word;
                      vertical-align: top;
                    }

                    th {
                      text-align: center;
                      font-weight: bold;
                    }

                    td {
                      text-align: center;
                    }

                    th:nth-child(1),
                    td:nth-child(1) {
                      width: 15%;
                    }

                    th:nth-child(2),
                    td:nth-child(2) {
                      width: 11%;
                    }

                    th:nth-child(3),
                    td:nth-child(3) {
                      width: 14%;
                    }

                    th:nth-child(4),
                    td:nth-child(4) {
                      width: 10%;
                    }

                    th:nth-child(5),
                    td:nth-child(5) {
                      width: 12%;
                    }

                    th:nth-child(6),
                    td:nth-child(6) {
                      width: 10%;
                    }

                    th:nth-child(7),
                    td:nth-child(7) {
                      width: 7.5%;
                    }
                  </style>
                </head>

                <body>
                  <div class='container'>
                    <h2>R.N.G Patel Institute of Technology</h2>

                    <div class='section'>
                      <div><span class='label'>Event Name:</span> {$event_name}</div>
                      <div><span class='label'>Event Category:</span> {$event_category}</div>
                      <div><span class='label'>Event Type:</span> {$event_type}</div>
                      <div><span class='label'>Venue:</span> {$venue}</div>
                      <div><span class='label'>Date & Time:</span> {$date_and_time}</div>
                      <div><span class='label'>Organized By:</span> {$organized_by}</div>
                    </div>

                    <div class='section'>
                      <div><span class='label'>Faculty Coordinators:</span> {$faculty_coordinator_name}</div>
                      <div><span class='label'>Student Coordinators:</span> {$student_coordinator_name}</div>
                    </div>

                    <h3 style='border: 1px solid black; border-bottom: none; margin-bottom: 0'>{$event_name} Participants List</h3>
                    <table>
                      <thead>
                        <tr>
                          {$th}
                        </tr>
                      </thead>

                      <tbody>";

      while ($paricipants_data = $paricipants_data_result->fetch_assoc()) {
        $name = $paricipants_data["firstname"] . " " . $paricipants_data["lastname"];
        $email = $paricipants_data["email"];
        $pen = $paricipants_data["student_enrollment"];
        $contact = $paricipants_data["contact_number"];
        $department = $paricipants_data["department"];
        $attended = $paricipants_data["attended"];

        if ($event_type === "Team") {
          $team_name = $paricipants_data["team_name"];
          $html .= "<tr>
                      <td>{$name}</td>
                      <td>{$pen}</td>
                      <td>{$email}</td>
                      <td>{$contact}</td>
                      <td>{$department}</td>
                      <td>{$team_name}</td>
                      <td>{$attended}</td>
                    </tr>";
        } else {
          $html .= "<tr> 
                      <td>{$name}</td>
                      <td>{$pen}</td>
                      <td>{$email}</td>
                      <td>{$contact}</td>
                      <td>{$department}</td>
                      <td>{$attended}</td>
                    </tr>";
        }
      }

      $html .= "</tbody></table></div></body></html>";

      $dompdf = new Dompdf([
        'isRemoteEnabled' => true
      ]);

      setcookie("fileDownload", "true", time() + 60, "/");
      $dompdf->setPaper('A4', 'portrait');

      $dompdf->loadHtml($html);
      $dompdf->render();

      $dompdf->stream($event_name, ['Attachment' => true]);
    }

    // For Excel
    if (isset($_POST["excel"])) {
      // Create spreadsheet
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      // Header row
      $rowNumber = 1;

      $heading = ["Sr. No.", "Name", "Email", "PEN", "Contact", "Department"];

      if ($event_type === "Team") {
        $heading[] = "Team Name";
      }

      $heading[] = "Attended";

      if ($event_cancelled === "Yes" && !empty($paricipants_data["payment_id"])) {
        $heading[] = "Refund Status";
      }

      $sheet->fromArray($heading, NULL, "A" . $rowNumber);
      $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

      $rowNumber++;

      // Data rows
      $x = 1;
      while ($paricipants_data = $paricipants_data_result->fetch_assoc()) {
        $name = $paricipants_data["firstname"] . " " . $paricipants_data["lastname"];

        $row_data = [
          $x,
          $name,
          $paricipants_data["email"],
          $paricipants_data["student_enrollment"],
          $paricipants_data["contact_number"],
          $paricipants_data["department"],
        ];

        if ($event_type === "Team") {
          $row_data[] = $paricipants_data["team_name"];
        }

        $row_data[] = $paricipants_data["attended"];

        if ($event_cancelled === "Yes") {
          if (!empty($paricipants_data["payment_id"])) {
            $row_data[] = "Refunded";
          }
        }

        $sheet->fromArray($row_data, NULL, "A" . $rowNumber);

        $rowNumber++;
        $x++;
      }

      $lastColumn = $sheet->getHighestColumn();
      $lastRow = $sheet->getHighestRow();

      // Formate PEN to Fraction
      $sheet->getStyle('D2:D' . $lastRow)->getNumberFormat()->setFormatCode('0');

      // Center each row and column
      $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

      // Auto column size
      foreach (range('A', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // Sheet Title
      $sheet->setTitle("{$event_name}");

      // Download Excel file
      setcookie("fileDownload", "true", time() + 60, "/");
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header("Content-Disposition: attachment; filename={$event_name}.xlsx");
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit();
    }
  }
} else {
  header("location:../login.php");
  exit();
}

?>