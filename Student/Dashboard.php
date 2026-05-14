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

  $firstname = $data["firstname"];
  $lastname = $data["lastname"];
  $name = $firstname . " " . $lastname;

  $name_initials = $firstname[0] . $lastname[0];

  $showWelcome = false;
  if (empty($_SESSION['welcome_shown'])) {
    $showWelcome = true;
    $_SESSION['welcome_shown'] = true;
  }

  $event_attended_query = $connection->prepare("SELECT COUNT(pid) AS attended FROM participants WHERE email = ? AND attended = 'Yes'");
  $event_attended_query->bind_param("s", $email);
  $event_attended_query->execute();
  $event_attended = $event_attended_query->get_result()->fetch_assoc()["attended"];

  $total_booking_query = $connection->prepare("SELECT COUNT(id) AS total_booking FROM payments WHERE user_id = ? AND(status = 'FREE' OR status = 'captured')");
  $total_booking_query->bind_param("s", $_SESSION["user_id"]);
  $total_booking_query->execute();
  $total_booking = $total_booking_query->get_result()->fetch_assoc()["total_booking"];

  // $participants_data_query = $connection->prepare("SELECT * FROM participants WHERE email = ? LIMIT 3");
  $participants_data_query = $connection->prepare("SELECT participants.qrcode_url, event.* FROM participants INNER JOIN event ON event.event_id = participants.event_id WHERE participants.email = ? AND(TIMESTAMP(event_start_date, event_start_time) >= NOW()) AND event.cancelled = 'No' ORDER BY event.event_start_date ASC LIMIT 3");
  $participants_data_query->bind_param("s", $email);
  $participants_data_query->execute();
  $participants_data_result = $participants_data_query->get_result();

  // Upcoming Event Count
  $upcoming_event_query = $connection->prepare("SELECT event.*, COUNT(*) AS upcoming_event FROM participants INNER JOIN event ON participants.event_id = event.event_id WHERE participants.email = ? AND TIMESTAMP(event_start_date, event_start_time) >= NOW() AND cancelled = 'No'");
  $upcoming_event_query->bind_param("s", $email);
  $upcoming_event_query->execute();
  $upcoming_event = $upcoming_event_query->get_result()->fetch_assoc()["upcoming_event"];

  $payment_data_query = $connection->prepare("SELECT payments.event_id, payments.status, payments.transaction_time, event.event_name FROM payments INNER JOIN event ON event.event_id = payments.event_id WHERE payments.email = ? ORDER BY payments.transaction_time DESC LIMIT 3");
  $payment_data_query->bind_param("s", $email);
  $payment_data_query->execute();
  $payment_data_result = $payment_data_query->get_result();
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
    #demo {
      width: 50%;
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
            <a class="nav-link active" href="Dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item dropdown-center dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" href="#events">Events</a>
            <ul class="dropdown-menu" style="border-radius: 14px; padding: 8px; min-width: 180px">
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="AllEvents.php"> <i class="fa-solid fa-calendar-days"></i>
                  All Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="UpcomingEvents.php"><i class="fa-solid fa-clock"></i>
                  Upcoming Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="MyEvents.php"><i class="fa-solid fa-id-badge"></i>
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
              role=" button"
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
            <h2>Welcome back, <?php echo $name; ?>! 👋</h2>
            <p class="greeting mb-3">
              Here's what's happening with your events today. You have
              <strong style="color: #0ea5e9"><?php echo $upcoming_event; ?> upcoming events</strong>
            </p>
            <div class="d-flex gap-2 flex-wrap">
              <a
                href="AllEvents.php"
                class="btn-hero"
                style="padding: 10px 24px; font-size: 0.85rem">
                <i class="bi bi-calendar-plus"></i> Book New Event
              </a>
              <a
                href="MyQRCodes.php"
                style="
                    padding: 10px 24px;
                    font-size: 0.85rem;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    background: rgba(14, 165, 233, 0.1);
                    color: #0284c7;
                    border-radius: 50px;
                    text-decoration: none;
                    font-weight: 600;
                    border: 1px solid rgba(14, 165, 233, 0.2);
                  ">
                <i class="bi bi-ticket-perforated"></i> View My Tickets
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
  <section class="py-4">
    <div class="container">
      <div class="row g-3">
        <div class="col-md-3 col-6">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(14, 165, 233, 0.2),
                    rgba(56, 189, 248, 0.1)
                  );
                  color: #0284c7;
                ">
              <i class="bi bi-ticket-perforated"></i>
            </div>
            <h4><?php echo $total_booking; ?></h4>
            <p>Total Bookings</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(244, 63, 94, 0.2),
                    rgba(251, 113, 133, 0.1)
                  );
                  color: #be123c;
                ">
              <i class="bi bi-calendar-check"></i>
            </div>
            <h4><?php echo $upcoming_event; ?></h4>
            <p>Upcoming Events</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(16, 185, 129, 0.2),
                    rgba(52, 211, 153, 0.1)
                  );
                  color: #047857;
                ">
              <i class="bi bi-check-circle"></i>
            </div>
            <h4><?php echo $event_attended; ?></h4>
            <p>Attended</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="quick-stat text-center">
            <div
              class="stat-icon mx-auto"
              style="
                  background: linear-gradient(
                    135deg,
                    rgba(251, 146, 60, 0.2),
                    rgba(253, 186, 116, 0.1)
                  );
                  color: #c2410c;
                ">
              <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <h4>0</h4>
            <p>Skill Sessions Attended</p>
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
              <i class="bi bi-search"></i>
            </div>
            <h5>My Events</h5>
            <p>
              View and manage the events you’ve registered for in one place
            </p>
            <a
              href="MyEvents.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #0ea5e9, #0284c7);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(14, 165, 233, 0.3);
                ">
              Explore <i class="bi bi-arrow-right"></i>
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
            <h5>My QR Codes</h5>
            <p>
              Access all your event entry passes and QR codes in one place
            </p>
            <a
              href="MyQRCodes.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #f43f5e, #e11d48);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(244, 63, 94, 0.3);
                ">
              View Passes <i class="bi bi-arrow-right"></i>
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
              <i class="bi bi-clock-history"></i>
            </div>
            <h5>My Transactions</h5>
            <p>Track your event payments, booking confirmations, and receipts.</p>
            <a
              href="MyTransactions.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #10b981, #059669);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
                ">
              View History <i class="bi bi-arrow-right"></i>
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
              <i class="bi bi-person-circle"></i>
            </div>
            <h5>My Profile</h5>
            <p>Update your personal information and password</p>
            <a
              href="MyProfile.php"
              class="btn-action mx-auto"
              style="
                  background: linear-gradient(135deg, #fb923c, #f97316);
                  color: #fff;
                  box-shadow: 0 4px 16px rgba(251, 146, 60, 0.3);
                ">
              Edit Profile <i class="bi bi-arrow-right"></i>
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
          <h2 class="section-title mb-2">My Upcoming Tickets</h2>
          <p class="section-subtitle mb-0">Events you're registered for</p>
        </div>
      </div>

      <?php if ($participants_data_result->num_rows > 0): ?>
        <div class="row">

          <div class="col-lg-8 mt-3">
            <?php
            while ($participants_data = $participants_data_result->fetch_assoc()) {
              $qrcode_url = $participants_data["qrcode_url"];

              $event_name = $participants_data["event_name"];
              $event_start_date = date("jS F Y", strtotime($participants_data["event_start_date"]));
              $event_start_time = date("H:i A", strtotime($participants_data["event_start_time"]));
              $event_venue = $participants_data["event_venue"];

              echo "<div class='ticket-card'>
              <div class='ticket-header'>
                <div>
                  <span class='ticket-badge badge-upcoming'>Upcoming</span>
                </div>
                <a href='{$qrcode_url}' target='_blank' style=' color: #0ea5e9; font-size: 0.9rem; text-decoration: none;'>
                  <i class='bi bi-qr-code'></i> View QR
                </a>
              </div>
              <h6>{$event_name}</h6>
              <div class='ticket-meta'>
                <span><i class='bi bi-calendar-event me-1'></i> {$event_start_date}</span>
                <span><i class='bi bi-clock me-1'></i> {$event_start_time}</span>
                <span><i class='bi bi-geo-alt me-1'></i> {$event_venue}</span>
              </div>
            </div>";
            }
            ?>

          </div>
        <?php endif; ?>

        <!-- Recent Activity Sidebar -->

        <?php if ($payment_data_result->num_rows > 0): ?>
          <div class="col-lg-4 ">
            <div class="feature-box">
              <h5 style="font-size: 1rem; margin-bottom: 16px">
                <i class="bi bi-bell text-primary me-2"></i> Recent Activity
              </h5>

              <div style="border-top: 2px solid rgba(14, 164, 233, 0.389); padding-top: 14px;">

                <?php
                $x = 1;
                $rows = $payment_data_result->num_rows;
                while ($payment_data = $payment_data_result->fetch_assoc()) {

                  if ($payment_data["status"] === "FREE" || $payment_data["status"] === "captured") {
                    $payment_status = "Payment Successful";
                  } else {
                    $payment_status = "Payment Failed";
                  }

                  $event_name = $payment_data["event_name"];

                  $transaction_time = $payment_data["transaction_time"];

                  $time = strtotime($transaction_time);
                  $current_time = time();
                  $diff = $current_time - $time;

                  $days = floor($diff / 86400);
                  $hours = floor(($diff % 86400) / 3600);
                  $minutes = floor(($diff % 3600) / 60);

                  if ($days > 0) {
                    $result_time = $days . " day" . ($days > 1 ? "s" : "") . " ago";
                  } elseif ($hours > 0) {
                    $result_time = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
                  } elseif ($minutes > 0) {
                    $result_time = $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
                  } else {
                    $result_time = "just now";
                  }

                  echo "<div class='mb-3'>
                  <p style='margin: 0; font-size: 0.8rem; color: #1e293b; font-weight: 600;'>{$payment_status}</p>
                  <p style='margin: 0; font-size: 0.75rem; color: #64748b'>{$event_name}</p>
                  <p style='margin: 0; font-size: 0.7rem; color: #94a3b8; margin-top: 2px;'>{$result_time}</p>
                </div>
                ";

                  if ($x < $rows) {
                    echo "<div class='mb-3' style='border-top: 2px solid rgba(14, 164, 233,0.233); padding-top: 12px;'>";
                    $x++;
                  }
                }
                ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        </div>

        <?php if ($participants_data_result->num_rows === 0): ?>
          <!-- NO UPCOMING EVENT BLOCK STARTS FROM HERE -->
          <div class="container mt-3 mb-5 p-0">
            <div class="welcome-card ">
              <div class="row align-items-center">
                <div class="col-lg-8">
                  <h2>No Upcoming Events Yet 🎟️</h2>
                  <p class="greeting mb-3">
                    You haven’t registered for any events yet. Explore upcoming events and secure your spot today.
                  </p>
                  <div class="d-flex gap-2 flex-wrap">
                    <a
                      href="AllEvents.php"
                      class="btn-hero"
                      style="padding: 10px 24px; font-size: 0.85rem">
                      <i class="bi bi-calendar-plus"></i> Browse Events
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
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