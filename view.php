<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer View</title>

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
    <h2 class="text-center">Customer View</h2>

    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Total Purchased</th>
                    <th>Total Paid</th>
                    <th>Remaining Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to calculate total purchased, total paid, and remaining balance
                $query = "
                    SELECT 
                        customers.id AS customer_id,
                        customers.name AS customer_name,
                        COALESCE(SUM(purchases.total_amount), 0) AS total_purchased,
                        COALESCE(SUM(purchases.paid_amount), 0) AS total_paid,
                        (COALESCE(SUM(purchases.total_amount), 0) - COALESCE(SUM(purchases.paid_amount), 0)) AS remaining_balance
                    FROM customers
                    LEFT JOIN purchases ON customers.id = purchases.customer_id
                    GROUP BY customers.id
                ";

                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $statusClass = ($row['remaining_balance'] <= 0) ? 'text-success' : 'text-danger';

                        echo "<tr>
                            <td><a href='payment_history.php?customer_id={$row['customer_id']}'>{$row['customer_name']}</a></td>
                            <td>{$row['total_purchased']}</td>
                            <td>{$row['total_paid']}</td>
                            <td class='{$statusClass}'>{$row['remaining_balance']}</td>
                            <td>
                                <a href='edit_customer.php?customer_id={$row['customer_id']}' class='btn btn-primary btn-sm'>Edit</a>
                                <a href='delete_customer.php?customer_id={$row['customer_id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure you want to delete this customer?');\">Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No customers found.</td></tr>";
                }

                // Free result set
                mysqli_free_result($result);

                // Close database connection
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>