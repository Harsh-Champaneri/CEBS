<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {

    // Updating the participants data
    if (isset($_POST["accept"])) {
        $qrcode_data_accept = $_POST["qrcode_data_accept"];

        $attended = "Yes";
        $scanned_time = date("Y-m-d H:i:s");

        // Updating the participants data in participants Table
        $participants_data_update_query = $connection->prepare("UPDATE participants SET attended = ?, scanned_by = ?, scanned_time = ? WHERE qrcode_data = ?");
        $participants_data_update_query->bind_param("ssss", $attended, $_SESSION["user_id"], $scanned_time, $qrcode_data_accept);
        $participants_data_update_query->execute();

        if ($participants_data_update_query->affected_rows === 1) {
            header("location:ScanQR.php?message=Successful.");
            exit();
        }
    }

    // Validating the QR Code and Fetching the data
    if (isset($_POST["qr_data"])) {
        // Fetching the event_id of faculty coordinator
        $faculty_coordinator_events_query = $connection->prepare("SELECT event_id FROM faculty_coordinators WHERE user_id = ?");
        $faculty_coordinator_events_query->bind_param("s", $_SESSION["user_id"]);
        $faculty_coordinator_events_query->execute();

        $faculty_coordinator_events = $faculty_coordinator_events_query->get_result()->fetch_all(MYSQLI_ASSOC);
        $event_ids = array_column($faculty_coordinator_events, "event_id"); // event_id of faculty coordinator

        $qrcode_data = $_POST["qr_data"];

        $participants_event_details_query = $connection->prepare("SELECT participants.event_id, participants.email, participants.student_enrollment, participants.attended, users.firstname, users.lastname, users.contact_number, users.department, event.event_name, event.event_start_date, event.event_start_time, event.event_end_date, event.event_end_time,event.event_venue, event.event_category, event.cancelled FROM users Inner JOIN participants ON users.email = participants.email INNER JOIN event on participants.event_id = event.event_id WHERE participants.qrcode_data = ?");
        $participants_event_details_query->bind_param("s", $qrcode_data);
        $participants_event_details_query->execute();

        $participants_event_details_result = $participants_event_details_query->get_result();

        if ($participants_event_details_result->num_rows === 1) {   // Unique Row
            $participants_event_details = $participants_event_details_result->fetch_assoc();

            if ($participants_event_details["cancelled"] === "Yes") {
                header("location:ScanQR.php?message=Event Cancelled.");
                exit();
            }

            if ($participants_event_details["attended"] === "No") { // Already attented or not

                if (in_array($participants_event_details["event_id"], $event_ids)) {    // Checking the coordinator

                    $currentDateTime = date("Y-m-d H:i:s"); // System current date and time
                    $eventDateTimeStart = $participants_event_details["event_start_date"] . ' ' . $participants_event_details["event_start_time"];   // Event start date and time
                    $eventDateTimeEnd = $participants_event_details["event_end_date"] . ' ' . $participants_event_details["event_end_time"];   // Event end date and time

                    if (strtotime($currentDateTime) < strtotime($eventDateTimeStart)) {  // Upcoming
                        header("location:ScanQR.php?message=Event not started yet.");
                        exit();
                    }

                    if (strtotime($currentDateTime) > strtotime($eventDateTimeEnd)) {  // Completed
                        header("location:ScanQR.php?message=Event Completed.");
                        exit();
                    }

                    // Participant's Details
                    $student_name = $participants_event_details["firstname"] . " " . $participants_event_details["lastname"];
                    $enrollment_number = $participants_event_details["student_enrollment"];
                    $department = $participants_event_details["department"];
                    $contact = $participants_event_details["contact_number"];
                    $email = $participants_event_details["email"];

                    // Event Details
                    $event_name = $participants_event_details["event_name"];
                    $event_date = $participants_event_details["event_start_date"];
                    $time = $participants_event_details["event_start_time"];
                    $venue = $participants_event_details["event_venue"];
                    $category = $participants_event_details["event_category"];

                    // Faculty Coordinator Details
                    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
                    $stmt->bind_param("s", $_SESSION["user_id"]);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $data = $result->fetch_assoc();

                    $firstname = $data["firstname"];
                    $lastname = $data["lastname"];
                    $faculty_coordinator = $firstname . " " . $lastname;
                } else {
                    header("location:ScanQR.php?message=Not Allowed.");
                    exit();
                }
            } else {
                header("location:ScanQR.php?message=QR Code Already Scanned.");
                exit();
            }
        } else {
            header("location:ScanQR.php?message=Invalid QR Code.");
            exit();
        }
    } else {
        header("location:ScanQR.php?message=Invalid QR Code.");
        exit();
    }
} else {
    header("location:../login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Participants – CEBS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../auth.css">
    <link rel="stylesheet" href="../style.css">

    <style>
        /* Payment Confirmation Page Styles */
        .payment-container {
            padding: 60px 20px;
            min-height: 100vh;
            position: relative;
        }

        .payment-card {
            max-width: 75vw;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 24px;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .payment-header {
            background: linear-gradient(135deg,
                    rgba(14, 165, 233, 0.1),
                    rgba(56, 189, 248, 0.05));
            padding: 32px;
            border-bottom: 2px solid rgba(14, 165, 233, 0.15);
            text-align: center;
        }

        .payment-header h2 {
            font-family: "Playfair Display", serif;
            font-weight: 700;
            color: #1e293b;
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .payment-header p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 12px;
        }

        .security-badge i {
            color: #10b981;
        }

        .payment-body {
            padding: 32px;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #0ea5e9;
            font-size: 1rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(14, 165, 233, 0.15);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(14, 165, 233, 0.1);
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .info-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-label i {
            color: #0ea5e9;
            font-size: 0.85rem;
        }

        .info-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: right;
        }

        .event-name {
            font-size: 1.1rem;
            color: #0ea5e9;
            font-weight: 700;
        }

        .participants-section {
            background: rgba(191, 219, 254, 0.2);
            border: 1px solid rgba(14, 165, 233, 0.2);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .participant-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(14, 165, 233, 0.1);
        }

        .participant-item:last-child {
            margin-bottom: 0;
        }

        .participant-name {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1e293b;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .participant-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .participant-amount {
            color: #1e293b;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .team-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg,
                    rgba(244, 63, 94, 0.15),
                    rgba(251, 113, 133, 0.08));
            color: #be123c;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(244, 63, 94, 0.25);
        }

        .pricing-breakdown {
            background: rgba(255, 255, 255, 0.6);
            border: 2px solid rgba(14, 165, 233, 0.2);
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .price-label {
            color: #64748b;
        }

        .price-value {
            color: #1e293b;
            font-weight: 600;
        }

        .price-divider {
            height: 1px;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(14, 165, 233, 0.3),
                    transparent);
            margin: 12px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: linear-gradient(135deg,
                    rgba(14, 165, 233, 0.1),
                    rgba(56, 189, 248, 0.05));
            border-radius: 12px;
            margin-top: 12px;
        }

        .total-label {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .total-amount {
            font-family: "Playfair Display", serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0ea5e9;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-pay {
            flex: 1;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-family: "DM Sans", sans-serif;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(16, 185, 129, 0.4);
            transition:
                transform 0.2s,
                box-shadow 0.2s;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.5);
        }

        .btn-pay i {
            font-size: 1.1rem;
        }

        .btn-cancel {
            flex: 1;
            background: rgba(255, 255, 255, 0.8);
            color: #64748b;
            border: 2px solid rgba(14, 165, 233, 0.2);
            border-radius: 12px;
            padding: 16px;
            font-family: "DM Sans", sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        .terms-notice {
            text-align: center;
            margin-top: 20px;
            padding: 16px;
            background: rgba(251, 146, 60, 0.08);
            border-radius: 10px;
            border: 1px solid rgba(251, 146, 60, 0.2);
        }

        .terms-notice p {
            margin: 0;
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.6;
        }

        .terms-notice a {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
        }

        .terms-notice a:hover {
            color: #0284c7;
        }

        .payment-secure {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
            padding: 12px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 10px;
            font-size: 0.8rem;
            color: #64748b;
            border: 1px solid rgba(16, 185, 129, 0.1);
        }

        .payment-secure img {
            height: 20px;
            opacity: 0.7;
        }

        .general-details {
            width: 30vw;
        }

        .payment-details {
            width: 30vw;
        }

        .payment-body {
            display: flex;
            flex-direction: row;
            justify-content: space-evenly;
        }

        .section-divider {
            width: 1px;
            background: rgba(14, 165, 233, 0.35);
            margin: 0 25px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .payment-container {
                padding: 40px 16px;
            }

            .payment-card {
                border-radius: 18px;
            }

            .payment-header {
                padding: 24px 20px;
            }

            .payment-header h2 {
                font-size: 1.5rem;
            }

            .payment-body {
                padding: 24px 20px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .info-value {
                text-align: left;
            }

            .participant-item {
                padding: 10px 12px;
            }

            .participant-name {
                font-size: 0.85rem;
            }

            .participant-icon {
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .total-amount {
                font-size: 1.5rem;
            }
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 9999;
            color: #fff;
            font-size: 18px;
            display: none;
        }

        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- 📹 Top Bar -->
    <div class="top-bar py-2">
        <div
            class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <i class="bi bi-envelope me-1"></i>
                <a href="#">cebs.tech.team@gmail.com</a>
                <span class="mx-2 opacity-50">|</span>
                <i class="bi bi-telephone me-1"></i> +91 98765 43210
            </span>
            <span class="badge-accred">
                <i class="bi bi-patch-check me-1"></i>
                Approved by AICTE &nbsp;|&nbsp; NAAC Accredited
            </span>
        </div>
    </div>

    <!-- 📹 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="">
                <div class="web-logo"><i class="fa-solid fa-building-columns fa-xl" id="logo-icon"></i></div>
                R.N.G Patel Institute of Technology
            </a>

            <button
                class="navbar-toggler border-0"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu"
                style="color: #fff">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="payment-container">
        <div class="payment-card">
            <!-- Header -->
            <div class="payment-header">
                <h2><i class="bi bi-shield-check me-2"></i>Verify Participants</h2>
                <p>Review participant and event details before proceeding to allow the entry</p>

            </div>

            <!-- Body -->
            <div class="payment-body">
                <div class="general-details">
                    <!-- Event Details Section -->
                    <div class="section-title">
                        <i class="bi bi-person"></i>
                        Participant Details
                    </div>

                    <div class="info-card">
                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-person icon-left"></i>
                                Student Name
                            </div>
                            <div class="info-value event-name"><?php echo $student_name; ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-person-badge"></i>
                                Enrollment Number
                            </div>
                            <div class="info-value"><?php echo $enrollment_number; ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">
                                <i class="fa-solid fa-user-graduate icon-left"></i>
                                Department
                            </div>
                            <div class="info-value"><?php echo $department; ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-telephone icon-left"></i>
                                Contact
                            </div>
                            <div class="info-value"><?php echo $contact; ?></div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">
                                <i class="bi bi-envelope icon-left"></i>
                                Email
                            </div>
                            <div class="info-value"><?php echo $email; ?></div>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="bi bi-person-fill"></i>
                        Faculty Coordinator
                    </div>

                    <div class="participants-section">
                        <div class="participant-item">
                            <div class="participant-name">
                                <div class="participant-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span><?php echo $faculty_coordinator; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <div class="payment-details">
                    <div class="general-details">
                        <!-- Event Details Section -->
                        <div class="section-title">
                            <i class="bi bi-calendar-event"></i>
                            Event Details
                        </div>

                        <div class="info-card">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-bookmark-fill"></i>
                                    Event Name
                                </div>
                                <div class="info-value event-name"><?php echo $event_name; ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-calendar3"></i>
                                    Event Date
                                </div>
                                <div class="info-value"><?php echo $event_date; ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-clock"></i>
                                    Time
                                </div>
                                <div class="info-value"><?php echo $time; ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    Venue
                                </div>
                                <div class="info-value"><?php echo $venue; ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">
                                    <i class="bi bi-tags-fill"></i>
                                    Category
                                </div>
                                <div class="info-value"><?php echo $category; ?></div>
                            </div>
                        </div>
                    </div>

                    <form action="" method="post">
                        <div class="action-buttons">
                            <input type="hidden" name="qrcode_data_accept" value="<?php echo $qrcode_data; ?>">
                            <button type="submit" class="btn-pay" id="accept" name="accept">
                                <i class="fa-solid fa-check fa-xs"></i>
                                Accecpt
                            </button>
                            <button type="button" class="btn-cancel" onclick="cancelTransaction()">
                                <i class="bi bi-arrow-left"></i>
                                Back
                            </button>
                        </div>
                    </form>

                    <div class="terms-notice">
                        <p>
                            <i class="bi bi-info-circle me-1"></i>
                            By proceeding, you agree to our
                            <a href="#">Terms & Conditions</a> and
                            <a href="#">Refund Policy</a>. All payments are processed
                            securely through Razorpay.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../footer.php"; ?>

    <script>
        function cancelTransaction() {
            if (confirm("Are you sure you want to go back?")) {
                window.history.back();
            }
        }
    </script>
</body>

</html>