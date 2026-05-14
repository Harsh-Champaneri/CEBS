<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
  $user_id = $_SESSION["user_id"];

  $_SESSION["active_page"] = "Dashboard";  // Current Page Name

  $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
  $stmt->bind_param("s", $_SESSION["user_id"]);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  $firstname = $data["firstname"];
  $lastname = $data["lastname"];
  $name = $firstname . " " . $lastname;

  $name_initials = $firstname[0] . $lastname[0];

  $showWelcome = false;
  if (empty($_SESSION['welcome_shown'])) {
    $showWelcome = true;
    $_SESSION['welcome_shown'] = true;
  }

  // Total Events Created Count
  $events_created_query = $connection->prepare("SELECT COUNT(*) AS event_created FROM event WHERE created_by = ?");
  $events_created_query->bind_param("s", $user_id);
  $events_created_query->execute();
  $events_created = $events_created_query->get_result()->fetch_assoc()["event_created"];

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

  // Active Event Count
  $active_event_query = $connection->prepare("SELECT COUNT(*) AS active_event FROM faculty_coordinators INNER JOIN event ON event.event_id = faculty_coordinators.event_id WHERE faculty_coordinators.user_id = ? AND cancelled = 'No' AND (TIMESTAMP(event.event_start_date, event.event_start_time) <= NOW() AND TIMESTAMP(event.event_end_date, event.event_end_time) >= NOW())");
  $active_event_query->bind_param("s", $user_id);
  $active_event_query->execute();
  $active_event = $active_event_query->get_result()->fetch_assoc()["active_event"];

  // Event Data
  $event_data_query = $connection->prepare("SELECT event.* FROM faculty_coordinators INNER JOIN event ON event.event_id = faculty_coordinators.event_id WHERE faculty_coordinators.user_id = ? AND cancelled = 'No' AND (TIMESTAMP(event.event_start_date, event.event_start_time) >= NOW() OR TIMESTAMP(event.event_end_date, event.event_end_time) >= NOW()) ORDER BY event.event_start_date ASC, event.event_start_time ASC, event.event_end_date ASC, event.event_end_time ASC LIMIT 3");
  $event_data_query->bind_param("s", $user_id);
  $event_data_query->execute();
  $event_data_result = $event_data_query->get_result();
} else {
  header("location:../login.php");
  exit();
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - CEBS</title>

  <!-- Bootstrap -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <!-- Icons -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    rel="stylesheet" />
  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="../style.css" />

  <style>
    .btn-action {
      border: none;
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
            <a class="nav-link active" href="Dashboard.php">Dashboard</a>
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
  <!-- 🔹 Dashboard Hero / Welcome Section -->
  <section class="dashboard-hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
      <div class="welcome-card">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h2>Welcome back, Prof. <?php echo $name; ?>! 👋</h2>
            <p class="greeting mb-3">
              Manage your events, track participants, and oversee event operations
              from your dashboard.
            </p>
            <div class="d-flex gap-2 flex-wrap">
              <a
                href="AddEvent.php"
                class="btn-hero"
                style="padding: 10px 24px; font-size: 0.85rem">
                <i class="bi bi-calendar-plus"></i> Add New Event
              </a>
            </div>
          </div>
          <div class="col-lg-4 text-end d-none d-lg-block">
            <div style="font-size: 5rem; opacity: 0.7">🎉</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔹 Quick Stats -->
  <section class="py-4 reveal">
    <div class="container">
      <div class="row g-3">
        <div class="col-6 col-lg-3">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(14, 165, 233, 0.2),
                    rgba(56, 189, 248, 0.12)
                  );
                  color: #0284c7;
                ">
              <i class="bi bi-calendar-check"></i>
            </div>
            <h4><?php echo $events_created; ?></h4>
            <p>Events Created</p>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(16, 185, 129, 0.2),
                    rgba(52, 211, 153, 0.12)
                  );
                  color: #047857;
                ">
              <i class="bi bi-people"></i>
            </div>
            <h4><?php echo $total_participants; ?></h4>
            <p>Total Participants</p>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(244, 63, 94, 0.2),
                    rgba(251, 113, 133, 0.12)
                  );
                  color: #be123c;
                ">
              <i class="bi bi-currency-rupee"></i>
            </div>
            <h4>₹<?php echo $total_revenue; ?></h4>
            <p>Revenue Generated</p>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(251, 146, 60, 0.2),
                    rgba(253, 186, 116, 0.12)
                  );
                  color: #c2410c;
                ">
              <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h4><?php echo $active_event; ?></h4>
            <p>Active Events</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔹 Quick Actions -->
  <section class="py-5 reveal">
    <div class="container">
      <h2 class="text-center section-title mb-2">Quick Actions</h2>
      <p class="text-center section-subtitle mb-4">
        Everything you need at your fingertips
      </p>
      <div class="row g-4">
        <div class="col-lg-3 col-md-6">
          <div class="action-card text-center">
            <div
              class="action-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(14, 165, 233, 0.2),
                    rgba(56, 189, 248, 0.12)
                  );
                  color: #0284c7;
                ">
              <i class="bi bi-plus-circle"></i>
            </div>
            <h5>Create Event</h5>
            <p>Set up a new event with registration and payment options</p>
            <a
              href="AddEvent.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #0ea5e9, #0284c7);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(14, 165, 233, 0.3);
                ">
              Create New <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="action-card text-center">
            <div
              class="action-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(16, 185, 129, 0.2),
                    rgba(52, 211, 153, 0.12)
                  );
                  color: #047857;
                ">
              <i class="bi bi-calendar-event"></i>
            </div>
            <h5>My Events</h5>
            <p>View and manage all events you've created</p>
            <a
              href="MyEvents.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #10b981, #059669);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
                ">
              View Events <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="action-card text-center">
            <div
              class="action-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(244, 63, 94, 0.2),
                    rgba(251, 113, 133, 0.12)
                  );
                  color: #be123c;
                ">
              <i class="bi bi-qr-code-scan"></i>
            </div>
            <h5>QR Scanner</h5>
            <p>Scan participant QR codes for event entry verification</p>
            <a
              href="ScanQR.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #f43f5e, #e11d48);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(244, 63, 94, 0.3);
                ">
              Open Scanner <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="action-card text-center">
            <div
              class="action-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(251, 146, 60, 0.2),
                    rgba(253, 186, 116, 0.12)
                  );
                  color: #c2410c;
                ">
              <i class="bi bi-file-earmark-bar-graph"></i>
            </div>
            <h5>Reports</h5>
            <p>Generate and download participant & revenue reports</p>
            <a
              href="ViewParticipants.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #fb923c, #f97316);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(251, 146, 60, 0.3);
                ">
              View Reports <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 🔹 My Upcoming Tickets -->
  <section class="py-5 reveal" id="tickets">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="section-title mb-2">My Active Events</h2>
          <p class="section-subtitle mb-0">
            Events currently open for registration
          </p>
        </div>
        <a
          href="MyEvents.php"
          style="
              color: #0ea5e9;
              font-size: 0.9rem;
              font-weight: 600;
              text-decoration: none;
            ">
          View All <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <?php if ($event_data_result->num_rows > 0): ?>
        <div class="row">
          <div class="col-lg-8" style="width: 100%;">

            <?php
            $event_revenue_query = $connection->prepare("SELECT SUM(amount) AS revenue FROM payments WHERE event_id = ? AND(status = 'FREE' OR status = 'captured')");
            $event_participants_query = $connection->prepare("SELECT COUNT(*) AS participants FROM participants WHERE event_id = ?");

            while ($event_data = $event_data_result->fetch_assoc()) {

              $event_id = $event_data["event_id"];

              $currentDateTime = date("Y-m-d H:i:s"); // System current date and time
              $eventDateTimeStart = $event_data["event_start_date"] . ' ' . $event_data["event_start_time"];   // Event start date and time
              $eventDateTimeEnd = $event_data["event_end_date"] . ' ' . $event_data["event_end_time"];   // Event end date and time

              if (strtotime($currentDateTime) < strtotime($eventDateTimeStart)) {
                $status = "upcoming";
                $edit = "<a href='AddEvent.php?event_id=$event_id' class='btn-action' style='background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);'>
                          <i class='bi bi-pencil'></i> Edit Event
                        </a>";
              } else if ((strtotime($currentDateTime) > strtotime($eventDateTimeStart)) && (strtotime($currentDateTime) < strtotime($eventDateTimeEnd))) {
                $status = "active";
                $edit = "";
              }

              $event_name = $event_data["event_name"];
              $event_start_date = date("jS F Y", strtotime($event_data["event_start_date"]));
              $event_venue = $event_data["event_venue"];

              $event_revenue_query->bind_param("s", $event_id);
              $event_revenue_query->execute();
              $event_revenue = $event_revenue_query->get_result()->fetch_assoc()["revenue"] ?? 0;

              $event_participants_query->bind_param("s", $event_id);
              $event_participants_query->execute();
              $event_participants = $event_participants_query->get_result()->fetch_assoc()["participants"];

              echo "<div class='ticket-card'>
              <div class='ticket-header'>
                <div>
                  <span class='ticket-badge badge-{$status}'>{$status}</span>
                </div>
                <div class='participant-count'>
                  <i class='bi bi-people-fill'></i>
                  <span>{$event_participants}</span>
                </div>
              </div>
              <h6>{$event_name}</h6>
              <div class='ticket-meta'>
                <span><i class='bi bi-calendar-event me-1'></i> {$event_start_date}</span>
                <span><i class='bi bi-clock me-1'></i> 9:00 AM - 5:00 PM</span>
                <span><i class='bi bi-geo-alt me-1'></i> {$event_venue}</span>
                <span><i class='bi bi-currency-rupee me-1'></i> Revenue: ₹{$event_revenue}</span>
              </div>
              <div class='mt-3 d-flex gap-2 flex-wrap'>
                {$edit}
                <form method='post' action='ParticipantsList.php'>
                  <input type='hidden' name='event_id' value='{$event_id}'>
                  <button type='submit' name='excel' class='btn-action' style='background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);' title='Download Excel'>
                    <i class='bi bi-file-earmark-excel'></i> Export Data
                  </button>
                </form>
              </div>
            </div>";
            }
            ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- NO UPCOMING EVENT BLOCK STARTS FROM HERE -->
      <?php if ($event_data_result->num_rows === 0): ?>
        <div class="container mt-3 mb-5 p-0">
          <div class="welcome-card ">
            <div class="row align-items-center">
              <div class="col-lg-9">
                <h2>No Active Events Yet 📅</h2>
                <p class="greeting mb-4">
                  You are not assigned to any active events currently open for registration. Once assigned, your active events will appear here.
                </p>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <?php include "../footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scroll Reveal Script -->
  <script>
    const reveals = document.querySelectorAll(".reveal");
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("visible");
          }
        });
      }, {
        threshold: 0.12
      },
    );
    reveals.forEach((el) => observer.observe(el));

    // Fullname pop-up on login 
    <?php if ($showWelcome): ?>
      window.onload = function() {
        setTimeout(function() {
          alert("Welcome <?php echo $name; ?>");
        }, 500);
      };
    <?php endif; ?>

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