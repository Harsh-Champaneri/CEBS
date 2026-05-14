<?php

include "../connection.php";

session_start();

if (isset($_SESSION["user_id"])) {
  $limit = 5;

  $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
  if ($page < 1) {
    $page = 1;
  }

  // For Month Filter
  $month = $_POST['month'] ?? '';
  $monthNum = !empty($month) ? (int)date("n", strtotime($month)) : '';

  // For Search Filter
  $search = $_POST['search'] ?? '';
  $searchLike = "%" . $search . "%";

  $offset = ($page - 1) * $limit;

  // Data from payments and event Tables
  $table_data_query = $connection->prepare("SELECT payments.*, event.event_name, event.cancelled FROM payments INNER JOIN event ON event.event_id = payments.event_id WHERE payments.user_id = ? 
                                            AND (? = '' OR MONTH(payments.transaction_time) = ?) 
                                            AND (? = '' OR event.event_name LIKE ? OR DATE_FORMAT(payments.transaction_time, '%d %M %Y') LIKE ? OR payments.amount LIKE ?)
                                            ORDER BY transaction_time ASC 
                                            LIMIT ? OFFSET ?");
  $table_data_query->bind_param("ssissssii", $_SESSION["user_id"], $month, $monthNum, $search, $searchLike, $searchLike, $searchLike, $limit, $offset);
  $table_data_query->execute();

  $table_data_result = $table_data_query->get_result();

  $table = "";
  $x = $offset + 1;

  if ($table_data_result->num_rows > 0) {
    while ($table_data = $table_data_result->fetch_assoc()) {
      $event_name = $table_data["event_name"];
      $transaction_time = date("jS F Y", strtotime($table_data["transaction_time"]));
      $amount = $table_data["amount"];
      $status = $table_data["status"];
      $invoice_id = $table_data["invoice_id"];

      $refund_status = null;

      if ($table_data["cancelled"] === "Yes" && $status === "captured") {
        // $status = "Refunded";
        // $class = "refund";
        $status = "SUCCESS";
        $class = "paid";
        $refund_status = "<td data-label='Status' style='text-align: center;'>
                  <span class='txn-status refund'>Refunded</span>
                </td>";
      } else if ($status === "captured") {
        $status = "SUCCESS";
        $class = "paid";
        $refund_status = "<td data-label='Status' style='text-align: center;'>-</td>";
      } else if ($status === "FREE") {
        $class = "pending"; 
        $refund_status = "<td data-label='Status' style='text-align: center;'>-</td>";
      } else {
        $class = "failed";
        $refund_status = "<td data-label='Status' style='text-align: center;'>-</td>";
      }

      $table .= "<tr>
                <td class='txn-id' data-label='Transaction ID'>{$x}</td>
                <td data-label='Event'>{$event_name}</td>
                <td class='transaction-date' data-label='Date'>{$transaction_time}</td>
                <td class='amount' data-label='Amount'>₹{$amount}</td>
                <td data-label='Status'>
                  <span class='txn-status $class'>{$status}</span>
                </td>
                {$refund_status}
                <td class='text-center' data-label='Invoice'>
                  <form action='Invoice.php' method='post'>
                    <input type='hidden' name='invoice_id' value='$invoice_id'>
                    <button type='submit' name='invoice' class='invoice-btn' title='Download Invoice'><i class='bi bi-download'></i></button>
                  </form>
                </td>
              </tr>";
      $x++;
    }
  } else {
    $table .= "<tr>
                <td class='txn-id' data-label='Transaction ID'>-</td>
                <td data-label='Event'>-</td>
                <td data-label='Date'>-</td>
                <td class='amount' data-label='Amount'>-</td>
                <td data-label='Status'>
                  <span>-</span>
                </td>
                <td class='text-center' data-label='Invoice'>
                  <span>-</span>
                </td>
              </tr>";
  }

  /* TOTAL COUNT */
  $totalQuery = $connection->prepare("SELECT COUNT(*) as total FROM payments INNER JOIN event ON event.event_id = payments.event_id WHERE user_id = ? 
                                      AND (? = '' OR MONTH(payments.transaction_time) = ?)
                                      AND (? = '' OR event.event_name LIKE ? OR DATE_FORMAT(payments.transaction_time, '%d %M %Y') LIKE ? OR payments.amount LIKE ?)
                                      ");
  $totalQuery->bind_param("ssissss", $_SESSION["user_id"], $month, $monthNum, $search, $searchLike, $searchLike, $searchLike);
  $totalQuery->execute();
  $totalResult = $totalQuery->get_result()->fetch_assoc();

  $totalPages = ceil($totalResult['total'] / $limit);

  /* PAGINATION */
  $pagination = "";

  /* PREV */
  $prev = $page - 1;
  $disabled = ($page <= 1) ? "disabled" : "";

  $pagination .= "<li class='page-item $disabled'>
                    <a class='page-link' href='#' data-page='$prev'>«</a>
                    </li>";

  /* NUMBERS */
  for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $page) ? "active" : "";

    $pagination .= "<li class='page-item'>
                        <a class='page-link $active' href='#' data-page='$i'>$i</a>
                    </li>";
  }

  /* NEXT */
  $next = $page + 1;
  $disabled = ($page >= $totalPages) ? "disabled" : "";

  $pagination .= "<li class='page-item $disabled'>
                    <a class='page-link' href='#' data-page='$next'>»</a>
                </li>";

  echo json_encode([
    "table" => $table,
    "pagination" => $pagination
  ]);
}

?>