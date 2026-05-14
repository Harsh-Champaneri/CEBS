<?php

session_start();
include "../connection.php";

require '../vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$keyId = "";
$keySecret = "";

$payment_verified = false;

if (isset($_POST["razorpay_payment_id"])) {
  $razorpay_payment_id = $_POST["razorpay_payment_id"];
  $razorpay_order_id = $_POST["razorpay_order_id"] ?? null;
  $razorpay_signature = $_POST["razorpay_signature"] ?? null;

  // Check order ID exists
  if (!$razorpay_order_id || $razorpay_order_id != $_SESSION["razorpay_order_id"]) {
    die("Order ID mismatch or missing");
  }

  // Restore registration data
  if (!isset($_SESSION["registration_data"])) {
    die("Session expired");
  }

  $_POST = $_SESSION["registration_data"];
  $user_id = $_SESSION["user_id"];
  $event_id = $_POST["event_id"];

  // Generate invoice ID
  $invoice_id = "INV-" . explode("-", $event_id)[1] . "-" . date("dmYHis") . "-" . substr($user_id, -4);

  try {
    $api = new Api($keyId, $keySecret);

    // Verify payment signature
    if ($razorpay_payment_id && $razorpay_signature) {
      // Only verify signature if available (successful payment)
      $api->utility->verifyPaymentSignature([
        'razorpay_order_id' => $razorpay_order_id,
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_signature' => $razorpay_signature
      ]);
    }

    // Fetch payment details
    $payment = $api->payment->fetch($razorpay_payment_id);

    $order_id = $payment->order_id;
    $payment_id = $payment->id;
    $amount = $payment->amount / 100; // Convert paisa to rupees
    $currency = $payment->currency;
    $method = $payment->method;
    $email = $payment->email;
    $contact = $payment->contact;
    $status = $payment->status; // captured, failed, etc.
    $transaction_time = date("Y-m-d H:i:s", $payment->created_at);

    // Default error info
    $error_code = null;
    $error_description = null;
    $error_reason = null;

    if ($status !== 'captured') {
      // Failed payment info
      $error_code = $payment->error_code ?? 'UNKNOWN';
      $error_description = $payment->error_description ?? 'Payment failed';
      $error_reason = $payment->error_reason ?? 'Check Razorpay dashboard';
    }

    if ($_POST["event_type"] === "Solo") {
      $members = 1;
      $student_enrollment = $_POST["student_enrollment"];
    }

    if ($_POST["event_type"] === "Team") {
      $members = $_POST["team_members"];
      $student_enrollment = $_POST["student_enrollment"][0];
    }

    // Insert payment into database
    $query = "INSERT INTO payments(
                invoice_id,
                user_id,
                student_enrollment,
                event_id,
                members,
                order_id,
                payment_id,
                signature,
                amount,
                currency,
                method,
                email,
                contact,
                status,
                error_code,
                error_description,
                error_reason,
                transaction_time
            ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $connection->prepare($query);
    $stmt->bind_param(
      "ssssisssisssssssss",
      $invoice_id,
      $user_id,
      $student_enrollment,
      $event_id,
      $members,
      $order_id,
      $payment_id,
      $razorpay_signature,
      $amount,
      $currency,
      $method,
      $email,
      $contact,
      $status,
      $error_code,
      $error_description,
      $error_reason,
      $transaction_time
    );
    $stmt->execute();

    // Clean up session
    unset($_SESSION["registration_data"]);
    unset($_SESSION["razorpay_order_id"]);

    // Redirect based on status
    if ($status === 'captured') {
      $payment_verified = true;
    } else {
      header("location:AllEvents.php?message=Payment Failed");
      exit();
    }
  } catch (SignatureVerificationError $e) {
    // Signature verification failed
    die("Payment verification failed: " . $e->getMessage());
  } catch (Exception $e) {
    // Other errors
    die("Error: " . $e->getMessage());
  }
} else if (isset($_POST["register"])) {
  $payment_verified = true;
  // Generate invoice ID
  $invoice_id = "INV-" . explode("-", $_POST["event_id"])[1] . "-" . date("dmYHis") . "-" . substr($_SESSION["user_id"], -4);
  $status = "FREE";
  $amount = 0;
  $transaction_time = date("Y-m-d H:i:s");

  if ($_POST["event_type"] === "Solo") {
    $members = 1;
    $student_enrollment = $_POST["student_enrollment"];
    $book_email = $_POST["email"];
  }

  if ($_POST["event_type"] === "Team") {
    $members = $_POST["team_members"];
    $student_enrollment = $_POST["student_enrollment"][0];
    $book_email = $_POST["student_email"][0];
  }

  $stmt = $connection->prepare("INSERT INTO payments(invoice_id,user_id,student_enrollment,event_id,members,amount,email,contact,status,transaction_time) VALUES(?,?,?,?,?,?,?,?,?,?)");
  $stmt->bind_param("ssssiissss", $invoice_id, $_SESSION["user_id"], $student_enrollment, $_POST["event_id"], $members, $amount, $book_email, $_POST["contact"], $status, $transaction_time);
  $stmt->execute();
} else {
  die("Payment ID missing");
}

require_once "../phpqrcode/qrlib.php";
require '../vendor/autoload.php';
require '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use Cloudinary\Api\Upload\UploadApi;

// For Uploading QR Code to Cloudinary
function uploadQRCode($folder, $public_id, $qrcode_data)
{
  // Capture QR output in memory
  ob_start();
  QRcode::png($qrcode_data, null, "H", 10, 2);
  $imageString = ob_get_contents();
  ob_end_clean();

  // Convert to base64
  $base64 = 'data:image/png;base64,' . base64_encode($imageString);

  // Upload to Cloudinary
  $upload = (new UploadApi())->upload($base64, [
    "folder" => $folder,
    'public_id' => $public_id,
  ]);

  return [
    "url" => $upload['secure_url'], // URL of the QR Code
    "public_id" => $upload['public_id'],    // Public Id of the QR Code
  ];
}

// For Sending Email
function sendEmail($email, $user_name, $body, $subject)
{
  try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    $mail->Username   = '';

    $mail->Password   = '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    //Recipients
    $mail->setFrom("", "");
    $mail->addAddress($email, $user_name);

    //Content
    $mail->isHTML(true);

    $mail->Subject = $subject;
    $mail->Body    = $body;

    return $mail->send();
  } catch (Exception $e) {
    // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    return false;
  }
}

// For getting the name of the user
function getName($connection, $email)
{
  $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  $firstname = $data["firstname"];
  $lastname = $data["lastname"];
  $name = $firstname . " " . $lastname;

  return $name;
}

// For generating the QR Code token/data
function generateToken($lenght = 8)
{
  return bin2hex(random_bytes($lenght));
}

if (isset($_SESSION["user_id"])) {
  // if (isset($_POST["register"])) {
  if ($payment_verified) {
    $event_id = $_POST["event_id"];
    $event_type = $_POST["event_type"];

    // Solo Event
    if ($event_type === "Solo") {
      $email = $_POST["email"];
      $student_enrollment = $_POST["student_enrollment"];

      $qrcode_data = generateToken();
      $qrcode = uploadQRCode($event_id, $email, $qrcode_data);

      $qrcode_url = $qrcode["url"];
      $qrcode_public_id = $qrcode["public_id"];

      $insert_data = $connection->prepare("INSERT INTO participants(email,event_id,event_type,student_enrollment,qrcode_data,qrcode_url,qrcode_public_id) VALUES(?,?,?,?,?,?,?)");
      $insert_data->bind_param("sssssss", $email, $event_id, $event_type, $student_enrollment, $qrcode_data, $qrcode_url, $qrcode_public_id);
      $insert_data->execute();

      $name = getName($connection, $email);

      $team_section = '<tr style="background: #ffffff">
                    <td
                      style="
                        padding: 11px 14px;
                        border-bottom: 1px solid rgba(14, 165, 233, 0.15);
                      "
                    >
                      <span
                        style="
                          font-size: 12px;
                          font-weight: 700;
                          color: #64748b;
                          text-transform: uppercase;
                          letter-spacing: 0.8px;
                        "
                        >👥 Event Type</span
                      >
                    </td>
                    <td
                      style="
                        padding: 11px 14px;
                        border-bottom: 1px solid rgba(14, 165, 233, 0.15);
                      "
                    >
                      <span style="font-size: 13px; color: #475569"
                        >Solo</span
                      >
                    </td>
                  </tr>';

      $body = file_get_contents("QRTemplate.html");
      $body = str_replace(['{{MEMBER_NAME}}', '{{EVENT_NAME}}', '{{EVENT_NAME}}', '{{TEAM_SECTION}}', '{{MEMBER_NAME}}', '{{EVENT_DATE}}', '{{EVENT_VENUE}}', '{{QR_CODE}}'], [$name, $_POST["event_name"], $_POST["event_name"], $team_section, $name, date("jS F Y", strtotime($_POST["event_start_date"])), $_POST["event_venue"], $qrcode_url], $body);

      $subject = "Your Tickets for " . $_POST["event_name"];

      if (sendEmail($email, $name, $body, $subject)) {
        header("location:MyEvents.php?message=Registration Successful!");
        exit();
      }
    }

    // Team Event
    if ($event_type === "Team") {
      $team_members = $_POST["team_members"];

      $stmt = $connection->prepare("SELECT COUNT(*) + 1 AS team_no FROM participants WHERE event_id = ?");
      $stmt->bind_param("s", $event_id);
      $stmt->execute();
      $result = $stmt->get_result()->fetch_assoc()["team_no"];

      $team_name = $_POST["team_name"];
      $student_email_array = $_POST["student_email"];
      $student_enrollment_array = $_POST["student_enrollment"];

      $parts = explode("-", $event_id);
      $event_code = "EV" . end($parts);
      $team_id = $event_code . "-T" . $result;

      $team_section = '<tr style="background: #ffffff">
                    <td
                      style="
                        padding: 11px 14px;
                        border-bottom: 1px solid rgba(14, 165, 233, 0.15);
                      "
                    >
                      <span
                        style="
                          font-size: 12px;
                          font-weight: 700;
                          color: #64748b;
                          text-transform: uppercase;
                          letter-spacing: 0.8px;
                        "
                        >👥 Team</span
                      >
                    </td>
                    <td
                      style="
                        padding: 11px 14px;
                        border-bottom: 1px solid rgba(14, 165, 233, 0.15);
                      "
                    >
                      <span style="font-size: 13px; color: #475569"
                        >' . $team_name . '</span
                      >
                    </td>
                  </tr>';

      $insert_data = $connection->prepare("INSERT INTO participants(email,event_id,event_type,student_enrollment,team_name,team_id,team_members,qrcode_data,qrcode_url,qrcode_public_id) VALUES(?,?,?,?,?,?,?,?,?,?)");
      for ($i = 0; $i < count($student_email_array); $i++) {
        $student_enrollment = $student_enrollment_array[$i];
        $student_email = $student_email_array[$i];

        $qrcode_data = generateToken();
        $qrcode = uploadQRCode($event_id, $student_email, $qrcode_data);

        $qrcode_url = $qrcode["url"];
        $qrcode_public_id = $qrcode["public_id"];

        $insert_data->bind_param("ssssssisss", $student_email, $event_id, $event_type, $student_enrollment, $team_name, $team_id, $team_members, $qrcode_data, $qrcode_url, $qrcode_public_id);
        $insert_data->execute();

        $name = getName($connection, $student_email);

        $body = file_get_contents("QRTemplate.html");
        $body = str_replace(['{{MEMBER_NAME}}', '{{EVENT_NAME}}', '{{EVENT_NAME}}', '{{TEAM_SECTION}}', '{{MEMBER_NAME}}', '{{EVENT_DATE}}', '{{EVENT_VENUE}}', '{{QR_CODE}}'], [$name, $_POST["event_name"], $_POST["event_name"], $team_section, $name, date("jS F Y", strtotime($_POST["event_start_date"])), $_POST["event_venue"], $qrcode_url], $body);

        $subject = "Your Tickets for " . $_POST["event_name"];

        sendEmail($student_email, $name, $body, $subject);
      }
      header("location:MyEvents.php?message=Registration Successful!");
      exit();
    }
  }
} else {
  header("location:../login.php");
  exit();
}
