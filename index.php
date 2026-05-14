<?php

include "connection.php";
$event_data = $connection->prepare("SELECT * FROM event WHERE TIMESTAMP(event_start_date, event_start_time) >= NOW() ORDER BY event_start_date ASC, event_start_time ASC");
$event_data->execute();
$result = $event_data->get_result();

$colours = ["slate", "coral", "pine", "ocean", "azure", "indigo", "sky", "lavender", "violet", "orchid", "mauve", "rose", "blush", "cherry", "peach", "sunset", "tangerine", "apricot", "mint", "sage", "seafoam", "jade", "pearl"];

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CEBS</title>

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

  <link rel="stylesheet" href="style.css" />
  <style>
    /* Typing Cursor */
    .cursor {
      display: inline-block;
      margin-left: 4px;
      animation: blink 1s infinite;
      color: #fbbf24;
    }

    @keyframes blink {

      0%,
      50%,
      100% {
        opacity: 1;
      }

      25%,
      75% {
        opacity: 0;
      }
    }

    .btn-container {
      display: flex;
      justify-content: space-between;
    }

    #btn-red {
      background: linear-gradient(135deg, #f43f5e, #e11d48);
    }

    .feature-icon.icon-rose {
      background: linear-gradient(135deg,
          rgba(255, 99, 132, 0.25),
          rgba(255, 205, 210, 0.15));
      color: #b91c1c;
    }

    .feature-icon.icon-ice {
      background: linear-gradient(135deg,
          rgba(148, 163, 184, 0.286),
          rgba(207, 223, 244, 0.362));
      color: #334155;
    }

    .feature-icon.icon-honey {
      background: linear-gradient(135deg,
          rgba(234, 178, 8, 0.397),
          rgba(254, 240, 138, 0.264));
      color: #a16207;
    }

    .feature-icon.icon-green {
      background: linear-gradient(135deg,
          rgba(34, 197, 94, 0.414),
          rgba(167, 253, 197, 0.303));
      color: #166534;
    }
  </style>
  </style>
</head>

<body>
  <?php
  $activePage = "home";
  $showNavbar = true;
  include "header.php";
  ?>
  <!-- 📹 Hero -->
  <section class="hero text-center">
    <!-- floating orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="container hero-content">
      <!-- <h1>College Event Booking System</h1> -->
      <h1 class="typing-text">
        <span id="typing"></span>
        <span class="cursor"></span>
      </h1>

      <p class="sub mt-3">
        Developed by Department of Computer Science and Engineering<br />
        R.N.G Patel Institute of Technology
      </p>
      <a href="#events" class="btn-hero mt-4">
        <i class="bi bi-calendar-event"></i> View Upcoming Events
      </a>
    </div>
  </section>

  <section>
    <div class="stats-bar">
      <div class="container stats-grid">
        <div class="stat-item">
          <h3>50+</h3>
          <p>Annual Events</p>
        </div>
        <div class="stat-item">
          <h3>2000+</h3>
          <p>Students Active</p>
        </div>
        <div class="stat-item">
          <h3>12</h3>
          <p>Departments</p>
        </div>
        <div class="stat-item">
          <h3>100%</h3>
          <p>Secure Booking</p>
        </div>
      </div>
    </div>
  </section>

  <!-- 📹 Features -->
  <section class="py-5 reveal">
    <div class="container">
      <h2 class="text-center section-title mb-2">System Features</h2>
      <p class="text-center section-subtitle mb-5">
        Everything you need to discover, book, and attend events seamlessly.
      </p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-blue mx-auto">
              <i class="bi bi-calendar-event"></i>
            </div>
            <h5>Online Event Registration</h5>
            <p>
              Students can browse and register for college events through a
              centralized portal — fast, simple, and hassle-free.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-green mx-auto">
              <i class="bi bi-credit-card"></i>
            </div>
            <h5>Secure Payment Gateway</h5>
            <p>
              Integrated Razorpay payment gateway ensures fast and secure
              online transactions with multiple payment options.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-teal mx-auto">
              <i class="bi bi-qr-code"></i>
            </div>
            <h5>QR Code Based Entry</h5>
            <p>
              QR-enabled tickets allow quick and hassle-free verification at
              event entry points across campus.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-rose mx-auto">
              <i class="bi bi-bell-fill"></i>
            </div>
            <h5>Smart Notifications</h5>
            <p>
              Automated email and SMS alerts keep you informed about
              registrations, reminders, and important updates.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-honey mx-auto">
              <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h5>Analytics & Reports</h5>
            <p>
              Comprehensive dashboards and downloadable reports for tracking
              participants, revenue, and attendance.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box text-center">
            <div class="feature-icon icon-ice mx-auto">
              <i class="bi bi-person"></i>
            </div>
            <h5>Role-Based Access</h5>
            <p>
              Secure authentication with customized access levels for
              students, faculty, and administrators.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 📹 Events -->
  <section class="py-5 reveal" id="events">
    <div class="container">
      <h2 class="text-center section-title mb-2">Upcoming Events</h2>
      <p class="text-center section-subtitle mb-5">
        Don't miss out on what's happening next at RNGPIT.
      </p>

      <div class="row g-4">
        <!-- Event 1 -->
        <?php
        while ($data = $result->fetch_assoc()) {
          $event_id = $data["event_id"];
          $date = $data["event_start_date"];

          $day = date("d", strtotime($date));
          $month = date("F", strtotime($date));

          $event_name = $data["event_name"];

          $event_start_time = date("H:i A", strtotime($data["event_start_time"]));
          $event_end_time = date("H:i A", strtotime($data["event_end_time"]));

          $event_venue = $data["event_venue"];

          $registration_fee = $data["registration_fee"];

          $currentDateTime = date("Y-m-d H:i:s"); // Current Date and Time of the system

          $eventDateTimeStart = $data["event_start_date"] . ' ' . $data["event_start_time"];   // Event start date and time
          $eventDateTimeEnd = $data["event_end_date"] . ' ' . $data["event_end_time"];   // Event end date and time

          $eventRegistrationDateTimeStart = $data["registration_start_date"] . ' ' . $data["registration_start_time"]; // Event registration start date and time 
          $eventRegistrationDateTimeEnd = $data["registration_end_date"] . ' ' . $data["registration_end_time"]; // Event registration end date and time

          $book_event = "";

          if (strtotime($currentDateTime) > strtotime($eventRegistrationDateTimeEnd)) {
            $book_event = "";
          } else {
            if (strtotime($currentDateTime) >= strtotime($eventRegistrationDateTimeStart)) {
              $book_event = "<a href='' class='btn-book mt-3 bookEvent' id='btn-red'>
                                <i class='bi bi-calendar-plus'></i> Book Event
                              </a>";
            }
          }

          echo "
                        <div class='col-md-4'>
                    <div class='event-card'>
                        <div class='event-header eh-{$colours[array_rand($colours)]}'>
                            <div class='date-badge'>
                                <div class='day'>{$day}</div>
                                <div class='mon'>{$month}</div>
                            </div>
                        </div>
                        <div class='event-body'>
                            <h6>{$event_name}</h6>
                            <div class='event-meta'><i class='bi bi-clock'></i> {$event_start_time} – {$event_end_time}</div>
                            <div class='event-meta'><i class='bi bi-geo-alt'></i> {$event_venue}</div>
                            <div class='event-meta'><i class='bi bi-ticket'></i> ₹ {$registration_fee}</div>
                            <div class='btn-container'>
                                <a href='ViewDetails.php?event_id=$event_id' class='btn-book mt-3'>
                                    <i class='bi bi-eye'></i> View Details
                                </a>
                                {$book_event}
                            </div>
                        </div>
                    </div>
                </div>
                    ";
        }
        ?>
      </div>
    </div>
  </section>

  <?php include "footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Demo - 1 -->
  <script>
    const text = "College Event Booking System";
    let index = 0;
    let isDeleting = false;
    const typingElement = document.getElementById("typing");

    function typeLoop() {
      if (!isDeleting) {
        typingElement.textContent = text.substring(0, index++);
        if (index > text.length) {
          isDeleting = true;
          setTimeout(typeLoop, 1500);
          return;
        }
      } else {
        typingElement.textContent = text.substring(0, index--);
        if (index === 0) {
          isDeleting = false;
        }
      }
      setTimeout(typeLoop, isDeleting ? 40 : 70);
    }

    typeLoop();
  </script>

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

    // Book Event Alert
    let bookEvent = document.querySelectorAll(".bookEvent");
    bookEvent.forEach(function(btn) {
      btn.addEventListener("click", function(e) {
        e.preventDefault();
        alert("Login to Book Event.");
      });
    });
  </script>
</body>

</html>