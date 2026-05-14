<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
    $_SESSION["active_page"] = "ManageEvents"; // Current Page Name

    $user_id = $_SESSION["user_id"];

    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $name = $firstname . " " . $lastname;

    $name_initials = $firstname[0] . $lastname[0];

    if (isset($_GET["event_id"])) {
        $event_id = $_GET["event_id"];

        $query = $connection->prepare("SELECT 
                                e.event_name,
                                e.event_start_date,

                                (SELECT COUNT(*) 
                                 FROM participants p 
                                 WHERE p.event_id = e.event_id) AS participant_count,

                                (SELECT COALESCE(SUM(amount), 0)
                                 FROM payments pay 
                                 WHERE pay.event_id = e.event_id 
                                 AND (pay.status = 'FREE' OR pay.status = 'captured')) AS revenue

                                FROM faculty_coordinators fc
                                JOIN event e ON fc.event_id = e.event_id
                                WHERE e.event_id = ?");

        $query->bind_param("s", $event_id);
        $query->execute();

        $result = $query->get_result();
        $row = $result->fetch_assoc();

        $event_name = $row['event_name'];
        $event_start_date = $row["event_start_date"];
        $participant_count = $row['participant_count'];

        $revenue = $row["revenue"] ?? 0;
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
    <title>Cancel Event – CEBS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../auth.css">
    <link rel="stylesheet" href="../style.css">

    <style>
        .textarea-ctrl {
            width: 100%;
            padding: 12px 14px 12px 40px;
            /* space for icon */
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
            /* allow only vertical resize */
            outline: none;
            transition: all 0.2s ease-in-out;
            background: #fff;
        }

        /* Focus effect */
        .textarea-ctrl:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }

        /* Placeholder styling */
        .textarea-ctrl::placeholder {
            color: #9ca3af;
        }

        /* Optional: match height better */
        .textarea-ctrl {
            min-height: 120px;
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #fff;
            font-size: 18px;
            z-index: 99999;
            display: none;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #fff;
            border-top: 6px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
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
    <!-- 🔹 Top Bar -->
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
                <i class="bi bi-patch-check me-1"></i> Approved by AICTE &nbsp;|&nbsp;
                NAAC Accredited
            </span>
        </div>
    </div>

    <!-- 🔹 Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="Dashboard.php">
                <div class="web-logo">
                    <i class="fa-solid fa-building-columns fa-xl" id="logo-icon"></i>
                </div>
                R.N.G Patel Institute of Technology
            </a>

            <button
                class="navbar-toggler border-0"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu"
                style="color: #fff">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <li class="nav-item">
                        <a class="nav-link" href="Dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown-center dropdown">
                        <a class="nav-link active dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#events">Events</a>
                        <ul class="dropdown-menu" style="border-radius: 14px; padding: 8px; min-width: 180px">
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="AddEvent.php"> <i class="fa-solid fa-calendar-plus"></i>
                                    Add Events</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="UpcomingEvents.php"><i class="fa-solid fa-clock"></i>
                                    Upcoming Events</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="ManageEvents.php"><i class="fa-solid fa-sliders"></i>
                                    Manage Events</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="MyEvents.php"><i class="fa-solid fa-address-card"></i>
                                    My Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ViewParticipants.php">View Participants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ScanQR.php">Scan QR</a>
                    </li>
                    <li class="nav-item ms-lg-2  dropdown-center d-flex justify-content-center">
                        <a
                            class="profile-btn dropdown-toggle"
                            role="button"
                            data-bs-toggle="dropdown"
                            href="#profile">
                            <div class="profile-avatar"><?php echo $name_initials; ?></div>
                            <span style="font-size: 0.85rem; font-weight: 500"><?php echo $name; ?></span>
                        </a>

                        <ul
                            class="dropdown-menu shadow border-0"
                            id="profile-dropdown-menu"
                            style="border-radius: 14px; padding: 8px; min-width: 200px">
                            <li>
                                <a
                                    class="dropdown-item d-flex align-items-center gap-3 py-2"
                                    href="MyProfile.php"
                                    id="edit-profile-opt">
                                    <i class="fa-solid fa-user-pen"></i>
                                    Edit Profile
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item d-flex align-items-center gap-3 py-2 "
                                    href="#"
                                    id="logout-opt">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="reg-card">
        <form action="CancelEvent.php" method="POST" autocomplete="off" onsubmit="return confirmCancel(this)">

            <input type="hidden" name="event_id" value="<?php if (!empty($event_id)) {
                                                            echo $event_id;
                                                        } ?>">

            <input type="hidden" name="event_start_date" value="<?php if (!empty($event_start_date)) {
                                                                    echo date("d F Y", strtotime($event_start_date));
                                                                } ?>">

            <div class="screen active">
                <button type="button" class="back-btn" id="backToLogin" style="font-size: 0.78rem !important;">
                    <i class="bi bi-arrow-left"></i> Back to Manage Events
                </button>

                <h2>Manage Cancellation</h2>
                <p class="form-hint">Review event details carefully before cancelling. This action will notify all participants and process refunds where applicable.</p>
                <div class="form-divider"></div>

                <label class="field-label">Event Name</label>
                <div class="input-wrap">
                    <i class="bi bi-calendar-event icon-left"></i>
                    <input type="text" class="form-ctrl" name="event_name" id="forgotEmail" value="<?php if (!empty($event_name)) {
                                                                                                        echo $event_name;
                                                                                                    } ?>" required readonly>
                </div>

                <label class="field-label">Total Participants</label>
                <div class="input-wrap">
                    <i class="bi bi-people-fill icon-left"></i>
                    <input type="text" class="form-ctrl" name="participant_count" id="forgotEmail" value="<?php if (!empty($participant_count)) {
                                                                                                                echo $participant_count;
                                                                                                            } ?>" required readonly>
                </div>

                <label class="field-label">Total Revenue</label>
                <div class="input-wrap">
                    <i class="bi bi-cash-stack icon-left"></i>
                    <input type="text" class="form-ctrl" name="total_revenue" id="forgotEmail" value="<?php echo $revenue;
                                                                                                        ?>" required readonly>
                </div>

                <label class="field-label">Cancellation Reason</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope icon-left"></i>
                    <textarea class="form-ctrl textarea-ctrl" name="reason" rows="5" placeholder="Enter the reason for cancelling this event..." required></textarea>
                </div>

                <button class="btn-submit" style="background: linear-gradient(135deg, #f43f5e, #e11d48);" name="submit" type="submit" id="submitBtn" onclick="return confirmCancel()"><i class='fa-solid fa-xmark'></i> Cancel Event</button>
            </div>
    </div>
    </form>

    <div id="loadingOverlay">
        <div class="loader"></div>
        <p>The event has been cancelled. Processing refund...</p>
        <small>⚠️ Please do not refresh or close this page.</small>
    </div>

    <?php include "../footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Loader on Form Submit
        $(document).on("submit", "form", function(e) {
            const clickedButton = e.originalEvent.submitter;

            // 👉 Run ONLY for Excel & PDF buttons
            if (!clickedButton || (clickedButton.name !== "submit")) {
                return; // skip loader
            }

            const overlay = document.getElementById("loadingOverlay");

            overlay.style.display = "flex";

            // disable buttons to prevent double click
            const submitButtons = document.querySelectorAll("button[type='submit']");
            setTimeout(() => {
                submitButtons.forEach(btn => btn.disabled = true);
            }, 10);

        });

        // Back Button
        let backToLogin = document.getElementById("backToLogin");
        backToLogin.addEventListener("click", function() {
            window.location = "ManageEvents.php";
        });

        function confirmCancel(form) {
            if (!form.checkValidity()) {
                return true;
            }
            return confirm("Are you sure you want to cancel this event?");
        }
    </script>
</body>

</html>