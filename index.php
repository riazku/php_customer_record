<?php
// Include database connection
include "config.php";

// Fetch total customers
$query = "SELECT COUNT(*) AS total_customers FROM customers";
$result = $conn->query($query);
$total_customers = $result->fetch_assoc()['total_customers'] ?? 0;

// Fetch total orders
$query = "SELECT COUNT(*) AS total_orders FROM orders";
$result = $conn->query($query);
$total_orders = $result->fetch_assoc()['total_orders'] ?? 0;

// Fetch total amount (sum of order amounts)
$query = "SELECT SUM(total_amount) AS total_amount FROM orders";
$result = $conn->query($query);
$total_amount = $result->fetch_assoc()['total_amount'] ?? 0;

// Fetch total paid amount
$query = "SELECT SUM(payment_amount) AS paid_amount FROM payments";
$result = $conn->query($query);
$paid_amount = $result->fetch_assoc()['paid_amount'] ?? 0;

// Calculate remaining amount
$remaining_amount = $total_amount - $paid_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - POS System</title>
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

        <!-- Dashboard Overview -->
        <div class="container mt-4">
            <h2 class="mb-4 text-center">Dashboard Overview</h2>

            <div class="row">

                <!-- Total Customers -->
                <div class="col-md-4">
                    <div class="card text-white bg-primary shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fa fa-users fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Total Customers</h5>
                                <h2 class="mb-0"><?= number_format($total_customers) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="col-md-4">
                    <div class="card text-white bg-success shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fa fa-shopping-cart fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Total Orders</h5>
                                <h2 class="mb-0"><?= number_format($total_orders) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="col-md-4">
                    <div class="card text-white bg-warning shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fa fa-dollar-sign fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Total Amount</h5>
                                <h2 class="mb-0">$<?= number_format($total_amount, 2) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paid Amount -->
                <div class="col-md-6 mt-3">
                    <div class="card text-white bg-info shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fa fa-money-bill-wave fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Paid Amount</h5>
                                <h2 class="mb-0">$<?= number_format($paid_amount, 2) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remaining Amount -->
                <div class="col-md-6 mt-3">
                    <div class="card text-white bg-danger shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <i class="fa fa-exclamation-circle fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title">Remaining Amount</h5>
                                <h2 class="mb-0">$<?= number_format($remaining_amount, 2) ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>

</body>
</html>
