<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
            max-width: 600px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn {
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Edit Customer</h2>

    <?php
    if (isset($_GET['customer_id'])) {
        $customer_id = $_GET['customer_id'];

        // Fetch customer details from the customers table
        $customer_query = "SELECT * FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($conn, $customer_query);
        mysqli_stmt_bind_param($stmt, 'i', $customer_id);
        mysqli_stmt_execute($stmt);
        $customer_result = mysqli_stmt_get_result($stmt);

        if ($customer_result && mysqli_num_rows($customer_result) > 0) {
            $customer = mysqli_fetch_assoc($customer_result);
        } else {
            echo "<div class='alert alert-danger'>Customer not found.</div>";
            exit;
        }

        // Fetch purchase details from the purchases table
        $purchase_query = "
            SELECT 
                COALESCE(SUM(total_amount), 0) AS total_purchased,
                COALESCE(SUM(paid_amount), 0) AS total_paid
            FROM purchases
            WHERE customer_id = ?
        ";

        $purchase_stmt = mysqli_prepare($conn, $purchase_query);
        mysqli_stmt_bind_param($purchase_stmt, 'i', $customer_id);
        mysqli_stmt_execute($purchase_stmt);
        $purchase_result = mysqli_stmt_get_result($purchase_stmt);
        $purchase_data = mysqli_fetch_assoc($purchase_result);

        // Get total purchased and total paid values
        $total_purchased = $purchase_data['total_purchased'];
        $total_paid = $purchase_data['total_paid'];

        mysqli_stmt_close($stmt);
        mysqli_stmt_close($purchase_stmt);
    } else {
        echo "<div class='alert alert-danger'>Invalid customer ID.</div>";
        exit;
    }

    // Handle form submission to update customer data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $total_purchased = $_POST['total_purchased'] ?? 0;
        $total_paid = $_POST['total_paid'] ?? 0;

        // Update the customer name in the customers table
        $update_customer_query = "UPDATE customers SET name = ? WHERE id = ?";
        $update_customer_stmt = mysqli_prepare($conn, $update_customer_query);
        mysqli_stmt_bind_param($update_customer_stmt, 'si', $name, $customer_id);

        if (mysqli_stmt_execute($update_customer_stmt)) {
            // Update the total_amount and paid_amount in the purchases table
            $update_purchase_query = "UPDATE purchases SET total_amount = ?, paid_amount = ? WHERE customer_id = ?";
            $update_purchase_stmt = mysqli_prepare($conn, $update_purchase_query);
            mysqli_stmt_bind_param($update_purchase_stmt, 'ddi', $total_purchased, $total_paid, $customer_id);

            if (mysqli_stmt_execute($update_purchase_stmt)) {
                // Redirect to view.php after successful update
                header('Location: view.php');
                exit; // Make sure to stop further execution after redirection
            } else {
                echo "<div class='alert alert-danger'>Error updating purchase details.</div>";
            }

            mysqli_stmt_close($update_purchase_stmt);
        } else {
            echo "<div class='alert alert-danger'>Error updating customer details.</div>";
        }

        mysqli_stmt_close($update_customer_stmt);
    }

    mysqli_close($conn);
    ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Customer Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="total_purchased">Total Purchased</label>
            <input type="number" step="0.01" id="total_purchased" name="total_purchased" class="form-control" 
                   value="<?php echo htmlspecialchars($total_purchased ?? 0); ?>" required>
        </div>

        <div class="form-group">
            <label for="total_paid">Total Paid</label>
            <input type="number" step="0.01" id="total_paid" name="total_paid" class="form-control" 
                   value="<?php echo htmlspecialchars($total_paid ?? 0); ?>" required>
        </div>

        <div class="form-group">
            <label for="remaining_balance">Remaining Balance</label>
            <input type="number" step="0.01" id="remaining_balance" name="remaining_balance" class="form-control" 
                   value="<?php echo htmlspecialchars(($total_purchased - $total_paid) ?? 0); ?>" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="view.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const totalPurchasedInput = document.getElementById('total_purchased');
    const totalPaidInput = document.getElementById('total_paid');
    const remainingBalanceInput = document.getElementById('remaining_balance');

    function updateRemainingBalance() {
        const totalPurchased = parseFloat(totalPurchasedInput.value) || 0;
        const totalPaid = parseFloat(totalPaidInput.value) || 0;
        remainingBalanceInput.value = (totalPurchased - totalPaid).toFixed(2);
    }

    totalPurchasedInput.addEventListener('input', updateRemainingBalance);
    totalPaidInput.addEventListener('input', updateRemainingBalance);
</script>
</body>
</html>
