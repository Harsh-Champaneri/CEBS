<?php

session_start();

include "../connection.php";

require '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

if (isset($_SESSION["user_id"])) {
  if (isset($_POST["invoice"])) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $_SESSION["user_id"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $firstname = $data["firstname"];
    $lastname = $data["lastname"];

    $invoice_id = $_POST["invoice_id"];

    $invoice_data_query = $connection->prepare("SELECT * FROM payments WHERE invoice_id = ? AND user_id = ?");
    $invoice_data_query->bind_param("ss", $invoice_id, $_SESSION["user_id"]);
    $invoice_data_query->execute();
    $invoice_data_result = $invoice_data_query->get_result();
    $invoice_data = $invoice_data_result->fetch_assoc();

    $transaction_time = $invoice_data["transaction_time"];
    $date = date("jS F Y", strtotime($transaction_time));
    $time = date("H:i:s", strtotime($transaction_time));
    $status = $invoice_data["status"];

    $name = $firstname . " " . $lastname;

    $student_pen = $invoice_data["student_enrollment"];

    $event_id = $invoice_data["event_id"];

    $phone = null;
    $payment_id = null;
    $fee = null;
    $amount_in_words = null;

    $event_data_query = $connection->prepare("SELECT * FROM event WHERE event_id = ?");
    $event_data_query->bind_param("s", $event_id);
    $event_data_query->execute();

    $event_data_result = $event_data_query->get_result();
    $event_data = $event_data_result->fetch_assoc();

    $event_name = $event_data["event_name"];
    $event_date = date("jS F Y", strtotime($event_data["event_start_date"]));
    $event_category = $event_data["event_category"];

    if ($event_data["event_type"] === "Solo") {
      $event_type = "<td style='font-weight: bold'>Event Type</td><td>Solo</td>";
    } else {
      $event_type = "<td style='font-weight: bold'>Event Type</td><td>Team</td>";
    }

    if ($status === "FREE") {
      $email = $data["email"];
      $phone = $data["contact_number"];
      $phone = $phone = "+91 " . substr($data["contact_number"], 0, 5) . " " . substr($data["contact_number"], 5);;
      $invoice_status = "<td class='free'>FREE</td>";

      $amount = 0;

      $fee = 0;

      $participants = $invoice_data["members"];

      $amount_in_words =  "Zero";

      $payment_details_table = "";
    } else {
      $email = $invoice_data["email"];

      $phone = "+91 " . substr(substr($invoice_data["contact"], 3), 0, 5) . " " . substr(substr($invoice_data["contact"], 3), 5);

      $amount = $invoice_data["amount"];

      $fee = $event_data["registration_fee"];

      $participants = $amount / $fee;

      $formatter = new NumberFormatter("en_IN", NumberFormatter::SPELLOUT);
      $amount_in_words =  ucwords($formatter->format($amount));

      $mode = $invoice_data["method"];
      $payment_id = $invoice_data["payment_id"];
      $date_and_time = $date . " " . $time;

      if ($status === "captured") {
        $invoice_status = "<td class='success'>Success</td>";
        $payment_details_table = "<table>
                                            <tr>
                                                <th colspan='2'>Payment Details</th>
                                            </tr>
                                            <tr>
                                                <td>Mode</td>
                                                <td>{$mode}</td>
                                            </tr>
                                            <tr>
                                                <td>Gateway</td>
                                                <td>Razorpay</td>
                                            </tr>
                                            <tr>
                                                <td>Payment ID</td>
                                                <td>{$payment_id}</td>
                                            </tr>
                                            <tr>
                                                <td>Date & Time</td>
                                                <td>{$date_and_time}</td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td class='success'>Success</td>
                                            </tr>
                                        </table>";
      }

      if ($status === "failed") {
        $invoice_status = "<td class='failed'>Failed</td>";
        $reason = $invoice_data["error_description"];
        $payment_details_table = "<table>
                                            <tr>
                                                <th colspan='2'>Payment Details</th>
                                            </tr>
                                            <tr>
                                                <td>Mode</td>
                                                <td>{$mode}</td>
                                            </tr>
                                            <tr>
                                                <td>Gateway</td>
                                                <td>Razorpay</td>
                                            </tr>
                                            <tr>
                                                <td>Payment ID</td>
                                                <td>{$payment_id}</td>
                                            </tr>
                                            <tr>
                                                <td>Date & Time</td>
                                                <td>{$date_and_time}</td>
                                            </tr>
                                            <tr>
                                                <td>Status</td>
                                                <td class='failed'>Failed</td>
                                            </tr>
                                            <tr>
                                                <td>Reason</td>
                                                <td>{$reason}</td>
                                            </tr>
                                        </table>";
      }
    }

    $html = "<!doctype html>
<html>
  <head>
    <meta charset='UTF-8' />
    <title>Invoice</title>
    <style>
      body {
        font-family:
          DejaVu Sans,
          sans-serif;
        font-size: 12px;
        color: #000;
        display: flex;
        justify-content: center;
      }

      .container {
        width: 700px;
        border: 1px solid #000;
        padding: 10px;
      }

      h2,
      h3 {
        margin: 5px 0;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
      }

      th,
      td {
        border: 1px solid #000;
        padding: 6px;
      }

      th {
        background-color: #f2f2f2;
      }

      .no-border td {
        border: none;
      }

      .text-right {
        text-align: right;
      }

      .text-center {
        text-align: center;
      }
      .success {
        color: #059669;
        font-weight: 600;
      }
      .failed {
        color: #dc2626;
        font-weight: 600;
      }
      .free {
        color: #0284c7;
        font-weight: 600;
      }
      
      .refund {
        color: #2563eb;
        font-weight: 600;
      }
    </style>
  </head>

  <body>
    <div class='container'>

      <table class='no-border'>
        <tr>
          <td>
            <h2>R.N.G Patel Institute of Technology</h2>
            <p>College Event Booking System</p>
          </td>
          <td class='text-right'>
            <h2>INVOICE</h2>
          </td>
        </tr>
      </table>

      <table>
        <tr>
          <th colspan='2'>Invoice Details</th>
          <th colspan='2'>Billed To</th>
        </tr>
        <tr>
          <td>Invoice No</td>
          <td>{$invoice_id}</td>
          <td>Name</td>
          <td>{$name}</td>
        </tr>
        <tr>
          <td>Date</td>
          <td>{$date}</td>
          <td>Email</td>
          <td>{$email}</td>
        </tr>
        <tr>
          <td>Time</td>
          <td>{$time}</td>
          <td>Phone</td>
          <td>{$phone}</td>
        </tr>
        <tr>
          <td>Status</td>
          {$invoice_status}
          <td>Student PEN</td>
          <td>{$student_pen}</td>
        </tr>
      </table>

      <table>
        <tr>
          <th colspan='4'>Event Information</th>
        </tr>
        <tr>
          <td style='font-weight: bold'>Event Name</td>
          <td>{$event_name}</td>
          <td style='font-weight: bold'>Event ID</td>
          <td>{$event_id}</td>
        </tr>
        <tr>
          <td style='font-weight: bold'>Event Date</td>
          <td>{$event_date}</td>
          <td style='font-weight: bold'>Event Category</td>
          <td>{$event_category}</td>
        </tr>
        <tr>
          <td style='font-weight: bold'>Registration Date</td>
          <td>{$date}</td>
          {$event_type}
        </tr>
      </table>

      <table>
        <tr>
          <th>Description</th>
          <th>Participant(s)</th>
          <th>Fee</th>
          <th>Amount</th>
        </tr>
        <tr>
          <td>Event Registration Fee</td>
          <td class='text-center'>{$participants}</td>
          <td class='text-center'>₹{$fee}.00</td>
          <td class='text-center'>₹{$amount}.00</td>
        </tr>
      </table>

      <table>
        <tr>
          <td class='text-right'><strong>Subtotal</strong></td>
          <td class='text-right'>₹{$amount}.00</td>
        </tr>
        <tr>
          <td class='text-right'><strong>Total</strong></td>
          <td class='text-right'><strong>₹{$amount}.00</strong></td>
        </tr>
        <tr>
          <td class='text-right'><strong>Amount in Words</strong></td>
          <td class='text-right'><strong>{$amount_in_words} Rupees only</strong></td>
        </tr>
      </table>

        {$payment_details_table}

      <table class='no-border'>
        <tr>
          <td>
            <p><strong>Notes:</strong></p>
            <ul>
              <li>This is a computer-generated invoice.</li>
              <li>Email: cebs.tech.team@gmail.com</li>
              <li>Contact: +91 98765 43210</li>
            </ul>
          </td>
        </tr>
      </table>
    </div>";

    if ($event_data["cancelled"] === "Yes" && $status === "captured") {
      $refund_date = date("jS F Y", strtotime($invoice_data["refund_time"]));
      $refund_time = date("H:i:s", strtotime($invoice_data["refund_time"]));
      $refund_reason = $invoice_data["reason"];
      $refund_id = $invoice_data["refund_id"];

      $html .= "<div class='container'>
      <table class='no-border'>
        <tr>
          <td>
            <h2>R.N.G Patel Institute of Technology</h2>
            <p>College Event Booking System</p>
          </td>
          <td class='text-right'>
            <h2>REFUND</h2>
          </td>
        </tr>
      </table>

      <table>
        <tr>
          <th colspan='2'>Refund Details</th>
          <th colspan='2'>Refund To</th>
        </tr>
        <tr>
          <td>Refund ID</td>
          <td>{$refund_id}</td>
          <td>Name</td>
          <td>{$name}</td>
        </tr>
        <tr>
          <td>Refund Date</td>
          <td>{$refund_date}</td>
          <td>Email</td>
          <td>{$email}</td>
        </tr>
        <tr>
          <td>Refund Time</td>
          <td>{$refund_time}</td>
          <td>Phone</td>
          <td>{$phone}</td>
        </tr>
        <tr>
          <td>Status</td>
          <td class='refund'>Processed</td>
          <td>Student PEN</td>
          <td>{$student_pen}</td>
        </tr>
      </table>

      <table>
        <tr>
          <th colspan='4'>Event Information</th>
        </tr>
        <tr>
          <td><b>Event Name</b></td>
          <td>{$event_name}</td>
          <td><b>Event ID</b></td>
          <td>{$event_id}</td>
        </tr>
        <tr>
          <td><b>Original Payment ID</b></td>
          <td>{$payment_id}</td>
          <td><b>Refund Reason</b></td>
          <td>{$refund_reason}</td>
        </tr>
      </table>

      <table>
        <tr>
          <th>Description</th>
          <th>Amount Paid</th>
          <th>Refund Amount</th>
          <th>Status</th>
        </tr>
        <tr>
          <td>Event Registration Fee</td>
          <td class='text-center'>₹{$amount}.00</td>
          <td class='text-center'>₹{$amount}.00</td>
          <td class='text-center refund'>Refunded</td>
        </tr>
      </table>

      <table>
        <tr>
          <td class='text-right'><strong>Total Refunded</strong></td>
          <td class='text-right'><strong>₹{$amount}.00</strong></td>
        </tr>
        <tr>
          <td class='text-right'><strong>Amount in Words</strong></td>
          <td class='text-right'><strong>{$amount_in_words} Rupees only</strong></td>
        </tr>
      </table>

      <table>
        <tr>
          <th colspan='2'>Refund Transaction</th>
        </tr>
        <tr>
          <td>Mode</td>
          <td>Original Payment Method</td>
        </tr>
        <tr>
          <td>Gateway</td>
          <td>Razorpay</td>
        </tr>
        <tr>
          <td>Refund ID</td>
          <td>{$refund_id}</td>
        </tr>
        <tr>
          <td>Processed On</td>
          <td>{$refund_date}</td>
        </tr>
        <tr>
          <td>Status</td>
          <td class='refund'>Processed</td>
        </tr>
      </table>

      <table class='no-border'>
        <tr>
          <td>
            <p><strong>Notes:</strong></p>
            <ul>
              <li>This is a system-generated refund invoice.</li>
              <li>
                The refunded amount will reflect in your account within 5-7
                working days.
              </li>
              <li>Email: cebs.tech.team@gmail.com</li>
              <li>Contact: +91 98765 43210</li>
            </ul>
          </td>
        </tr>
      </table>
    </div>";
    } else {
      $html .= "";
    }

    $html .= "</body></html>";

    $invoice_name = explode("-", $invoice_id)[2];

    $dompdf = new Dompdf([
      'isRemoteEnabled' => true
    ]);

    setcookie("fileDownload", "true", time() + 60, "/");

    $dompdf->setPaper('A4', 'portrait');

    $dompdf->loadHtml($html);
    $dompdf->render();

    $dompdf->stream($invoice_name, ['Attachment' => true]);
  }
} else {
  header("location:../login.php");
  exit();
}

?>