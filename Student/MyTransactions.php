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

  // Data from payments and event Tables
  $table_data_query = $connection->prepare("SELECT payments.*, event.event_name FROM payments INNER JOIN event ON event.event_id = payments.event_id WHERE payments.user_id = ? ORDER BY transaction_time ASC");
  $table_data_query->bind_param("s", $_SESSION["user_id"]);
  $table_data_query->execute();

  $table_data_result = $table_data_query->get_result();

  $total_transaction = $table_data_result->num_rows;  // Total Transactions Count

  // Total Amount Spent
  $total_amount_spent_query = $connection->prepare("SELECT SUM(amount) AS amount FROM payments WHERE email = ? AND(status = 'captured' OR status = 'FREE') AND refund_status IS NULL");
  $total_amount_spent_query->bind_param("s", $email);
  $total_amount_spent_query->execute();
  $total_amount_spent = $total_amount_spent_query->get_result()->fetch_assoc()["amount"];

  if ($total_amount_spent === null) {
    $total_amount_spent = 0;
  }

  // Successful Payments Count
  $successful_payments_query = $connection->prepare("SELECT COUNT(amount) AS amount FROM payments WHERE email = ? AND(status = 'captured' OR status = 'FREE')");
  $successful_payments_query->bind_param("s", $email);
  $successful_payments_query->execute();
  $successful_payments = $successful_payments_query->get_result()->fetch_assoc()["amount"];
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
  <title>My Transactions - CEBS</title>

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
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    crossorigin="anonymous" />

  <link rel="stylesheet" href="../style.css" />

  <style>
    /* Transaction Page Specific Styles */
    .transactions-container {
      padding: 40px 0;
    }

    .page-header {
      margin-bottom: 32px;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
    }

    .page-header h1 {
      font-family: "Playfair Display", serif;
      font-weight: 700;
      color: #1e293b;
      font-size: 2rem;
      margin-bottom: 8px;
    }

    .page-header p {
      color: #64748b;
      font-size: 0.95rem;
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
      transition:
        transform 0.3s,
        box-shadow 0.3s;
    }

    .summary-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(14, 165, 233, 0.2);
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
      font-family: "Playfair Display", serif;
      font-size: 1.8rem;
      font-weight: 800;
      color: #1e293b;
      margin: 0;
    }

    .summary-card p {
      font-size: 0.8rem;
      color: #64748b;
      margin: 4px 0 0;
    }

    .table-card {
      background: var(--card-bg);
      border: 1px solid var(--card-bdr);
      border-radius: 18px;
      padding: 0;
      backdrop-filter: blur(12px);
      box-shadow: 0 10px 30px rgba(14, 165, 233, 0.15);
      overflow: hidden;
    }

    .table-header {
      padding: 24px 28px;
      border-bottom: 1px solid rgba(14, 165, 233, 0.15);
    }

    .table-header h5 {
      font-weight: 600;
      color: #1e293b;
      margin: 0;
      font-size: 1.1rem;
    }

    .transaction-table {
      margin: 0;
      background: transparent;
    }

    .transaction-table thead {
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.08),
          rgba(56, 189, 248, 0.04));
    }

    .transaction-table thead th {
      border: none;
      padding: 16px 20px;
      font-size: 0.8rem;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .transaction-table tbody tr {
      border-bottom: 1px solid rgba(14, 165, 233, 0.1);
      transition: background 0.2s;
    }

    .transaction-table tbody tr:hover {
      background: rgba(191, 219, 254, 0.15);
    }

    .transaction-table tbody tr:last-child {
      border-bottom: none;
    }

    .transaction-table tbody td {
      padding: 18px 20px;
      font-size: 0.9rem;
      color: #1e293b;
      vertical-align: middle;
      border: none;
    }

    .txn-id {
      font-family: "Courier New", monospace;
      font-weight: 600;
      color: #0ea5e9;
      font-size: 0.85rem;
    }

    .amount {
      font-weight: 700;
      color: #1e293b;
      font-size: 0.95rem;
    }

    .txn-status {
      display: inline-block;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .txn-status.paid {
      background: linear-gradient(135deg,
          rgba(16, 185, 129, 0.15),
          rgba(52, 211, 153, 0.08));
      color: #065f46;
      border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .txn-status.pending {
      background: linear-gradient(135deg,
          rgba(251, 146, 60, 0.15),
          rgba(253, 186, 116, 0.08));
      color: #9a3412;
      border: 1px solid rgba(251, 146, 60, 0.25);
    }

    .txn-status.failed {
      background: linear-gradient(135deg,
          rgba(239, 68, 68, 0.15),
          rgba(248, 113, 113, 0.08));
      color: #991b1b;
      border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .txn-status.refund {
      background: linear-gradient(135deg,
          rgba(14, 165, 233, 0.15),
          rgba(56, 189, 248, 0.08));
      color: #0369a1;
      border: 1px solid rgba(14, 165, 233, 0.25);
    }

    .invoice-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, #0ea5e9, #0284c7);
      color: #fff;
      border-radius: 10px;
      text-decoration: none;
      transition:
        transform 0.2s,
        box-shadow 0.2s;
      box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
      border: none;
    }

    .invoice-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(14, 165, 233, 0.45);
      color: #fff;
    }

    .invoice-btn i {
      font-size: 1rem;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state i {
      font-size: 4rem;
      color: #bfdbfe;
      margin-bottom: 20px;
    }

    .empty-state h5 {
      color: #64748b;
      font-size: 1.1rem;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #94a3b8;
      font-size: 0.9rem;
    }

    /* Mobile Responsive - Card View */
    @media (max-width: 768px) {
      .table-card {
        border-radius: 14px;
      }

      .table-header {
        padding: 20px;
      }

      /* Hide table, show card view */
      .transaction-table thead {
        display: none;
      }

      .transaction-table,
      .transaction-table tbody,
      .transaction-table tr,
      .transaction-table td {
        display: block;
        width: 100%;
      }

      .transaction-table tr {
        background: rgba(255, 255, 255, 0.4);
        margin-bottom: 16px;
        border-radius: 12px;
        border: 1px solid rgba(14, 165, 233, 0.15);
        padding: 16px;
      }

      .transaction-table tr:hover {
        background: rgba(191, 219, 254, 0.25);
      }

      .transaction-table td {
        padding: 8px 0;
        text-align: left;
        position: relative;
        padding-left: 50%;
      }

      .transaction-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 45%;
        font-weight: 700;
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .transaction-table td.text-center {
        text-align: left !important;
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
      .transactions-container {
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
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="AllEvents.php"> <i class="fa-solid fa-calendar-days"></i>
                  All Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="UpcomingEvents.php"><i class="fa-solid fa-clock"></i>
                  Upcoming Events</a></li>
              <li><a class="dropdown-item d-flex align-items-center gap-3 py-2 d-item" href="MyEvents.php"><i class="fa-solid fa-id-badge"></i>
                  My Events</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="MyTransactions.php">My Transactions</a>
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
  <!-- 🔹 Transactions Content -->
  <section
    class="transactions-container"
    id="event-section"
    style="position: relative; overflow: hidden">
    <!-- Floating orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1>
            <i class="bi bi-receipt me-2" style="color: #0ea5e9"></i>My
            Transactions
          </h1>
          <p>
            View all your event booking transactions and download invoices
          </p>
        </div>

        <div style="width: 30%; align-self: center;">
          <div class="container">
            <div class="event-search-wrapper">
              <i class="bi bi-search search-icon"></i>
              <input
                type="text"
                id="eventSearch"
                class="event-search-input"
                placeholder="Search for a transaction or event booking..." />
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="summary-card">
          <div
            class="icon"
            style="
                background: linear-gradient(
                  135deg,
                  rgba(14, 165, 233, 0.2),
                  rgba(56, 189, 248, 0.12)
                );
                color: #0284c7;
              ">
            <i class="bi bi-receipt-cutoff"></i>
          </div>
          <h3><?php echo $total_transaction; ?></h3>
          <p>Total Transactions</p>
        </div>

        <div class="summary-card">
          <div
            class="icon"
            style="
                background: linear-gradient(
                  135deg,
                  rgba(16, 185, 129, 0.2),
                  rgba(52, 211, 153, 0.12)
                );
                color: #047857;
              ">
            <i class="bi bi-check-circle"></i>
          </div>
          <h3><?php echo $successful_payments; ?></h3>
          <p>Successful Payments</p>
        </div>

        <div class="summary-card">
          <div
            class="icon"
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
          <h3>₹<?php echo $total_amount_spent; ?></h3>
          <p>Total Amount Spent</p>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="table-card ">
        <div class="table-header d-flex justify-content-between align-items-center">
          <h5><i class="bi bi-list-ul me-2"></i>Transaction History</h5>
          <select class="form-select" id="monthFilter" style="width: 20%;">
            <option value="" selected>---Select Month---</option>

            <?php
            // Distinct Month
            $month_query = $connection->prepare("SELECT DISTINCT DATE_FORMAT(transaction_time, '%M %Y') AS month_year FROM payments WHERE email = ? ORDER BY transaction_time");
            $month_query->bind_param("s", $email);
            $month_query->execute();

            $month_result = $month_query->get_result();

            if ($month_result->num_rows > 0) {
              while ($month = $month_result->fetch_assoc()) {
                $month_year = $month["month_year"];
                echo "<option value='$month_year'>$month_year</option>";
              }
            }
            ?>
          </select>
        </div>

        <div class="table-responsive">
          <table class="table transaction-table align-middle">
            <thead>
              <tr>
                <th>Sr. No.</th>
                <th>Event Name</th>
                <th>Transaction Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th style='text-align: center;'>Refund Status</th>
                <th class="text-center">Invoice</th>
              </tr>
            </thead>
            <tbody id="transactionTable"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="pagination-div">
      <ul class="pagination" id="pagination"></ul>
    </div>

    <div id="loadingOverlay">
      <div class="loader"></div>
      <p>Generating your invoice, please wait...</p>
      <small>⚠️ Please do not refresh or close this page.</small>
    </div>

  </section>

  <!-- 🔹 Footer -->
  <?php include "../footer.php"; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
    // Loader on Form Submit
    $(document).on("submit", "form", function(e) {
      const clickedButton = e.originalEvent.submitter;

      // 👉 Run ONLY for Excel & PDF buttons
      if (!clickedButton || (clickedButton.name !== "invoice")) {
        return; // skip loader
      }

      const overlay = document.getElementById("loadingOverlay");

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

    // Pagination
    $(document).ready(function() {

      function loadData(page = 1) {
        let month = $("#monthFilter").val();
        let search = $("#eventSearch").val();

        $.ajax({
          url: "MyTransactionsData.php",
          method: "POST",
          data: {
            page: page,
            month: month,
            search: search,
          },
          success: function(response) {
            console.log(response);
            let res = JSON.parse(response);

            $("#transactionTable").html(res.table);
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

      // Month Filter
      $("#monthFilter").on("change", function() {
        loadData(1);
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