<?php
include 'header.php';
include 'config.php';

// Get the customer_id and purchase_id from the URL
$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
$purchaseId = isset($_GET['purchase_id']) ? intval($_GET['purchase_id']) : 0;

// Check if both customer_id and purchase_id are valid
if ($customerId == 0 || $purchaseId == 0) {
    echo "Invalid customer or purchase ID. Please ensure the customer and purchase IDs are passed correctly in the URL.";
    exit;
}

// Fetch customer and purchase details
$query = "
    SELECT 
        c.name AS customer_name,
        p.id AS purchase_id,
        p.total_amount,
        p.paid_amount,
        p.remaining_amount
    FROM purchases p
    JOIN customers c ON p.customer_id = c.id
    WHERE p.customer_id = '$customerId' AND p.id = '$purchaseId'
";

$result = mysqli_query($conn, $query);

// Check if the query was successful and if data was found
if (!$result || mysqli_num_rows($result) == 0) {
    echo "Purchase not found for the given customer and purchase ID. Please ensure that the customer and purchase IDs are valid.";
    exit;
}

$purchase = mysqli_fetch_assoc($result);
$remainingAmount = $purchase['remaining_amount'];  // Current remaining balance

// Handle form submission when a payment is made
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transactionAmount = $_POST['transaction_amount'];  // The amount paid
    $paymentDate = $_POST['payment_date'];               // Payment date
    $paymentMethod = $_POST['payment_method'];           // Payment method (Cash, Card, etc.)

    // Check if the payment amount does not exceed the remaining balance
    if ($transactionAmount <= $remainingAmount) {
        // Calculate new remaining balance
        $newRemainingBalance = $remainingAmount - $transactionAmount;

        // Insert the transaction into the transactions table
        $transactionQuery = "
            INSERT INTO transactions (purchase_id, customer_id, paid_amount, payment_date, remaining_balance, payment_method)
            VALUES ('$purchaseId', '$customerId', '$transactionAmount', '$paymentDate', '$newRemainingBalance', '$paymentMethod')
        ";

        if (mysqli_query($conn, $transactionQuery)) {
            // Update the remaining amount in the purchases table
            $updatePurchaseQuery = "
                UPDATE purchases
                SET remaining_amount = '$newRemainingBalance', paid_amount = paid_amount + '$transactionAmount'
                WHERE id = '$purchaseId'
            ";

            if (mysqli_query($conn, $updatePurchaseQuery)) {
                echo "Remaining bill paid successfully!";
                // Optionally, redirect after success
                // header("Location: customer_history.php?customer_id={$customerId}");
                // exit;
            } else {
                echo "Error updating purchase: " . mysqli_error($conn);
            }
        } else {
            echo "Error recording transaction: " . mysqli_error($conn);
        }
    } else {
        echo "The payment amount exceeds the remaining balance.";
    }

    // Close the connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Remaining Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="text-center mt-4">Pay Remaining Bill</h2>

    <p><strong>Customer Name:</strong> <?php echo $purchase['customer_name']; ?></p>
    <p><strong>Customer ID:</strong> <?php echo $customerId; ?></p>
    <p><strong>Total Bill:</strong> <?php echo $purchase['total_amount']; ?></p>
    <p><strong>Remaining Bill:</strong> <?php echo $purchase['remaining_amount']; ?></p>
    <p><strong>Paid Bill:</strong> <?php echo $purchase['paid_amount']; ?></p>

    <form action="add_transaction.php?customer_id=<?php echo $customerId; ?>&purchase_id=<?php echo $purchaseId; ?>" method="POST">
        <div class="mb-3">
            <label for="transaction_amount" class="form-label">Remaining Bill Payment</label>
            <input type="number" class="form-control" id="transaction_amount" name="transaction_amount" step="0.01" max="<?php echo $purchase['remaining_amount']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="payment_date" class="form-label">Payment Date</label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-control" id="payment_method" name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="Online">Online</option>
            </select>
         </div>

        <button type="submit" class="btn btn-primary w-100">Pay Remaining Bill</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
