<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customers</title>

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
    <h2 class="text-center">Customer and Purchase Details</h2>

    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Purchase Date</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to fetch customer and purchase details
                $query = "
                    SELECT 
                        customers.id AS customer_id,
                        customers.name AS customer_name, 
                        purchases.item_name, 
                        purchases.price, 
                        purchases.quantity, 
                        purchases.total, 
                        purchases.purchase_date, 
                        purchases.receipt_image 
                    FROM purchases 
                    INNER JOIN customers ON purchases.customer_id = customers.id
                ";

                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>
                                <a href='customer_details.php?customer_id={$row['customer_id']}'>{$row['customer_name']}</a>
                            </td>
                            <td>{$row['item_name']}</td>
                            <td>{$row['price']}</td>
                            <td>{$row['quantity']}</td>
                            <td>{$row['total']}</td>
                            <td>{$row['purchase_date']}</td>
                            <td>";
                        if (!empty($row['receipt_image'])) {
                            echo "<a href='uploads/{$row['receipt_image']}' target='_blank'>View Receipt</a>";
                        } else {
                            echo "No Receipt";
                        }
                        echo "</td>
                            <td>
                                <form action='delete_customer.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='customer_id' value='{$row['customer_id']}'>
                                    <button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this customer?\")'>Delete</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No records found</td></tr>";
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
