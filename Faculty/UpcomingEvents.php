<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
    $_SESSION["active_page"] = "UpcomingEvents"; // Current Page Name

    $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $_SESSION["user_id"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $name = $firstname . " " . $lastname;

    $name_initials = $firstname[0] . $lastname[0];
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
    <title>Upcoming Events – CEBS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../style.css">
    <Style>
        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        #btn-red {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
        }

        .pagination-div {
            margin-top: 2rem;
            margin-bottom: -60px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #event-section {
            padding-bottom: 0;
        }
    </Style>
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

    <section class="dashboard-hero" style="padding: 50px">
        <!-- 🔹 Hero Title Section -->
        <div
            class="container text-left"
            style="
          display: flex;
          flex-direction: row;
          justify-content: space-between;
        ">
            <div>
                <h2 class="section-title mb-2">Upcoming Events</h2>
                <p class="section-subtitle m-0">
                    Explore all upcoming and featured events happening at our institute
                </p>
            </div>

            <!-- 🔍 Event Search -->
            <div style="width: 30%">
                <div class="container">
                    <div class="event-search-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input
                            type="text"
                            id="eventSearch"
                            class="event-search-input"
                            placeholder="Search events, workshops, hackathons..." />
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Events Grid -->
        <div id="event-data">
        </div>
    </section>

    <!-- 🔹 Footer -->
    <?php include "../footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Pagination
        $(document).ready(function() {
            function loadData(page = 1) {
                let search = $("#eventSearch").val();
                let activePage = "<?php echo $_SESSION["active_page"]; ?>";

                $.ajax({
                    url: "EventsData.php",
                    method: "POST",
                    data: {
                        page: page,
                        search: search,
                        activePage: activePage
                    },
                    success: function(response) {
                        console.log(response);
                        let res = JSON.parse(response);

                        $("#event-data").html(res.output);
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