

<?php
// Include database connection
include "config.php";

// Get customer ID from URL
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : '';

// Initialize variables
$customer_name = "Unknown";
$remaining_balance = 0;

if ($customer_id) {
    // Fetch customer's name
    $query = "SELECT name FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customer_name = $row['name'];
    }
    $stmt->close();

    // Fetch total orders amount
    $query = "SELECT SUM(remaining_amount) AS total_remaining FROM orders WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_orders = $result->fetch_assoc()['total_remaining'] ?? 0;
    $stmt->close();

    // Fetch total payments made
    $query = "SELECT SUM(payment_amount) AS total_paid FROM payments WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_paid = $result->fetch_assoc()['total_paid'] ?? 0;
    $stmt->close();

    // Calculate remaining balance
    $remaining_balance = max(0, $total_orders - $total_paid);
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_date = $_POST['payment_date']; 

    if ($customer_id && $payment_amount > 0 && !empty($payment_date)) {
        $conn->begin_transaction();
        try {
            // Insert the payment record
            $insert_query = "INSERT INTO payments (customer_id, payment_amount, payment_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('ids', $customer_id, $payment_amount, $payment_date);
            $stmt->execute();
            $stmt->close();

            // Update the orders table to reflect the payment
            $update_query = "UPDATE orders SET remaining_amount = GREATEST(remaining_amount - ?, 0) WHERE customer_id = ? AND remaining_amount > 0 ORDER BY order_id ASC LIMIT 1";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('di', $payment_amount, $customer_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<p class='alert alert-success text-center'>Payment recorded successfully!</p>";
            
            // Refresh remaining balance calculation
            header("Location: ".$_SERVER['PHP_SELF']."?customer_id=".$customer_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p class='alert alert-danger text-center'>Error: Could not save payment.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Collect Payment - POS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Include Sidebar -->
    <?php include "menu.php"; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">

        <!-- Include Header -->
        <?php include "header.php"; ?>

        <!-- Collect Payment Form -->
        <div class="content">
            <h2 class="mb-4 text-center">Collect Remaining Payment</h2>

            <form method="POST" class="mx-auto p-4 rounded shadow-sm bg-light" style="width: 50%;">
                <label class="fw-bold">Customer Name:</label>
                <input type="text" value="<?= htmlspecialchars($customer_name) ?>" readonly class="form-control mb-3">

                <label class="fw-bold">Customer ID:</label>
                <input type="text" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>" required readonly class="form-control mb-3">

                <label class="fw-bold">Remaining Balance:</label>
                <input type="text" value="$<?= number_format($remaining_balance, 2) ?>" readonly class="form-control mb-3">

                <label class="fw-bold">Enter Payment Amount:</label>
                <input type="number" name="payment_amount" step="0.01" min="0.01" max="<?= $remaining_balance ?>" required class="form-control mb-3">

                <label class="fw-bold">Select Payment Date:</label>
                <input type="date" name="payment_date" required class="form-control mb-4">

                <button type="submit" class="btn btn-success w-100">Submit Payment</button>
            </form>
        </div>

    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>

</body>
</html>
