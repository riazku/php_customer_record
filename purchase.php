<?php
// Connect to your database
$conn = new mysqli("localhost", "root", "", "customer");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Customer ID from query string
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if ($customer_id > 0) {
    // Fetch customer details
    $customer_sql = "SELECT * FROM customers WHERE id = $customer_id";
    $customer_result = $conn->query($customer_sql);
    $customer = $customer_result->fetch_assoc();

    // Fetch purchase history
    $history_sql = "SELECT * FROM purchase_history WHERE customer_id = $customer_id";
    $history_result = $conn->query($history_sql);
} else {
    die("Invalid customer ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
</head>
<body>
    <h1>Purchase History for <?= htmlspecialchars($customer['name']) ?></h1>
    <table>
        <thead>
            <tr>
                <th>Purchase ID</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Purchase Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($history_result->num_rows > 0): ?>
                <?php while($row = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['purchase_id'] ?></td>
                        <td><?= $row['item_name'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td><?= number_format($row['quantity'] * $row['price'], 2) ?></td>
                        <td><?= $row['purchase_date'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No purchase history found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="index.php">Back to Customer List</a>
</body>
</html>

<?php
$conn->close();
?>
