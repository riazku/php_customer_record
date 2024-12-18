<?php 
include 'header.php'; 
include 'config.php'; 

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];

    // Query to get customer details
    $customer_query = "SELECT name FROM customers WHERE id = ?";
    $customer_stmt = mysqli_prepare($conn, $customer_query);
    mysqli_stmt_bind_param($customer_stmt, 'i', $customer_id);
    mysqli_stmt_execute($customer_stmt);
    $customer_result = mysqli_stmt_get_result($customer_stmt);

    if (mysqli_num_rows($customer_result) > 0) {
        $customer = mysqli_fetch_assoc($customer_result);
        $customer_name = $customer['name'];
    } else {
        echo "<div class='alert alert-danger'>Customer not found.</div>";
        exit;
    }

    // Query to get payment history
    $payment_query = "
        SELECT 
            p.purchase_date, 
            p.total_amount, 
            p.paid_amount, 
            p.remaining_amount 
        FROM purchases p
        WHERE p.customer_id = ?
        ORDER BY p.purchase_date DESC
    ";

    $payment_stmt = mysqli_prepare($conn, $payment_query);
    mysqli_stmt_bind_param($payment_stmt, 'i', $customer_id);
    mysqli_stmt_execute($payment_stmt);
    $payment_result = mysqli_stmt_get_result($payment_stmt);

} else {
    echo "<div class='alert alert-danger'>Invalid customer ID.</div>";
    exit;
}

mysqli_stmt_close($payment_stmt);
mysqli_stmt_close($customer_stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - <?php echo htmlspecialchars($customer_name); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Payment History for <?php echo htmlspecialchars($customer_name); ?></h2>

    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Purchase Date</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Remaining Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($payment_result) > 0) {
                    while ($row = mysqli_fetch_assoc($payment_result)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['purchase_date']) . "</td>
                            <td>" . htmlspecialchars(number_format($row['total_amount'], 2)) . "</td>
                            <td>" . htmlspecialchars(number_format($row['paid_amount'], 2)) . "</td>
                            <td>" . htmlspecialchars(number_format($row['remaining_amount'], 2)) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No payment history found for this customer.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <a href="view.php" class="btn btn-secondary">Back to Customer View</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
