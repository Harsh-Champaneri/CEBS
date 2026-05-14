<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $_SESSION["user_id"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $email = $data["email"];
    $contact_number = $data["contact_number"];

    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $name = $firstname . " " . $lastname;

    $name_initials = $firstname[0] . $lastname[0];

    // Checking wheather the user has already registered or not
    $check_participants_stmt = $connection->prepare("SELECT * FROM participants WHERE email = ? AND event_id = ?");
    $check_participants_stmt->bind_param("ss", $email, $_GET["event_id"]);
    $check_participants_stmt->execute();
    $check_participants_result = $check_participants_stmt->get_result();

    if ($check_participants_result->num_rows > 0) {
        header("location:" . $_SESSION['active_page'] . ".php?message=Already Registered in this Event.");
        exit();
    }

    if (isset($_GET["event_id"])) {
        if (isset($_SESSION["active_page"])) {
            $back_page = $_SESSION["active_page"];

            if ($_SESSION["active_page"] === "AllEvents" || $_SESSION["active_page"] === "UpcomingEvents" || $_SESSION["active_page"] === "MyEvents") {
                $page = explode("E", $back_page)[0] . " E" . explode("E", $back_page)[1];
            }
        }

        // Event Data from event table
        $event_data = $connection->prepare("SELECT * FROM event WHERE event_id = ?");
        $event_data->bind_param("s", $_GET["event_id"]);
        $event_data->execute();
        $event_data_result = $event_data->get_result();
        $event = $event_data_result->fetch_assoc();

        $event_id = $event["event_id"];

        $event_name = $event["event_name"];
        $event_venue = $event["event_venue"];
        $event_type = $event["event_type"];
        $maximum_participants = $event["maximum_participants"];
        $event_start_date = $event["event_start_date"];
        $event_start_time = $event["event_start_time"];
        $event_end_date = $event["event_end_date"];
        $event_end_time = $event["event_end_time"];
        $event_category = $event["event_category"];
        $registration_start_date = $event["registration_start_date"];
        $registration_start_time = $event["registration_start_time"];
        $registration_end_date = $event["registration_end_date"];
        $registration_end_time = $event["registration_end_time"];
        $registration_fee = $event["registration_fee"];
        $organized_by = $event["organized_by"];
    }
} else {
    header("location:../login.php");
    exit();
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Book Event – CEBS</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet" />
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <!-- Sky Blush Theme -->
    <link rel="stylesheet" href="../style.css" />
    <link rel="stylesheet" href="../auth.css" />

    <style>
        .reg-card {
            max-width: 900px !important;
        }

        .step-actions {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 10px;
        }

        .student-block {
            padding: 20px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(14, 165, 233, 0.15);
            margin-bottom: 18px;
        }

        select:disabled {
            background-color: rgba(255, 255, 255, 0.8) !important;
            opacity: 1;
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
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#events">Events</a>
                        <ul class="dropdown-menu" style="border-radius: 14px; padding: 8px; min-width: 180px">
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2" href="AllEvents.php"> <i class="fa-solid fa-calendar-days"></i>
                                    All Events</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2" href="UpcomingEvents.php"><i class="fa-solid fa-clock"></i>
                                    Upcoming Events</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-3 py-2" href="MyEvents.php"><i class="fa-solid fa-id-badge"></i>
                                    My Events</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="MyTransactions.php">My Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="MyQRCodes.php">My QR Codes</a>
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

    <!-- FORM CODE BEGINS HERE -->
    <div class="reg-card">
        <form id="eventForm" method="post" action="BookEventData.php">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <!-- ===================================================== -->
            <!-- STEP 1 – EVENT GENERAL INFORMATION -->
            <!-- ===================================================== -->
            <div class="screen active" id="step1">
                <div style="display: flex; flex-direction: row; justify-content: space-between;">
                    <div>
                        <h2>Event General Information</h2>
                        <p class="form-hint">Provide complete details about the event.</p>
                    </div>
                    <div style="align-self: flex-start;">
                        <button type="button" id="backButton" class="back-btn" style="font-size: 0.9rem;">
                            <?php if (isset($_GET["event_id"])): ?>
                                <i class="bi bi-arrow-left"></i> Back to <?php echo $page; ?>
                            <?php endif; ?>
                        </button>
                    </div>
                </div>
                <div class="form-divider"></div>

                <!-- Event Name -->
                <label class="field-label">Event Name</label>
                <div class="input-wrap">
                    <i class="bi bi-calendar-event icon-left"></i>
                    <input type="text" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                    echo $event_name;
                                                                                } ?>" name="event_name" id="event_name" placeholder="Event Name" required readonly>
                </div>

                <!-- Event Venue -->
                <div>
                    <label class="field-label">Event Venue</label>
                    <div class="input-wrap">
                        <i class="bi bi-geo-alt icon-left"></i>
                        <input type="text" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                        echo $event_venue;
                                                                                    } ?>" name="event_venue" id="event_venue" placeholder="Event Venue" required readonly>
                    </div>
                </div>

                <!-- Event Type and Maximum Participants -->
                <div class="row-2">
                    <div>
                        <label class="field-label">Event Type</label>
                        <div class="select-wrap">
                            <i class="bi bi-list icon-left"></i>
                            <i class="bi bi-chevron-down chevron"></i>
                            <select class="form-ctrl form-select form-control" id="event_type" name="event_type" required>
                                <option value="">Select Event Type</option>
                                <option value="Solo" <?php if (isset($_GET["event_id"])) {
                                                            if ($event_type === "Solo") {
                                                                echo "selected";
                                                            }
                                                        } ?>>Solo Event</option>
                                <option value="Team" <?php if (isset($_GET["event_id"])) {
                                                            if ($event_type === "Team") {
                                                                echo "selected";
                                                            }
                                                        } ?>>Team Event</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Maximum Participants</label>
                        <div class="select-wrap">
                            <i class="bi bi-list icon-left"></i>
                            <i class="bi bi-chevron-down chevron"></i>
                            <select class="form-ctrl form-select form-control" id="total_members" name="total_members" required>
                                <option value="">Select Maximum Participants</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Event Start Date and Time -->
                <div class="row-2">
                    <div>
                        <label class="field-label">Event Start Date</label>
                        <div class="input-wrap">
                            <i class="bi bi-calendar icon-left"></i>
                            <input type="date" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                            echo $event_start_date;
                                                                                        } ?>" name="event_start_date" id="event_start_date" required readonly>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Event Start Time</label>
                        <div class="input-wrap">
                            <i class="bi bi-clock icon-left"></i>
                            <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                            echo $event_start_time;
                                                                                        } ?>" name="event_start_time" id="event_start_time" required readonly>
                        </div>
                    </div>
                </div>

                <!-- Event End Date and Time -->
                <div class="row-2">
                    <div>
                        <label class="field-label">Event End Date</label>
                        <div class="input-wrap">
                            <i class="bi bi-calendar icon-left"></i>
                            <input type="date" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                            echo $event_end_date;
                                                                                        } ?>" name="event_end_date" id="event_end_date" required readonly>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Event End Time</label>
                        <div class="input-wrap">
                            <i class="bi bi-clock icon-left"></i>
                            <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                            echo $event_end_time;
                                                                                        } ?>" name="event_end_time" id="event_end_time" required readonly>
                        </div>
                    </div>
                </div>

                <!-- Event Category -->
                <label class="field-label">Event Category</label>
                <div class="select-wrap">
                    <i class="bi bi-list icon-left"></i>
                    <i class="bi bi-chevron-down chevron"></i>
                    <select class="form-ctrl form-select form-control" id="event_category" name="event_category" required>
                        <option value="">Select Category</option>
                        <option value="Visvesmruti" <?php if (isset($_GET["event_id"])) {
                                                        if ($event_category === "Visvesmruti") {
                                                            echo "selected";
                                                        }
                                                    } ?>>Visvesmruti</option>
                        <option value="Kaushalya" <?php if (isset($_GET["event_id"])) {
                                                        if ($event_category === "Kaushalya") {
                                                            echo "selected";
                                                        }
                                                    } ?>>Kaushalya</option>
                        <option value="Kashish" <?php if (isset($_GET["event_id"])) {
                                                    if ($event_category === "Kashish") {
                                                        echo "selected";
                                                    }
                                                } ?>>Kashish</option>
                        <option value="Skill Development Seminar" <?php if (isset($_GET["event_id"])) {
                                                                        if ($event_category === "Skill Development Seminar") {
                                                                            echo "selected";
                                                                        }
                                                                    } ?>>Skill Development Seminar</option>
                        <option value="Social & Community Activity" <?php if (isset($_GET["event_id"])) {
                                                                        if ($event_category === "Social & Community Activity") {
                                                                            echo "selected";
                                                                        }
                                                                    } ?>>Social & Community Activity</option>
                    </select>
                </div>

                <!-- Fee -->
                <label class="field-label">Registration Fee</label>
                <div class="input-wrap">
                    <i class="bi bi-currency-rupee icon-left"></i>
                    <input type="number" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                                    echo $registration_fee;
                                                                                } ?>" name="registration_fee" id="registration_fee" placeholder="0.00" required readonly>
                </div>

                <!-- Organized By -->
                <label class="field-label">Organized By</label>
                <div class="select-wrap">
                    <i class="bi bi-building icon-left"></i>
                    <i class="bi bi-chevron-down chevron"></i>
                    <select class="form-ctrl form-select form-control" name="organized_by" id="organized_by" required>
                        <option value="">Select Organizer</option>
                        <option value="Computer Science and Engineering" <?php if (isset($_GET["event_id"])) {
                                                                                if ($organized_by === "Computer Science and Engineering") {
                                                                                    echo "selected";
                                                                                }
                                                                            } ?>>Computer Science and Engineering</option>
                        <option value="Mechanical Engineering" <?php if (isset($_GET["event_id"])) {
                                                                    if ($organized_by === "Mechanical Engineering") {
                                                                        echo "selected";
                                                                    }
                                                                } ?>>Mechanical Engineering</option>
                        <option value="Civil Engineering" <?php if (isset($_GET["event_id"])) {
                                                                if ($organized_by === "Civil Engineering") {
                                                                    echo "selected";
                                                                }
                                                            } ?>>Civil Engineering</option>
                        <option value="Chemical Engineering" <?php if (isset($_GET["event_id"])) {
                                                                    if ($organized_by === "Chemical Engineering") {
                                                                        echo "selected";
                                                                    }
                                                                } ?>>Chemical Engineering</option>
                        <option value="Information Technology" <?php if (isset($_GET["event_id"])) {
                                                                    if ($organized_by === "Information Technology") {
                                                                        echo "selected";
                                                                    }
                                                                } ?>>Information Technology</option>
                        <option value="Electrical Engineering" <?php if (isset($_GET["event_id"])) {
                                                                    if ($organized_by === "Electrical Engineering") {
                                                                        echo "selected";
                                                                    }
                                                                } ?>>Electrical Engineering</option>
                        <option value="R.N.G. Patel Institute of Technology" <?php if (isset($_GET["event_id"])) {
                                                                                    if ($organized_by === "R.N.G. Patel Institute of Technology") {
                                                                                        echo "selected";
                                                                                    }
                                                                                } ?>>R.N.G. Patel Institute of Technology</option>
                    </select>
                </div>

                <div class="step-actions justify-content-end">
                    <button
                        type="button"
                        class="btn-submit w-auto px-4"
                        onclick="nextStep(1)">
                        Next
                    </button>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- STEP 2 – TEAM DETAILS -->
            <!-- ===================================================== -->
            <div class="screen" id="step2">
                <?php
                if ($event_type === "Solo") {
                    echo "<h2>Participant's Details</h2>";
                }

                if ($event_type === "Team") {
                    echo "<h2>Team Member Details</h2>";
                }
                ?>
                <p class="form-hint">
                    Add student coordinators responsible for the event.
                </p>
                <div class="form-divider"></div>

                <?php if ($event_type === "Team"): ?>
                    <!-- <div class="student-block"> -->
                    <label class="field-label">Team Name</label>
                    <div class="input-wrap">
                        <i class="bi bi-people icon-left"></i>
                        <input type="hidden" name="contact" value="<?php echo $contact_number; ?>">
                        <input type="text" class="form-ctrl form-control"
                            name="team_name" placeholder="Enter Team Name" required>
                    </div>
                    <!-- </div> -->
                    <label class="field-label">Select Number of Team Members</label>
                    <div class="select-wrap">
                        <i class="bi bi-people icon-left"></i>
                        <i class="bi bi-chevron-down chevron"></i>
                        <select
                            class="form-ctrl form-select form-control"
                            name="team_members"
                            id="studentCount"
                            onchange="generateStudents()"
                            required>
                            <option value="">Select</option>
                            <?php
                            for ($i = 2; $i <= $maximum_participants; $i++) {
                                echo "<option value='{$i}'>{$i}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <?php else: ?> <!-- For Solo Event -->
                    <div class="student-block">
                        <h6 class="mb-3"></h6>
                        <div class="row-2">
                            <div>
                                <label class="field-label">Student Email</label>
                                <div class="input-wrap">
                                    <i class="bi bi-envelope icon-left"></i>
                                    <input type="text" name="email" class="form-ctrl form-control"
                                        value="<?php echo $email; ?>" required readonly>
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Student Enrollment</label>
                                <div class="input-wrap">
                                    <i class="bi bi-person-badge icon-left"></i>
                                    <input type="tel" class="form-ctrl form-control studentEnrollment" name="student_enrollment" required>
                                    <small class="d-none studentEnrollmentError"></small>
                                </div>
                            </div>
                            <input type="hidden" name="contact" value="<?php echo $contact_number; ?>">
                        </div>
                    </div>
                <?php endif; ?>

                <div id="studentContainer">
                </div>

                <div class="step-actions">
                    <button
                        type="button"
                        class="btn-submit w-auto px-4"
                        onclick="prevStep(2)">
                        Previous
                    </button>
                    <?php if ($registration_fee == 0): ?>
                        <button type="submit" id="register" name="register" class="btn-submit w-auto px-4">
                            Register
                        </button>
                    <?php else: ?>
                        <button type="submit" formaction="Payment.php" id="proceedToPay" name="proceedToPay" class="btn-submit w-auto px-4">
                            Proceed To Pay
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div id="loadingOverlay">
        <div class="loader"></div>
        <p>Processing your registration and generating your event pass...</p>
        <small>⚠️ Please do not refresh or close this page.</small>
    </div>
    <!-- FORM CODE ENDS HERE -->

    <!-- 🔹 Footer -->
    <?php include "../footer.php"; ?>

    <!-- ===================================================== -->
    <!-- JAVASCRIPT -->
    <!-- ===================================================== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://checkout.razorpay.com/v1/checkout.js"></script> -->
    <script>
        // Loader on Form Submit
        document.querySelector("form").addEventListener("submit", function(e) {

            const overlay = document.getElementById("loadingOverlay");
            const statusText = overlay.querySelector("p");
            const clickedButton = e.submitter; // button that triggered submit

            if (clickedButton.id === "register") {
                statusText.textContent = "Processing your registration and generating your event pass...";
            }

            if (clickedButton.id === "proceedToPay") {
                statusText.textContent = "Redirecting to secure payment gateway...";
            }

            overlay.style.display = "flex";

            // disable buttons to prevent double click
            const submitButtons = document.querySelectorAll("button[type='submit']");
            setTimeout(() => {
                submitButtons.forEach(btn => btn.disabled = true);
            }, 10);

        });

        // Hide loader if user comes back using browser back button
        window.addEventListener("pageshow", function(event) {
            const overlay = document.getElementById("loadingOverlay");

            if (overlay) {
                overlay.style.display = "none";
            }

            // Re-enable buttons
            document.querySelectorAll("button[type='submit']").forEach(btn => {
                btn.disabled = false;
            });
        });

        // Dropdown disable
        $("#organized_by, #event_category, #total_members, #event_type").on("mousedown", function(e) {
            e.preventDefault();
        });

        // Previous Button
        function nextStep(step) {
            const current = document.getElementById("step" + step);
            const inputs = current.querySelectorAll("input, select");

            current.classList.remove("active");
            document.getElementById("step" + (step + 1)).classList.add("active");
        }

        // Next Button
        function prevStep(step) {
            document.getElementById("step" + step).classList.remove("active");
            document.getElementById("step" + (step - 1)).classList.add("active");
        }

        // Generating Team Member Fields
        function generateStudents() {
            const count = document.getElementById("studentCount").value;
            const container = document.getElementById("studentContainer");
            const eventType = $("#event_type").val();

            container.innerHTML = "";

            for (let i = 1; i <= count; i++) {
                let title = `Member ${i}`;
                let email = "";
                let readonly = "";

                if (eventType === "Team" && i === 1) {
                    title = "Member 1 (Team Leader)";
                    email = "<?php echo $email; ?>";
                    readonly = "readonly";
                }

                container.innerHTML += `
      <div class="student-block">
        <h6 class="mb-3">${title}</h6>

        <div class="row-2">
          <div>
            <label class="field-label">Student Email</label>
            <div class="input-wrap">
              <i class="bi bi-envelope icon-left"></i>
              <input type="text" class="form-ctrl form-control studentEmail" name="student_email[]"
                     value="${email}" ${readonly} required>
              <small class="d-none studentEmailError"></small>
              <small class="d-none userError"></small>
              <small class="d-none successElement"></small>
            </div>
          </div>

          <div>
            <label class="field-label">Student Enrollment</label>
            <div class="input-wrap">
              <i class="bi bi-telephone icon-left"></i>
              <input type="tel" class="form-ctrl form-control studentEnrollment" name="student_enrollment[]" required>
              <small class="d-none studentEnrollmentError"></small>
            </div>
          </div>
        </div>
      </div>
    `;
            }
        }

        // Form Submission Handling
        $("#register, #proceedToPay").on("click", function(e) {
            if (
                $(".studentEmailError:not(.d-none)").length > 0 ||
                $(".studentEnrollmentError:not(.d-none)").length > 0 ||
                $(".userError:not(.d-none)").length > 0
            ) {
                e.preventDefault();
                alert("Please fix the highlighted errors.");
            }
        });

        // Pre-filled Event Type and Maximum Participants Dropdown Options
        $(document).ready(function() {
            let savedMembers = "<?= $maximum_participants ?>";
            let savedType = "<?= $event_type ?>";

            $("#event_type").val(savedType);

            $("#total_members").empty();
            $("#total_members").append('<option value="">Select Maximum Participants</option>');

            if (savedType === "Solo") {
                $("#total_members").append('<option value="1">1</option>');
            }

            if (savedType === "Team") {
                for (let i = 1; i <= 5; i++) {
                    $("#total_members").append(`<option value="${i}">${i}</option>`);
                }
            }
            $("#total_members").val(savedMembers);
        });

        // Maximum Participants Dropdown Options
        $("#event_type").change(function() {
            let eventType = $(this).val();

            $("#total_members").empty();
            $("#total_members").append('<option value="">Select Maximum Participants</option>');

            if (eventType === "Solo") {
                $("#total_members").append('<option value="1">1</option>');
            }

            if (eventType === "Team") {
                for (let i = 1; i <= 5; i++) {
                    $("#total_members").append(`<option value="${i}">${i}</option>`);
                }
            }
        });

        // Email Validation
        $(document).on("input", ".studentEmail", function() {
            const email = $(this).val();
            let errorElement = $(this).siblings(".studentEmailError");

            const basicPattern = /^[a-zA-Z0-9]+([._%+-][a-zA-Z0-9]+)*@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            let duplicate = false;

            $(".studentEmail").not(this).each(function() {
                if ($(this).val().trim().toLowerCase() === email && email !== "") {
                    duplicate = true;
                }
            });

            if (email.length === 0) {
                errorElement.text("Email address is required.").css("color", "red").removeClass("d-none");
            } else if (!email.includes("@")) {
                errorElement.text("Email must contain '@' symbol.").css("color", "red").removeClass("d-none");
            } else if (!email.includes(".")) {
                errorElement.text("Email must contain a domain extension (e.g. .com).").css("color", "red").removeClass("d-none");
            } else if (!basicPattern.test(email)) {
                errorElement.text("Please enter a valid email address.").css("color", "red").removeClass("d-none");
            } else if (duplicate) {
                errorElement.text("This email is already entered.").css("color", "red").removeClass("d-none");
            } else {
                errorElement.addClass("d-none");
            }
        });

        // Enrollment Validation
        $(document).on("input", ".studentEnrollment", function() {
            const enrollment = $(this).val();
            const onlyDigits = /^\d{12}$/;

            let errorElement = $(this).siblings(".studentEnrollmentError");

            let duplicate = false;

            $(".studentEnrollment").not(this).each(function() {
                if ($(this).val().trim() === enrollment && enrollment !== "") {
                    duplicate = true;
                }
            });

            if (enrollment.length === 0) {
                errorElement.text("Enrollment number is required.").css("color", "red").removeClass("d-none");
            } else if (!onlyDigits.test(enrollment)) {
                errorElement.text("Enrollment must be exactly 12 digits (numbers only).").css("color", "red").removeClass("d-none");
            } else if (duplicate) {
                errorElement.text("This enrollment number is already entered.").css("color", "red").removeClass("d-none");
            } else {
                errorElement.addClass("d-none");
            }
        });

        // Checking the Email in the Database
        $(document).on("blur", ".studentEmail", function() {
            const email = $(this).val();
            const event_id = "<?php echo $event_id; ?>";

            let errorElement = $(this).siblings(".userError");
            let successElement = $(this).siblings(".successElement");

            const basicPattern = /^[a-zA-Z0-9]+([._%+-][a-zA-Z0-9]+)*@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            let duplicate = false;

            $(".studentEmail").not(this).each(function() {
                if ($(this).val().trim().toLowerCase() === email && email !== "") {
                    duplicate = true;
                }
            });

            // STOP if invalid or duplicate
            if (!basicPattern.test(email) || duplicate || email === "") {
                errorElement.addClass("d-none");
                successElement.addClass("d-none");
                return;
            }

            $.ajax({
                url: "script.php",
                type: "POST",
                data: {
                    email: email,
                    eventId: event_id,
                },
                success: function(response) {
                    if (response.trim() === "not_found") {
                        errorElement.text("User not registered.")
                            .css("color", "red")
                            .removeClass("d-none");
                        successElement.addClass("d-none");
                    } else if (response.trim() === "already_registered") {
                        errorElement.text("User already registered in this event.")
                            .css("color", "red")
                            .removeClass("d-none");
                        successElement.addClass("d-none");
                    } else {
                        successElement.text(response)
                            .css("color", "green")
                            .removeClass("d-none");
                        errorElement.addClass("d-none");
                    }
                }
            });
        });

        // Logout Toggle
        let logoutText = document.getElementById("logout-opt");
        logoutText.addEventListener("click", function(event) {
            event.preventDefault();
            let x = confirm("Are you sure you want to Logout?");

            if (x) {
                window.location = "../logout.php";
            }
        });

        // Back Arrow Button Page Redirection
        <?php if (isset($_SESSION["active_page"])): ?>
            let backToLogin = document.getElementById("backButton");
            backToLogin.addEventListener("click", function() {
                window.location = "<?php echo $back_page; ?>.php";
            });
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>