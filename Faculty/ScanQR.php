<?php

session_start();
include "../connection.php";

if (isset($_SESSION["user_id"])) {
  $_SESSION["active_page"] = "ScanQR";  // Current Page Name

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

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Scan QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

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
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    crossorigin="anonymous" />

  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="../auth.css">

  <script src="https://unpkg.com/html5-qrcode"></script>

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      min-height: 100%;
      font-family: "DM Sans", sans-serif;
      color: var(--text-dark);
    }

    body {
      display: flex;
      flex-direction: column;
    }

    @keyframes floatOrb {

      0%,
      100% {
        transform: translate(0, 0) scale(1);
      }

      50% {
        transform: translate(30px, -40px) scale(1.08);
      }
    }

    .scanner-wrapper {
      width: 100%;
      max-width: 520px;
      position: relative;
      z-index: 1;
      align-self: center;
      margin-top: 30px;
      margin-bottom: 30px;
    }

    .scanner-card {
      background: rgba(255, 255, 255, 0.85);
      border: 1px solid rgba(14, 165, 233, 0.2);
      border-radius: 24px;
      backdrop-filter: blur(12px);
      padding: 40px 32px;
      box-shadow: 0 20px 50px rgba(14, 165, 233, 0.2);
      position: relative;
      z-index: 1;
    }

    /* Header */
    .scanner-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .logo-container {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .scanner-logo {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, #f43f5e, #e11d48);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: white;
      box-shadow: 0 8px 24px rgba(244, 63, 94, 0.4);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }
    }

    .scanner-header h2 {
      font-family: "Playfair Display", serif;
      font-weight: 800;
      font-size: 1.8rem;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .scanner-header p {
      color: #475569;
      font-size: 0.9rem;
      font-weight: 300;
      line-height: 1.6;
    }

    .header-divider {
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, var(--glow), var(--glow-lt));
      border-radius: 3px;
      margin: 16px auto 0;
    }

    /* Scanner Box */
    #reader {
      width: 100%;
      border-radius: 16px;
      overflow: hidden;
      border: 3px dashed rgba(14, 165, 233, 0.4);
      background: rgba(255, 255, 255, 0.5);
      padding: 8px;
      margin-bottom: 20px;
      transition: border-color 0.3s;
    }

    #reader:hover {
      border-color: rgba(14, 165, 233, 0.7);
    }

    /* Override html5-qrcode default styles */
    #reader video {
      border-radius: 12px;
    }

    #reader__scan_region {
      border-radius: 12px !important;
    }

    #html5-qrcode-button-camera-start,
    #html5-qrcode-button-camera-permission,
    #html5-qrcode-button-camera-stop {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #f43f5e, #e11d48);
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 14px 32px;
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none;
      box-shadow: 0 6px 28px rgba(244, 63, 94, 0.4);
      transition:
        transform 0.25s,
        box-shadow 0.25s;
      animation: fadeUp 0.8s ease 0.3s both;
      margin-bottom: 20px;
    }

    #reader__camera_permission_button {
      background: linear-gradient(135deg, #0ea5e9, #0284c7) !important;
      color: white !important;
      border: none !important;
      padding: 12px 24px !important;
      border-radius: 12px !important;
      font-family: "DM Sans", sans-serif !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      box-shadow: 0 4px 16px rgba(14, 165, 233, 0.4) !important;
      transition: transform 0.2s !important;
    }

    #reader__camera_permission_button:hover {
      transform: translateY(-2px) !important;
    }

    /* Instructions */
    .scan-instructions {
      text-align: center;
      padding: 20px;
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.08),
          rgba(56, 189, 248, 0.04));
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .scan-instructions i {
      font-size: 2rem;
      color: var(--glow);
      margin-bottom: 12px;
      display: block;
    }

    .scan-instructions h5 {
      font-weight: 600;
      color: var(--text-dark);
      font-size: 1rem;
      margin-bottom: 8px;
    }

    .scan-instructions p {
      font-size: 0.85rem;
      color: var(--text-mute);
      margin: 0;
      line-height: 1.6;
    }

    /* Status Messages */
    .status-message {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: none;
      align-items: center;
      gap: 10px;
      font-size: 0.9rem;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .status-message i {
      font-size: 1.2rem;
    }

    .status-success {
      background: linear-gradient(135deg,
          rgba(16, 185, 129, 0.15),
          rgba(52, 211, 153, 0.08));
      color: #065f46;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .status-error {
      background: linear-gradient(135deg,
          rgba(239, 68, 68, 0.15),
          rgba(248, 113, 113, 0.08));
      color: #991b1b;
      border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .status-scanning {
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.15),
          rgba(56, 189, 248, 0.08));
      color: #0369a1;
      border: 1px solid rgba(14, 165, 233, 0.25);
    }

    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 12px;
      margin-top: 20px;
    }

    .btn-action {
      flex: 1;
      padding: 12px;
      border-radius: 12px;
      border: none;
      font-family: "DM Sans", sans-serif;
      font-weight: 600;
      font-size: 0.88rem;
      cursor: pointer;
      transition:
        transform 0.2s,
        box-shadow 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      color: #fff;
      box-shadow: 0 4px 16px rgba(14, 165, 233, 0.4);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(14, 165, 233, 0.6);
    }

    .btn-secondary {
      background: rgba(255, 255, 255, 0.8);
      color: var(--text-dark);
      border: 2px solid rgba(14, 165, 233, 0.2);
    }

    .btn-secondary:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 1);
    }

    /* Footer Link */
    .scanner-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 0.85rem;
      color: var(--text-mute);
    }

    .scanner-footer a {
      color: var(--glow);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .scanner-footer a:hover {
      color: var(--accent-lt);
    }

    /* Scan Result Card */
    .scan-result {
      display: none;
      margin-top: 20px;
      padding: 20px;
      background: linear-gradient(135deg,
          rgba(16, 185, 129, 0.08),
          rgba(52, 211, 153, 0.04));
      border: 1px solid rgba(16, 185, 129, 0.2);
      border-radius: 12px;
    }

    .scan-result h5 {
      font-weight: 600;
      color: var(--success);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .scan-result-data {
      background: rgba(255, 255, 255, 0.6);
      padding: 12px;
      border-radius: 8px;
      font-family: "Courier New", monospace;
      font-size: 0.85rem;
      color: var(--text-dark);
      word-break: break-all;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .scanner-card {
        padding: 32px 24px;
      }

      .scanner-header h2 {
        font-size: 1.5rem;
      }

      .scanner-logo {
        width: 56px;
        height: 56px;
        font-size: 1.7rem;
      }
    }

    @media (max-width: 480px) {
      body {
        padding: 10px;
      }

      .scanner-card {
        padding: 24px 18px;
        border-radius: 18px;
      }

      .scanner-header h2 {
        font-size: 1.3rem;
      }

      .scanner-logo {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
      }

      .action-buttons {
        flex-direction: column;
      }

      .scan-instructions {
        padding: 16px;
      }
    }

    /* Loading Spinner */
    .spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    #reader {
      width: 100%;
      border-radius: 16px;
      overflow: hidden;
      border: 2px dashed var(--glow);
      padding: 8px;
      background: rgba(255, 255, 255, 0.6);
    }
  </style>
</head>

<body>
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
            <a class="nav-link active" href="ScanQR.php">Scan QR</a>
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

  <div class="scanner-wrapper">
    <div class="scanner-card">
      <!-- Header -->
      <div class="scanner-header">
        <div class="logo-container">
          <div class="scanner-logo">
            <i class="bi bi-qr-code-scan"></i>
          </div>
        </div>
        <h2>Event QR Scanner</h2>
        <p>Scan participant QR codes to verify entry and mark attendance</p>
        <div class="header-divider"></div>
      </div>

      <!-- Status Messages -->
      <div id="statusSuccess" class="status-message status-success">
        <i class="bi bi-check-circle-fill"></i>
        <div><strong>Success!</strong> QR code verified and processed.</div>
      </div>

      <div id="statusError" class="status-message status-error">
        <i class="bi bi-x-circle-fill"></i>
        <div><strong>Error!</strong> Invalid QR code or already scanned.</div>
      </div>

      <div id="statusScanning">
      </div>

      <!-- Instructions -->
      <div class="scan-instructions">
        <i class="bi bi-info-circle"></i>
        <h5>How to Scan</h5>
        <p>
          Position the QR code within the scanning area. The scanner will
          automatically detect and process valid codes.
        </p>
      </div>
      <div class="scanner-card">
        <h3>Scan QR Code</h3>

        <div id="reader"></div>

        <!-- Hidden POST form -->
        <form id="qrForm" method="POST" action="VerifyParticipants.php">
          <input type="hidden" name="qr_data" id="qr_data" />
        </form>
      </div>
      <div id="scanResult" class="scan-result">
        <h5>
          <i class="bi bi-check-circle-fill"></i>
          Scanned Data
        </h5>
        <div class="scan-result-data" id="resultData"></div>
      </div>
    </div>
  </div>
  <?php include "../footer.php"; ?>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  <?php if (isset($_GET["message"])): ?>
    window.onload = function() {
      setTimeout(function() {
        alert("<?php echo $_GET["message"]; ?>");
        window.history.replaceState(null, '', window.location.pathname);
      }, 100);
    };
  <?php endif; ?>

  let scanner;

  function onScanSuccess(decodedText) {
    scanner.clear(); // stop camera

    document.getElementById("qr_data").value = decodedText;
    document.getElementById("qrForm").submit();
  }

  scanner = new Html5QrcodeScanner("reader", {
    fps: 10,
    qrbox: 250,
  });

  scanner.render(onScanSuccess);

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

</html>