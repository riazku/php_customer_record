<?php
include 'config.php'; // Database connection

// Check if customer ID is provided
if (!isset($_GET['customer_id'])) {
    die('Customer ID is required.');
}

$customer_id = intval($_GET['customer_id']);

// Fetch customer name
$customer_query = "SELECT name FROM customers WHERE customer_id = ?";
$customer_stmt = $conn->prepare($customer_query);
$customer_stmt->bind_param('i', $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_name = $customer['name'] ?? 'Unknown';
$customer_stmt->close();

// Fetch order history
$order_query = "SELECT order_id, total_amount, paid_amount, order_date FROM orders WHERE customer_id = ? ORDER BY order_date ASC";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param('i', $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);
$order_stmt->close();

// Fetch payment history
$payment_query = "SELECT payment_amount, payment_date FROM payments WHERE customer_id = ? ORDER BY payment_date ASC";
$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param('i', $customer_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payments = $payment_result->fetch_all(MYSQLI_ASSOC);
$payment_stmt->close();

// Calculate total amounts
$total_ordered = 0;
$total_paid = 0;
foreach ($orders as $order) {
    $total_ordered += $order['total_amount'];
    $total_paid += $order['paid_amount'];
}
foreach ($payments as $payment) {
    $total_paid += $payment['payment_amount']; // Include additional payments
}
$remaining_balance = max(0, $total_ordered - $total_paid); // Ensure it doesn't go negative

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Payment & Order History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body>

<h2>Customer: <?= htmlspecialchars($customer_name) ?></h2>

<!-- Buttons Section -->
<div class="mb-3">
    <button class="btn btn-success me-2" onclick="printPage()">Print</button>
    <a href="./index.php" class="btn btn-primary">Dashboard</a>
</div>

<h2>Customer Order History</h2>
<table>
    <tr>
        <th>Order ID</th>
        <th>Total Amount</th>
        <th>Paid Amount</th>
        <th>Order Date</th>
    </tr>
    <?php foreach ($orders as $row) { ?>
        <tr>
            <td><?= $row['order_id'] ?></td>
            <td>$<?= number_format($row['total_amount'], 2) ?></td>
            <td>$<?= number_format($row['paid_amount'], 2) ?></td>
            <td><?= $row['order_date'] ?></td>
        </tr>
    <?php } ?>
</table>

<h2>Customer Payment History</h2>
<table>
    <tr>
        <th>Payment Amount</th>
        <th>Payment Date</th>
    </tr>
    <?php foreach ($payments as $row) { ?>
        <tr>
            <td>$<?= number_format($row['payment_amount'], 2) ?></td>
            <td><?= $row['payment_date'] ?></td>
        </tr>
    <?php } ?>
</table>

<h2>Remaining Balance</h2>
<table>
    <tr>
        <th>Total Remaining Balance</th>
    </tr>
    <tr>
        <td>$<?= number_format($remaining_balance, 2) ?></td>
    </tr>
</table>

</body>
</html>
