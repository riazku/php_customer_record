<?php 
include 'config.php';

// Handle the search functionality
$search_name = '';
if (isset($_POST['search'])) {
    $search_name = $_POST['search_name'];
    $stmt = $conn->prepare("SELECT * FROM Customers WHERE name LIKE :name");
    $stmt->execute([':name' => "%$search_name%"]);
} else {
    // Fetch all customers if no search is performed
    $stmt = $conn->prepare("SELECT * FROM Customers");
    $stmt->execute();
}

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        .search-container { margin-bottom: 20px; }
        .search-container input { padding: 8px; font-size: 16px; }
        .search-container button { padding: 8px; font-size: 16px; }
    </style>
</head>
<body>

    <h2>Customer List</h2>

    <!-- Customer search form -->
    <div class="search-container">
        <form method="POST" action="">
            <input type="text" name="search_name" placeholder="Enter Customer Name" value="<?= $search_name ?>" required>
            <button type="submit" name="search">Search</button>
        </form>
    </div>

    <!-- Customer Table -->
    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Previous Orders Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): 
                // Fetch the previous remaining balance for the customer
                $stmt = $conn->prepare("SELECT SUM(remaining_amount) as total_remaining FROM Orders WHERE customer_id = :customer_id");
                $stmt->execute([':customer_id' => $customer['customer_id']]);
                $order_balance = $stmt->fetch(PDO::FETCH_ASSOC);
                $remaining_balance = $order_balance['total_remaining'] ? $order_balance['total_remaining'] : 0;
            ?>
                <tr>
                    <td><?= $customer['customer_id']; ?></td>
                    <td><?= $customer['name']; ?></td>
                    <td>$<?= number_format($remaining_balance, 2); ?></td>
                    <td>
                        <!-- Order Button -->
                        <!-- <a href="order.php?customer_id=<?= $customer['customer_id']; ?>">
                            <button type="button">Place Order</button>
                        </a>
                         -->
                        <!-- Payment Button -->
                        <a href="payments.php?customer_id=<?= $customer['customer_id']; ?>">
                            <button type="button">Make Payment</button>
                        </a>

                        <!-- Payment Button -->
                        <a href="customer_history.php?customer_id=<?= $customer['customer_id']; ?>">
                            <button type="button">View History</button>
                        </a>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
