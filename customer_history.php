<?php
include 'config.php';

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];

    // Fetch customer details
    $stmt = $conn->prepare("SELECT * FROM Customers WHERE customer_id = :customer_id");
    $stmt->execute([':customer_id' => $customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo "<script>alert('Customer not found.'); window.location.href='customers.php';</script>";
        exit;
    }

    // Fetch orders for the customer
    $stmt = $conn->prepare("SELECT * FROM Orders WHERE customer_id = :customer_id");
    $stmt->execute([':customer_id' => $customer_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch payment history for the customer
    $stmt = $conn->prepare("SELECT * FROM Payments WHERE customer_id = :customer_id ORDER BY payment_date DESC");
    $stmt->execute([':customer_id' => $customer_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "<script>alert('Invalid request.'); window.location.href='customers.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Payment History</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        h2, h3 { margin-top: 20px; }
    </style>
</head>
<body>

    <h2>Payment History for <?= htmlspecialchars($customer['name']); ?></h2>

    <h3>Orders</h3>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Total Amount</th>
            <th>Paid Amount</th>
            <th>Remaining Amount</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['order_id']; ?></td>
                <td>$<?= number_format($order['total_amount'], 2); ?></td>
                <td>$<?= number_format($order['paid_amount'], 2); ?></td>
                <td>$<?= number_format($order['remaining_amount'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Payment History</h3>
    <table>
        <tr>
            <th>Payment ID</th>
            <th>Order ID</th>
            <th>Amount Paid</th>
            <th>Payment Date</th>
        </tr>
        <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?= $payment['payment_id']; ?></td>
                <td><?= $payment['order_id']; ?></td>
                <td>$<?= number_format($payment['amount_paid'], 2); ?></td>
                <td><?= $payment['payment_date']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="customers.php">Back to Customer List</a>

</body>
</html>
