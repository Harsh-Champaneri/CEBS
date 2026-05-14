<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
    $_SESSION["active_page"] = "ViewParticipants"; // Current Page Name

    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $_SESSION["user_id"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $name = $firstname . " " . $lastname;

    $name_initials = $firstname[0] . $lastname[0];

    // Total Events Count
    $total_event_created_query = $connection->prepare("SELECT COUNT(fid) AS totol_count FROM faculty_coordinators WHERE user_id = ?");
    $total_event_created_query->bind_param("s", $_SESSION["user_id"]);
    $total_event_created_query->execute();
    $total_event_created = $total_event_created_query->get_result()->fetch_assoc()["totol_count"];

    // Total Participants Count
    $total_participants_query = $connection->prepare("SELECT COUNT(pid) AS total_participants FROM faculty_coordinators INNER JOIN participants ON participants.event_id = faculty_coordinators.event_id WHERE faculty_coordinators.user_id = ?");
    $total_participants_query->bind_param("s", $_SESSION["user_id"]);
    $total_participants_query->execute();
    $total_participants = $total_participants_query->get_result()->fetch_assoc()["total_participants"];

    // Total Revenue
    $total_revenue_query = $connection->prepare("SELECT SUM(amount) AS total_revenue FROM faculty_coordinators INNER JOIN payments ON payments.event_id = faculty_coordinators.event_id WHERE faculty_coordinators.user_id = ? AND(payments.status = 'FREE' OR payments.status = 'captured') AND refund_status IS NULL");
    $total_revenue_query->bind_param("s", $_SESSION["user_id"]);
    $total_revenue_query->execute();
    $total_revenue = $total_revenue_query->get_result()->fetch_assoc()["total_revenue"];

    if ($total_revenue === NULL) {
        $total_revenue = 0;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Participants - CEBS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" />

    <link rel="stylesheet" href="../style.css">
    <style>
        /* My Events Page Specific Styles */
        .events-container {
            padding: 40px 0;
        }

        .page-header {
            margin-bottom: 32px;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #1e293b;
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #64748b;
            font-size: .95rem;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--card-bdr);
            border-radius: 14px;
            padding: 20px;
            backdrop-filter: blur(12px);
            text-align: center;
            transition: transform .3s, box-shadow .3s;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(14, 165, 233, .2);
        }

        .summary-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 12px;
        }

        .summary-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .summary-card p {
            font-size: .8rem;
            color: #64748b;
            margin: 4px 0 0;
        }

        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--card-bdr);
            border-radius: 18px;
            padding: 0;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, .15);
            overflow: hidden;
        }

        .table-header {
            padding: 24px 28px;
            border-bottom: 1px solid rgba(14, 165, 233, .15);
        }

        .table-header h5 {
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            font-size: 1.1rem;
        }

        .events-table {
            margin: 0;
            background: transparent;
        }

        .events-table thead {
            background: linear-gradient(135deg, rgba(14, 165, 233, .08), rgba(56, 189, 248, .04));
        }

        .events-table thead th {
            border: none;
            padding: 16px 20px;
            font-size: .8rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .events-table tbody tr {
            border-bottom: 1px solid rgba(14, 165, 233, .1);
            transition: background .2s;
        }

        .events-table tbody tr:hover {
            background: rgba(191, 219, 254, .15);
        }

        .events-table tbody tr:last-child {
            border-bottom: none;
        }

        .events-table tbody td {
            padding: 18px 20px;
            font-size: .9rem;
            color: #1e293b;
            vertical-align: middle;
            border: none;
        }

        .event-id {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #0ea5e9;
            font-size: .85rem;
        }

        .participant-count {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            color: #1e293b;
        }

        .participant-count i {
            color: #0ea5e9;
        }

        .capacity-info {
            font-size: .85rem;
            color: #64748b;
        }

        .capacity-full {
            color: #f43f5e;
            font-weight: 600;
        }

        .event-status {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .event-status.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, .15), rgba(52, 211, 153, .08));
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, .25);
        }

        .event-status.upcoming {
            background: linear-gradient(135deg, rgba(14, 165, 233, .15), rgba(56, 189, 248, .08));
            color: #0369a1;
            border: 1px solid rgba(14, 165, 233, .25);
        }

        .event-status.completed {
            background: linear-gradient(135deg, rgba(100, 116, 139, .15), rgba(148, 163, 184, .08));
            color: #334155;
            border: 1px solid rgba(100, 116, 139, .25);
        }

        .event-status.cancelled {
            background: linear-gradient(135deg, rgba(239, 68, 68, .15), rgba(248, 113, 113, .08));
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, .25);
        }

        .export-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: .8rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            border: none;
            cursor: pointer;
        }

        .export-btn:hover {
            transform: translateY(-2px);
        }

        .export-excel {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, .3);
        }

        .export-excel:hover {
            box-shadow: 0 6px 18px rgba(16, 185, 129, .45);
            color: #fff;
        }

        .export-pdf {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            color: #fff;
            box-shadow: 0 4px 12px rgba(244, 63, 94, .3);
        }

        .export-pdf:hover {
            box-shadow: 0 6px 18px rgba(244, 63, 94, .45);
            color: #fff;
        }

        .export-btn i {
            font-size: .9rem;
        }

        .revenue-info {
            font-weight: 600;
            color: #1e293b;
        }

        /* Action Icons */
        .action-icons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 2px 8px rgba(14, 165, 233, .25);
            font-size: .85rem;
            border: none;
        }

        .action-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, .4);
            color: #fff;
        }

        .action-icon.edit {
            background: linear-gradient(135deg, #fb923c, #f97316);
            box-shadow: 0 2px 8px rgba(251, 146, 60, .25);
        }

        .action-icon.edit:hover {
            box-shadow: 0 4px 12px rgba(251, 146, 60, .4);
        }

        /* Mobile Responsive - Card View */
        @media (max-width: 968px) {
            .table-card {
                border-radius: 14px;
            }

            .table-header {
                padding: 20px;
            }

            /* Hide table, show card view */
            .events-table thead {
                display: none;
            }

            .events-table,
            .events-table tbody,
            .events-table tr,
            .events-table td {
                display: block;
                width: 100%;
            }

            .events-table tr {
                background: rgba(255, 255, 255, .4);
                margin-bottom: 16px;
                border-radius: 12px;
                border: 1px solid rgba(14, 165, 233, .15);
                padding: 16px;
            }

            .events-table tr:hover {
                background: rgba(191, 219, 254, .25);
            }

            .events-table td {
                padding: 8px 0;
                text-align: left;
                position: relative;
                padding-left: 50%;
            }

            .events-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                font-weight: 700;
                font-size: .75rem;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .5px;
            }

            .events-table td.text-center {
                text-align: left !important;
            }

            .export-buttons {
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .action-icons {
                justify-content: flex-start;
            }

            .summary-cards {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.6rem;
            }
        }

        /* Extra small devices */
        @media (max-width: 480px) {
            .events-container {
                padding: 20px 0;
            }

            .summary-card {
                padding: 16px;
            }

            .summary-card h3 {
                font-size: 1.5rem;
            }
        }

        .pagination-div {
            margin-top: 2rem;
            margin-bottom: 2rem;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #event-section {
            padding-bottom: 0;
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
                        <a class="nav-link active" href="ViewParticipants.php">View Participants</a>
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

    <!-- 🔹 My Events Content -->
    <section class="events-container" id="event-section" style="position: relative; overflow: hidden;">
        <!-- Floating orbs -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fa-regular fa-address-card me-2" style="color: #0ea5e9;"></i>View Participants</h1>
                    <p>Manage all events you've created and export participant data</p>
                </div>

                <div style="width: 30%; align-self: center;">
                    <div class="container">
                        <div class="event-search-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input
                                type="text"
                                id="eventSearch"
                                class="event-search-input"
                                placeholder="Search for events and participation..." />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="icon" style="background: linear-gradient(135deg, rgba(14,165,233,.2), rgba(56,189,248,.12)); color: #0284c7;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?php echo $total_event_created; ?></h3>
                    <p>Total Events</p>
                </div>

                <div class="summary-card">
                    <div class="icon" style="background: linear-gradient(135deg, rgba(16,185,129,.2), rgba(52,211,153,.12)); color: #047857;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3><?php echo $total_participants; ?></h3>
                    <p>Total Participants</p>
                </div>

                <div class="summary-card">
                    <div class="icon" style="background: linear-gradient(135deg, rgba(244,63,94,.2), rgba(251,113,133,.12)); color: #be123c;">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <h3>₹<?php echo $total_revenue; ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>

            <!-- Events Table -->
            <div class="table-card">
                <div class="table-header d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-grid-3x3 me-2"></i>Events Management</h5>
                    <a href="AddEvent.php" class="btn-register" style="padding: 8px 18px; font-size: .85rem;">
                        <i class="bi bi-plus-circle me-1"></i> Create New Event
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table events-table align-middle">
                        <thead>
                            <tr>
                                <th>SR. NO.</th>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Participants</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                                <th class="text-center">Export Data</th>
                            </tr>
                        </thead>

                        <tbody id="participantsTable"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pagination-div">
            <ul class="pagination" id="pagination"></ul>
        </div>

    </section>

    <div id="loadingOverlay">
        <div class="loader"></div>
        <p>Processing your registration and generating your event pass...</p>
        <small>⚠️ Please do not refresh or close this page.</small>
    </div>

    <!-- 🔹 Footer -->
    <?php include "../footer.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Pagination 
        $(document).ready(function() {

            function loadData(page = 1) {
                let search = $("#eventSearch").val();

                $.ajax({
                    url: "ViewParticipantsData.php",
                    method: "POST",
                    data: {
                        page: page,
                        search: search,
                    },
                    success: function(response) {
                        console.log(response);
                        let res = JSON.parse(response);

                        $("#participantsTable").html(res.table);
                        $("#pagination").html(res.pagination);
                    }
                });
            }

            loadData();

            $(document).on("click", ".page-link", function(e) {
                e.preventDefault();
                let page = $(this).data("page");

                if (page) {
                    loadData(page);
                }
            });

            // Live Search
            $("#eventSearch").on("keyup", function() {
                loadData(1);
            });
        });

        // Loader on Form Submit
        $(document).on("submit", "form", function(e) {
            const clickedButton = e.originalEvent.submitter;

            // 👉 Run ONLY for Excel & PDF buttons
            if (!clickedButton || (clickedButton.name !== "excel" && clickedButton.name !== "pdf")) {
                return; // skip loader
            }

            const overlay = document.getElementById("loadingOverlay");
            const statusText = overlay.querySelector("p");
            // const clickedButton = e.originalEvent.submitter; // button that triggered submit

            if (clickedButton.name === "excel") {
                statusText.textContent = "Generating Excel file... Please wait.";
            }

            if (clickedButton.name === "pdf") {
                statusText.textContent = "Generating PDF file... Please wait.";
            }

            overlay.style.display = "flex";

            // disable buttons to prevent double click
            const submitButtons = document.querySelectorAll("button[type='submit']");
            setTimeout(() => { 
                submitButtons.forEach(btn => btn.disabled = true);
            }, 10);

            let checkDownload = setInterval(function() {
                if (document.cookie.indexOf("fileDownload=true") !== -1) {
                    clearInterval(checkDownload);

                    overlay.style.display = "none";

                    submitButtons.forEach(btn => btn.disabled = false);

                    // delete cookie
                    document.cookie = "fileDownload=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                }
            }, 500); 
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
    </script>
</body>

</html>