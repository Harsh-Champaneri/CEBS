<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {

  $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
  $stmt->bind_param("s", $_SESSION["user_id"]);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  $firstname = $data["firstname"];
  $lastname = $data["lastname"];
  $name = $firstname . " " . $lastname;

  $name_initials = $firstname[0] . $lastname[0];

  $page = null;
  $faculty_json = null;
  $students_json = null;

  if (isset($_GET["event_id"])) {
    if (isset($_SESSION["active_page"])) {
      $back_page = $_SESSION["active_page"];

      if ($_SESSION["active_page"] === "ManageEvents" || $_SESSION["active_page"] === "MyEvents") {
        $page = explode("E", $back_page)[0] . " E" . explode("E", $back_page)[1];
      }

      if ($_SESSION["active_page"] === "Dashboard") {
        $page = "Dashboard";
      }

      if ($_SESSION["active_page"] === "ViewParticipants") {
        $page = "View Participants";
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

    // Faculty Data from faculty_coordinators table
    $faculty_data = $connection->prepare("SELECT users.firstname, users.lastname, users.contact_number, users.user_id FROM users INNER JOIN faculty_coordinators ON users.user_id = faculty_coordinators.user_id WHERE faculty_coordinators.event_id = ?");
    $faculty_data->bind_param("s", $event_id);
    $faculty_data->execute();
    $faculty = $faculty_data->get_result();

    $selected_faculty_ids = [];
    while ($f_row = $faculty->fetch_assoc()) {
      $selected_faculty_ids[] = $f_row['user_id'];
    }
    $faculty_json = json_encode($selected_faculty_ids);

    // Student Data from student_coordinators table
    $student_data = $connection->prepare("SELECT * FROM student_coordinators WHERE event_id = ?");
    $student_data->bind_param("s", $event_id);
    $student_data->execute();
    $students_res = $student_data->get_result();
    $students_array = [];
    while ($s = $students_res->fetch_assoc()) {
      $students_array[] = $s;
    }
    $students_json = json_encode($students_array);
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
  <title>Add Event – CEBS</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />
  <!-- Sky Blush Theme -->
  <link rel="stylesheet" href="../auth.css">
  <link rel="stylesheet" href="../style.css">

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
      background: rgba(255, 255, 255, .6);
      border: 1px solid rgba(14, 165, 233, .15);
      margin-bottom: 18px;
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

  <!-- FORM CODE BEGINS HERE -->
  <div class="reg-card">
    <form id="eventForm" method="post" action="AddEventData.php" enctype="multipart/form-data">

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
                                                                    } ?>" name="event_name" id="event_name" placeholder="Event Name" required>
        </div>

        <!-- Event Venue -->
        <div>
          <label class="field-label">Event Venue</label>
          <div class="input-wrap">
            <i class="bi bi-geo-alt icon-left"></i>
            <input type="text" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                        echo $event_venue;
                                                                      } ?>" name="event_venue" id="event_venue" placeholder="Event Venue" required>
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
                                                                        } ?>" name="event_start_date" id="event_start_date" required>
            </div>
          </div>
          <div>
            <label class="field-label">Event Start Time</label>
            <div class="input-wrap">
              <i class="bi bi-clock icon-left"></i>
              <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $event_start_time;
                                                                        } ?>" name="event_start_time" id="event_start_time" required>
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
                                                                        } ?>" name="event_end_date" id="event_end_date" required>
            </div>
          </div>
          <div>
            <label class="field-label">Event End Time</label>
            <div class="input-wrap">
              <i class="bi bi-clock icon-left"></i>
              <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $event_end_time;
                                                                        } ?>" name="event_end_time" id="event_end_time" required>
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

        <!-- Registration Start -->
        <div class="row-2">
          <div>
            <label class="field-label">Registration Start Date</label>
            <div class="input-wrap">
              <i class="bi bi-calendar icon-left"></i>
              <input type="date" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $registration_start_date;
                                                                        } ?>" name="registration_start_date" id="registration_start_date" required>
            </div>
          </div>
          <div>
            <label class="field-label">Registration Start Time</label>
            <div class="input-wrap">
              <i class="bi bi-clock icon-left"></i>
              <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $registration_start_time;
                                                                        } ?>" name="registration_start_time" id="registration_start_time" required>
            </div>
          </div>
        </div>

        <!-- Registration End -->
        <div class="row-2">
          <div>
            <label class="field-label">Registration End Date</label>
            <div class="input-wrap">
              <i class="bi bi-calendar icon-left"></i>
              <input type="date" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $registration_end_date;
                                                                        } ?>" name="registration_end_date" id="registration_end_date" required>
            </div>
          </div>
          <div>
            <label class="field-label">Registration End Time</label>
            <div class="input-wrap">
              <i class="bi bi-clock icon-left"></i>
              <input type="time" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                          echo $registration_end_time;
                                                                        } ?>" name="registration_end_time" id="registration_end_time" required>
            </div>
          </div>
        </div>

        <!-- Fee -->
        <label class="field-label">Registration Fee</label>
        <div class="input-wrap">
          <i class="bi bi-currency-rupee icon-left"></i>
          <input type="number" class="form-ctrl form-control" value="<?php if (isset($_GET["event_id"])) {
                                                                        echo $registration_fee;
                                                                      } ?>" name="registration_fee" id="registration_fee" placeholder="0.00" required>
        </div>

        <!-- Rules PDF -->
        <label class="field-label">Event Rules (PDF) <?php if (isset($_GET["event_id"])) {
                                                        echo "(Optional)";
                                                      } ?></label>
        <div class="input-wrap">
          <i class="bi bi-filetype-pdf icon-left"></i>
          <input type="file" accept="application/pdf" class="form-ctrl form-control" name="event_rules" <?php if (!isset($_GET["event_id"])) {
                                                                                                          echo "required";
                                                                                                        } ?>>
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
          <button type="button" class="btn-submit w-auto px-4" onclick="nextStep(1)">Next</button>
        </div>
      </div>

      <!-- ===================================================== -->
      <!-- STEP 2 – FACULTY COORDINATOR -->
      <!-- ===================================================== -->
      <div class="screen" id="step2">

        <h2>Faculty Coordinator Details</h2>
        <p class="form-hint">Assign faculty coordinators for this event.</p>
        <div class="form-divider"></div>

        <label class="field-label">Select Number of Coordinators</label>
        <div class="select-wrap">
          <i class="bi bi-people icon-left"></i>
          <i class="bi bi-chevron-down chevron"></i>
          <select class="form-ctrl form-select form-control" id="facultyCount" name="facultyCount" required>
          </select>
        </div>

        <div id="facultyContainer">
        </div>

        <div class="step-actions">
          <button type="button" class="btn-submit w-auto px-4" onclick="prevStep(2)">Previous</button>
          <button type="button" class="btn-submit w-auto px-4" onclick="nextStep(2)">Next</button>
        </div>
      </div>

      <!-- ===================================================== -->
      <!-- STEP 3 – STUDENT COORDINATOR -->
      <!-- ===================================================== -->
      <div class="screen" id="step3">
        <h2>Student Coordinator Details</h2>
        <p class="form-hint">Add student coordinators responsible for the event.</p>
        <div class="form-divider"></div>

        <label class="field-label">Select Number of Student Coordinators</label>
        <div class="select-wrap">
          <i class="bi bi-people icon-left"></i>
          <i class="bi bi-chevron-down chevron"></i>
          <select class="form-ctrl form-select form-control" name="studentCount" id="studentCount" onchange="generateStudents()" required>
            <option value="">Select</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
          </select>
        </div>

        <div id="studentContainer"></div>

        <div class="step-actions">
          <button type="button" class="btn-submit w-auto px-4" onclick="prevStep(3)">Previous</button>
          <?php if (isset($_GET["event_id"])): ?>
            <input type="hidden" name="event_id" value="<?php echo $_GET["event_id"]; ?>">
            <button type="submit" name="update" class="btn-submit w-auto px-4" id="update">Update Event</button>
          <?php else: ?>
            <button type="submit" name="submit" class="btn-submit w-auto px-4" id="submit">Submit Event</button>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <div id="loadingOverlay">
    <div class="loader"></div>
    <?php
    if (isset($_GET["event_id"])) {
      echo "<p>Updating Event...</p>";
    } else {
      echo "<p>Creating Event...</p>";
    }
    ?>
    <small>⚠️ Please do not refresh or close this page.</small>
  </div>

  <!-- 🔹 Footer -->
  <?php include "../footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    // Success Message Alert
    <?php
    if (isset($_GET["successMsg"])) {
      echo "alert('$_GET[successMsg]');";
      echo "window.history.replaceState(null, '', window.location.pathname);";
    }
    ?>

    // Next Button
    function nextStep(step) {
      const current = document.getElementById("step" + step);
      const inputs = current.querySelectorAll("input, select");
      for (let input of inputs) {
        if (!input.checkValidity()) {
          input.reportValidity();
          alert("Please Fill all the Details.");
          return;
        }
      }
      current.classList.remove("active");
      document.getElementById("step" + (step + 1)).classList.add("active");
    }

    // Previous Button
    function prevStep(step) {
      document.getElementById("step" + step).classList.remove("active");
      document.getElementById("step" + (step - 1)).classList.add("active");
    }

    $(document).ready(function() {
      let savedMembers = "<?php if (isset($_GET["event_id"])) {
                            echo $maximum_participants;
                          } ?>";
      let savedType = "<?php if (isset($_GET["event_id"])) {
                          echo $event_type;
                        } ?>";

      $("#event_type").val(savedType).trigger("change");

      setTimeout(function() {
        $("#total_members").val(savedMembers);
      }, 100);
    });

    // Event type and Maximum Participants
    $("#event_type").change(function() {
      let eventType = $(this).val();

      $("#total_members").empty();

      if (eventType === "") {
        $("#total_members").append('<option value="">Select Maximum Participants</option>');
      }

      if (eventType === "Solo") {
        $("#total_members").append('<option value="">Select Maximum Participants</option>');
        $("#total_members").append('<option value="1">1</option>');
      }

      if (eventType === "Team") {
        $("#total_members").append('<option value="">Select Maximum Participants</option>');
        for (i = 2; i <= 5; i++) {
          $("#total_members").append(`<option value="${i}">${i}</option>`);
        }
      }
    });

    // For fetching the faculty name
    $(document).ready(function() {
      let numberOfCordinators = 5;
      let facultyOptions = "";

      $("#organized_by").change(function() {
        let organized_by = $(this).val();

        $("#facultyContainer").html("");
        $("#facultyCount").html('<option value="">Select</option>');

        if (organized_by === "") {
          return;
        }

        $.ajax({
          url: "FacultyCoordinatorData.php",
          type: "POST",
          data: {
            department: organized_by,
          },
          success: function(response) {
            console.log(response);
            let parts = response.split("|||");

            let facultyCount = parseInt(parts[0]);
            facultyOptions = parts[1];

            if (facultyCount > numberOfCordinators) {
              facultyCount = numberOfCordinators;
            }

            for (let i = 1; i <= facultyCount; i++) {
              $("#facultyCount").append(`<option value="${i}">${i}</option>`);
            }
          }
        });
      });

      // Generating Faculty Name Dropdown
      $("#facultyCount").change(function() {
        let number = $(this).val();
        let currentUserId = "<?php echo $_SESSION['user_id']; ?>";

        let facultyContainer = $("#facultyContainer");
        facultyContainer.html("");

        for (let i = 1; i <= number; i++) {
          let id = `faculty${i}`;
          let select = `<label class="field-label">Coordinator ${i}</label>
                        <div class="select-wrap">
                          <i class="bi bi-person icon-left"></i>
                          <i class="bi bi-chevron-down chevron"></i>
                          <select class="form-ctrl form-select form-control" name="faculty[]"  id="${id}" required>
                            <option value="">— Select Faculty —</option>
                            ${facultyOptions}
                          </select>
                        </div>
            `;
          facultyContainer.append(select);

          // ✅ Set selected ONLY for first dropdown
          if (i === 1) {
            facultyContainer.find("select").last().val(currentUserId);
          }
        }
      });

      // Dropdown disable
      $(document).on("mousedown", "#faculty1", function(e) {
        e.preventDefault();
      });

      // Faculty Selection Validation
      $(document).on("change", "select[name='faculty[]']", function() {

        let selectedValues = [];
        let isDuplicate = false;

        $("select[name='faculty[]']").each(function() {
          let val = $(this).val();

          if (val) {
            if (selectedValues.includes(val)) {
              isDuplicate = true;
              return false; // break loop
            }
            selectedValues.push(val);
          }
        });

        if (isDuplicate) {
          alert("This faculty is already selected!");
          $(this).val(""); // reset current dropdown
        }

        // $("#errorMsg").remove(); // remove old message
        // if (isDuplicate) {
        //   $(this).after('<div id="errorMsg" style="color:red;">Faculty already selected</div>');
        //   $(this).val("");
        // }
      });

      <?php if (isset($_GET["event_id"])): ?>
        // 1. Trigger the department change to get the list of faculty
        $("#organized_by").trigger('change');

        // 2. Wait for AJAX to finish, then set the count and selections
        $(document).ajaxStop(function() {
          const selectedFaculty = <?php echo $faculty_json; ?>;
          if (selectedFaculty.length > 0) {
            $("#facultyCount").val(selectedFaculty.length).trigger('change');

            // Set the values for each newly created dropdown
            $("#facultyContainer select").each(function(index) {
              $(this).val(selectedFaculty[index]);
            });
          }
          // Unbind ajaxStop so it doesn't trigger on every future request
          $(this).unbind("ajaxStop");
        });
      <?php endif; ?>
    });

    // For generating Student Coordinators Input Fields
    function generateStudents(existingData = null) {
      const count = existingData ? existingData.length : document.getElementById("studentCount").value;
      if (existingData) document.getElementById("studentCount").value = count;

      const container = document.getElementById("studentContainer");
      container.innerHTML = "";

      console.log(existingData);
      for (let i = 1; i <= count; i++) {
        let sName = existingData ? existingData[i - 1].student_name : "";
        let sContact = existingData ? existingData[i - 1].student_contact : "";

        container.innerHTML += `
            <div class="student-block">
        <h6 class="mb-3">Student ${i}</h6>
        <div class="row-2">
          <div>
            <label class="field-label">Student Name</label>
            <div class="input-wrap">
                <i class="bi bi-person icon-left"></i>
              <input type="text" placeholder="Student Name" value="${sName}" name="student[]" class="form-ctrl form-control studentName" required>
              <small class="studentNameError d-none"></small>
            </div>
          </div>
          <div>
            <label class="field-label">Contact Number</label>
            <div class="input-wrap">
                <i class="bi-telephone icon-left"></i>
              <input type="tel" placeholder="Contact Number" value="${sContact}" name="contact[]" class="form-ctrl form-control contactNumber" required>
              <small class="contactNumberError d-none"></small>
            </div>
          </div>
        </div>
      </div>`;
      }
    };

    // Trigger on load for Edit Mode
    <?php if (isset($_GET["event_id"])): ?>
      generateStudents(<?php echo $students_json; ?>);
    <?php endif; ?>

    // Contact Number Validation and Duplicate Prevention
    $(document).on("input", ".contactNumber", function() {
      const contact = $(this).val();
      const onlyDigits = /^[0-9]{10}$/;

      let errorElement = $(this).siblings(".contactNumberError");

      // 👉 Get all values in array
      let values = $(".contactNumber").map(function() {
        return $(this).val().trim();
      }).get();

      // 👉 Count duplicates
      let count = values.filter(v => v === contact && v !== "").length;

      if (contact.length === 0) {
        errorElement.text("Contact Number is required.").css("color", "red").removeClass("d-none");
      } else if (!onlyDigits.test(contact)) {
        errorElement.text("Contact number must be exactly 10 digits (numbers only).").css("color", "red").removeClass("d-none");
      } else if (count > 1) {
        errorElement.text("Duplicate contact number not allowed.").css("color", "red").removeClass("d-none");
      } else {
        errorElement.addClass("d-none");
      }
    });

    // Form Submission Handling
    $("#update, #submit").on("click", function(e) {
      if (
        $(".contactNumberError:not(.d-none)").length > 0
      ) {
        e.preventDefault();
        alert("Please fix the highlighted errors.");
      }
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
    <?php if (isset($_SESSION["active_page"]) && isset($_GET["event_id"])): ?>
      let backToLogin = document.getElementById("backButton");
      backToLogin.addEventListener("click", function() {
        window.location = "<?php echo $back_page; ?>.php";
      });
    <?php endif; ?>
  </script>
</body>

</html>