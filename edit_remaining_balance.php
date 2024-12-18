<?php
include 'header.php';
include 'config.php';

// Get the customer_id from the URL
$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// Check if customer_id is valid
if ($customerId == 0) {
    echo "Invalid customer ID.";
    exit;
}

// Fetch customer details (optional)
$query = "SELECT name FROM customers WHERE id = '$customerId'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Customer not found.";
    exit;
}

$customer = mysqli_fetch_assoc($result);
mysqli_free_result($result);

// Get current remaining balance for the customer (from purchases)
$purchaseQuery = "
    SELECT remaining_amount, paid_amount 
    FROM purchases 
    WHERE customer_id = '$customerId' 
    AND remaining_amount > 0 
    LIMIT 1
";

$purchaseResult = mysqli_query($conn, $purchaseQuery);

if (!$purchaseResult || mysqli_num_rows($purchaseResult) == 0) {
    echo "No purchase records found or remaining balance is already 0.";
    exit;
}

$purchase = mysqli_fetch_assoc($purchaseResult);
$currentRemainingBalance = $purchase['remaining_amount'];  // Current remaining balance
$currentPaidAmount = $purchase['paid_amount'];  // Current paid amount
mysqli_free_result($purchaseResult);

// Handle form submission when updating the remaining balance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the new remaining balance from the form input
    $newRemainingBalance = $_POST['remaining_balance'];

    // Check if the new remaining balance is valid
    if ($newRemainingBalance < 0) {
        echo "Remaining balance cannot be negative.";
        exit;
    }

    // Update the remaining balance in the purchases table
    // Ensure that the paid amount remains unchanged
    $updatePurchaseQuery = "
        UPDATE purchases 
        SET remaining_amount = ?
        WHERE customer_id = ? 
        AND remaining_amount = ?
    ";

    // Prepare and execute the query
    if ($stmt = mysqli_prepare($conn, $updatePurchaseQuery)) {
        // Bind parameters and execute query
        mysqli_stmt_bind_param($stmt, "dii", $newRemainingBalance, $customerId, $currentRemainingBalance);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Remaining balance updated successfully!";
            // Redirect to customer view after successful update
            header("Location: view.php");
            exit;
        } else {
            echo "Error updating remaining balance: " . mysqli_error($conn);
        }

        // Close the prepared statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing query: " . mysqli_error($conn);
    }
}

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Remaining Balance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="text-center mt-4">Edit Remaining Balance</h2>

    <p><strong>Customer Name:</strong> <?php echo $customer['name']; ?></p>
    <p><strong>Customer ID:</strong> <?php echo $customerId; ?></p>

    <form action="edit_remaining_balance.php?customer_id=<?php echo $customerId; ?>" method="POST">
        <div class="mb-3">
            <label for="remaining_balance" class="form-label">Remaining Balance</label>
            <input type="number" class="form-control" id="remaining_balance" name="remaining_balance" value="<?php echo $currentRemainingBalance; ?>" step="0.01" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Remaining Balance</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
