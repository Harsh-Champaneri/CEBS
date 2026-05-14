<?php

include "connection.php";

// For Back Arrow Button Page Redirection
if (isset($_SESSION["active_page"])) {
  $back_page = $_SESSION["active_page"];
}

$event_id = $_GET["event_id"];

// Event Data from event
$event_data = $connection->prepare("SELECT * FROM event WHERE event_id = ?");
$event_data->bind_param("s", $event_id);
$event_data->execute();
$result = $event_data->get_result();

$data = $result->fetch_assoc();

// Faculty Coordinator Data from faculty_coordinators
$faculty_data = $connection->prepare("SELECT users.firstname, users.lastname, users.contact_number FROM users INNER JOIN faculty_coordinators ON users.user_id = faculty_coordinators.user_id WHERE faculty_coordinators.event_id = ?");
$faculty_data->bind_param("s", $event_id);
$faculty_data->execute();
$faculty_data_result = $faculty_data->get_result();

// Student Coordinator Data from student_coordinators
$student_data = $connection->prepare("SELECT * FROM student_coordinators WHERE event_id = ?");
$student_data->bind_param("s", $event_id);
$student_data->execute();
$student_data_result = $student_data->get_result();


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Event Details – CEBS</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">

  <style>
    .dashboard-hero {
      padding: 50px 0 30px;
      /* Reduced gap */
    }

    .event-title {
      font-family: 'Playfair Display', serif;
      font-weight: 800;
      font-size: 2.3rem;
      margin-bottom: 6px;
    }

    .event-subtitle {
      font-size: .95rem;
      color: #64748b;
    }

    .event-detail-card {
      background: var(--card-bg);
      border: 1px solid var(--card-bdr);
      border-radius: 22px;
      padding: 28px;
      margin-bottom: 28px;
      backdrop-filter: blur(12px);
      box-shadow: 0 12px 35px rgba(14, 165, 233, .15);
      transition: .3s;
    }

    .event-detail-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(14, 165, 233, .25);
    }

    .section-heading {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      font-size: 1.4rem;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .detail-item {
      margin-bottom: 18px;
    }

    .detail-label {
      font-size: .72rem;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .detail-label i {
      color: #0ea5e9;
    }

    .detail-value {
      font-size: .95rem;
      font-weight: 500;
      color: #1e293b;
    }

    .coordinator-card {
      background: rgba(255, 255, 255, .75);
      border: 1px solid rgba(14, 165, 233, .25);
      border-radius: 18px;
      padding: 20px;
      height: 100%;
      transition: .3s;
      box-shadow: 0 8px 22px rgba(14, 165, 233, .12);
    }

    .coordinator-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 40px rgba(14, 165, 233, .22);
    }

    .name-label {
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #64748b;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .coordinator-name,
    .coordinator-phone {
      font-weight: 700;
      font-size: 1rem;
      margin-bottom: 12px;
    }

    .coordinator-detail {
      font-size: .85rem;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .coordinator-detail i {
      color: #0ea5e9;
    }

    .download-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      color: #fff;
      padding: 8px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: .8rem;
      font-weight: 600;
      box-shadow: 0 6px 18px rgba(14, 165, 233, .35);
    }

    .download-btn:hover {
      transform: translateY(-2px);
      color: #fff;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: var(--text-mute);
      font-size: 0.78rem;
      font-weight: 500;
      cursor: pointer;
      border: none;
      background: none;
      padding: 0;
      margin-bottom: 22px;
      transition: color 0.2s;
    }

    .back-btn:hover {
      color: var(--glow);
    }

    .back-btn i {
      font-size: 0.82rem;
    }
  </style>
</head>

<body>
  <?php
  $activePage = "event";
  $showNavbar = true;
  include "header.php";
  ?>
  <!-- Hero -->
  <section class="dashboard-hero">
    <div class="container">
      <section class="pb-1">
        <div class="welcome-card" style="display: flex; flex-direction: row; justify-content: space-between;">
          <div>
            <h2><?php echo $data["event_name"]; ?></h2>
            <p class="greeting">
              Complete overview of this event including coordinators, schedule, and registration details.
            </p>
          </div>
          <div style="align-self: flex-start;">
            <button type="button" class="back-btn" id="backArrowButton">
              <i class="bi bi-arrow-left"></i> Back to Home
            </button>
          </div>
        </div>
    </div>
    </div>
  </section>

  <div class="container">
    <!-- GENERAL DETAILS -->
    <div class="event-detail-card">
      <div class="section-heading">
        <i class="fa-solid fa-calendar text-primary"></i>
        Event General Information
      </div>

      <div class="row">

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-calendar-day"></i> Event Start Date</div>
          <div class="detail-value"><?php echo date("d F Y", strtotime($data["event_start_date"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-clock"></i> Event Start Time</div>
          <div class="detail-value"><?php echo date("H:i A", strtotime($data["event_start_time"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-calendar-day"></i> Event End Date</div>
          <div class="detail-value"><?php echo date("d F Y", strtotime($data["event_end_date"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-clock"></i> Event End Time</div>
          <div class="detail-value"><?php echo date("H:i A", strtotime($data["event_end_time"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-location-dot"></i> Venue</div>
          <div class="detail-value"><?php echo $data["event_venue"]; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-layer-group"></i> Category</div>
          <div class="detail-value"><?php echo $data["event_category"]; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-hourglass-start"></i> Registration Start Date</div>
          <div class="detail-value"><?php echo date("d F Y", strtotime($data["registration_start_date"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-clock"></i> Registration Start Time</div>
          <div class="detail-value"><?php echo date("H:i A", strtotime($data["registration_start_time"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-hourglass-start"></i> Registration End Date</div>
          <div class="detail-value"><?php echo date("d F Y", strtotime($data["registration_end_date"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-clock"></i> Registration End Time</div>
          <div class="detail-value"><?php echo date("H:i A", strtotime($data["registration_end_time"])); ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-sitemap"></i> Event Type</div>
          <div class="detail-value"><?php echo $data["event_type"] . " Event"; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-user-group"></i> Maximum Participants</div>
          <div class="detail-value"><?php echo $data["maximum_participants"] . " Participant"; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-indian-rupee-sign"></i> Registration Fee</div>
          <div class="detail-value">₹ <?php echo $data["registration_fee"]; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-building-columns"></i> Organized By</div>
          <div class="detail-value"><?php echo $data["organized_by"]; ?></div>
        </div>

        <div class="col-md-6 detail-item">
          <div class="detail-label"><i class="fa-solid fa-file-pdf"></i> Event Rules</div>
          <a href="<?php echo $data["event_rules"]; ?>" class="download-btn" target="_blank" download>
            <i class="fa-solid fa-download"></i> View PDF
          </a>
        </div>

      </div>
    </div>

    <!-- FACULTY COORDINATORS -->
    <div class="event-detail-card">
      <div class="section-heading">
        <i class="fa-solid fa-chalkboard-user text-primary"></i>
        Faculty Coordinators
      </div>

      <div class="row g-4">
        <?php
        while ($faculty_coordinator = $faculty_data_result->fetch_assoc()) {
          $fullname = $faculty_coordinator["firstname"] . " " . $faculty_coordinator["lastname"];
          $contact_number = $faculty_coordinator["contact_number"];

          echo "
            <div class='col-md-4'>
              <div class='coordinator-card'>
                <div class='name-label detail-label'><i class='fa-solid fa-user'></i> Name</div>
                  <div class='coordinator-name'>Prof. {$fullname}</div>
                <div class='name-label detail-label'><i class='fa-solid fa-phone'></i> Contact Number</div>
                  <div class='coordinator-phone'>{$contact_number}</div>
              </div>
            </div>
          ";
        }
        ?>
      </div>
    </div>

    <!-- STUDENT COORDINATORS -->
    <div class="event-detail-card mb-5">
      <div class="section-heading">
        <i class="fa-solid fa-user-group text-primary"></i>
        Student Coordinators
      </div>

      <div class="row g-4">
        <?php
        while ($student_coordinator = $student_data_result->fetch_assoc()) {
          $student_name = $student_coordinator["student_name"];
          $student_contact = $student_coordinator["student_contact"];
          echo "
              <div class='col-md-4'>
                <div class='coordinator-card'>
                  <div class='name-label detail-label'><i class='fa-solid fa-user'></i> Name</div>
                    <div class='coordinator-name'>{$student_name}</div>
                  <div class='name-label detail-label'><i class='fa-solid fa-phone'></i> Phone</div>
                    <div class='coordinator-phone'>{$student_contact}</div>
                </div>
              </div>
            ";
        }
        ?>
      </div>
    </div>

  </div>
  </section>

  <?php include "footer.php"; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Back Arrow Button Page Redirection
    let backToLogin = document.getElementById("backArrowButton");
    backToLogin.addEventListener("click", function() {
      window.location = "index.php";
    });
  </script>
</body>

</html>