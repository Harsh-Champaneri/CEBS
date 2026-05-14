<?php

include "../connection.php";
session_start();

require('../vendor/autoload.php');

use Razorpay\Api\Api;

$keyId = "";
$keySecret = "";

$api = new Api($keyId, $keySecret);

function normalizePhone($phone)
{
    $phone = trim($phone);

    // Remove spaces, +, -
    $phone = str_replace([" ", "+", "-"], "", $phone);

    // Remove country code if already exists
    if (substr($phone, 0, 2) == "91" && strlen($phone) > 10) {
        $phone = substr($phone, 2);
    }

    return $phone;
}

/* =========================
   FUNCTION: Send WhatsApp
========================= */
function sendWhatsApp($phone, $message)
{
    $instance_id = "";
    $token = "";

    $url = "";

    $data = [
        "token" => $token,
        "to" => $phone,
        "body" => $message
    ];

    $options = [
        "http" => [
            "header"  => "Content-type: application/x-www-form-urlencoded",
            "method"  => "POST",
            "content" => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "❌ Failed to send message to $phone<br>";
    } else {
        echo "✅ Message sent to $phone<br>";
    }
}

/* ========================= 
   GET POST DATA
========================= */
$event_id = $_POST["event_id"];
$event_name = $_POST["event_name"];
$event_start_date = $_POST["event_start_date"];
$reason = $_POST["reason"];

/* =========================
   STEP 1: REFUND USERS
========================= */
$refund_query = $connection->prepare("SELECT * FROM payments 
    INNER JOIN users ON payments.user_id = users.user_id 
    WHERE event_id = ?
");

$refund_query->bind_param("s", $event_id);
$refund_query->execute();
$refund_result = $refund_query->get_result();

$refund_contacts = [];

while ($refund = $refund_result->fetch_assoc()) {

    $name = $refund["firstname"] . " " . $refund["lastname"];
    $contact = normalizePhone($refund["contact"]);
    $payment_status = trim(strtolower($refund["status"]));

    $payment_id = $refund["payment_id"];

    try {
        $refundData = $api->payment->fetch($payment_id)->refund();

        $refund_id = $refundData->id;
        $refund_status = $refundData->status;
        $refund_time = date("Y-m-d H:i:s", $refundData->created_at);

        $update_payment_query = $connection->prepare("UPDATE payments SET refund_id = ?, refund_status = ?, refund_time = ?, reason = ? WHERE payment_id = ?");
        $update_payment_query->bind_param("sssss", $refund_id, $refund_status, $refund_time, $reason, $payment_id);
        $update_payment_query->execute();
    } catch (Exception $e) {
        echo "Refund Failed: " . $e->getMessage();
    }

    // Remove +91 if exists
    if (substr($contact, 0, 2) == "91") {
        $contact = substr($contact, 2);
    }

    $phone = "91" . $contact;
    $refund_contacts[] = $contact;

    if ($payment_status == "captured") {
        $amount = $refund["amount"];

        $message = "Dear *{$name}*,

Your event *\"{$event_name}\"* scheduled on *{$event_start_date}* has been *CANCELLED* due to *{$reason}*.

Your registration fee of ₹{$amount} has been initiated and will be refunded within 5-7 working days.

We apologize for the inconvenience caused and thank you for your understanding.

Regards,
CEBS Team

📞 Need Help?
Phone: +91 98765 43210
Email: cebs.tech.team@gmail.com
";
    } else {

        $message = "Dear *{$name}*,

Your event *\"{$event_name}\"* scheduled on *{$event_start_date}* has been *CANCELLED* due to *{$reason}*.

We apologize for the inconvenience caused and thank you for your understanding.

Regards,
CEBS Team

📞 Need Help?
Phone: +91 98765 43210
Email: cebs.tech.team@gmail.com
";
    }

    sendWhatsApp($phone, $message);
}

/* Remove duplicates */
$refund_contacts = array_unique($refund_contacts);

/* =========================
   STEP 2: ALL PARTICIPANTS
========================= */
$participants_query = $connection->prepare("SELECT * FROM participants 
    INNER JOIN users ON participants.email = users.email 
    WHERE event_id = ?
");

$participants_query->bind_param("s", $event_id);
$participants_query->execute();
$participants_result = $participants_query->get_result();

/* Store contact => name */
$participants = [];

while ($row = $participants_result->fetch_assoc()) {
    $contact = trim($row["contact_number"]);
    $name = $row["firstname"] . " " . $row["lastname"];

    $participants[$contact] = $name;
}

/* =========================
   STEP 3: SEND TO NON-REFUND USERS
========================= */
foreach ($participants as $contact => $name) {

    if (!in_array($contact, $refund_contacts)) {

        $phone = "91" . $contact;

        $message = "Dear *{$name}*,

Your event *\"{$event_name}\"* scheduled on *{$event_start_date}* has been *CANCELLED* due to *{$reason}*.

We apologize for the inconvenience caused and thank you for your understanding.

Regards,
CEBS Team

📞 Need Help?
Phone: +91 98765 43210
Email: cebs.tech.team@gmail.com
";

        sendWhatsApp($phone, $message);
    }
}

// /* =========================
//    STEP 4: UPDATE EVENT
// ========================= */
$update_event_query = $connection->prepare("UPDATE event SET cancelled = 'Yes', cancelled_by = ?, reason = ? WHERE event_id = ?");

$update_event_query->bind_param("sss", $_SESSION["user_id"], $reason, $event_id);

if ($update_event_query->execute()) {
    header("location:ManageEvents.php?successMsg=Event Cancelled Successfully!");
    exit();
}

?>