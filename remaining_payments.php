<?php
// Include database connection
include "config.php";

// Debugging: Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search query if provided (Sanitize input)
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCondition = $searchQuery ? "AND c.name LIKE ?" : "";

// Define number of results per page
$limit = 8;

// Get current page number from URL, default is 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calculate offset for pagination
$offset = ($page - 1) * $limit;

// Get total number of customers with payments
$sql = "
    SELECT COUNT(DISTINCT c.customer_id) AS total
    FROM customers c
    JOIN orders o ON c.customer_id = o.customer_id
    WHERE 1 $searchCondition
";

$stmt = $conn->prepare($sql);
if ($searchQuery) {
    $param = "%$searchQuery%";
    $stmt->bind_param("s", $param);
}
$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $limit));
$stmt->close();

// Fetch customer payment data
$query = "
    SELECT 
        c.customer_id,
        c.name AS customer_name,
        c.phone,
        COALESCE(SUM(o.total_amount), 0) AS total_amount,
        COALESCE(SUM(o.paid_amount), 0) + COALESCE(SUM(p.payment_amount), 0) AS paid_amount,
        (COALESCE(SUM(o.total_amount), 0) - COALESCE(SUM(o.paid_amount), 0) - COALESCE(SUM(p.payment_amount), 0)) AS remaining_amount
    FROM customers c
    INNER JOIN orders o ON c.customer_id = o.customer_id  
    LEFT JOIN (
        SELECT customer_id, SUM(payment_amount) AS payment_amount 
        FROM payments 
        GROUP BY customer_id
    ) p ON c.customer_id = p.customer_id
    WHERE 1 $searchCondition
    GROUP BY c.customer_id, c.name, c.phone
    ORDER BY remaining_amount DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
if ($searchQuery) {
    $stmt->bind_param("sii", $param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Remaining Payments - POS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include "menu.php"; ?>

    <div class="main-content" id="mainContent">
        <?php include "header.php"; ?>

        <div class="content">
            <h2 class="mb-4">Customers with Remaining Payments</h2>

            <input type="text" id="search" class="form-control mb-4" placeholder="Search by customer name..." value="<?php echo htmlspecialchars($searchQuery); ?>">

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Remaining Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)) { ?>
                            <?php foreach ($customers as $customer) { ?>
                                <tr>
                                    <td><?php echo $customer['customer_id']; ?></td>
                                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo number_format($customer['total_amount'], 2); ?></td>
                                    <td><?php echo number_format($customer['paid_amount'], 2); ?></td>
                                    <td><?php echo number_format($customer['remaining_amount'], 2); ?></td>
                                    <td>
                                        <?php if ($customer['remaining_amount'] == 0) { ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php } else { ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($customer['remaining_amount'] > 0) { ?>
                                            <a href="payments.php?customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary btn-sm">Pay Now</a>
                                        <?php } else { ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Paid</button>
                                        <?php } ?>
                                        <a href="history.php?customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-history"></i> History
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="8" class="text-center text-danger">No remaining payments found</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1) { ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1) { ?>
                            <li class="page-item"><a class="page-link" href="?page=1&search=<?php echo htmlspecialchars($searchQuery); ?>">First</a></li>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($searchQuery); ?>">Previous</a></li>
                        <?php } ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($searchQuery); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php } ?>

                        <?php if ($page < $totalPages) { ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($searchQuery); ?>">Next</a></li>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo htmlspecialchars($searchQuery); ?>">Last</a></li>
                        <?php } ?>
                    </ul>
                </nav>
            <?php } ?>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="script.js"></script>

    <script>
        document.getElementById('search').addEventListener('input', function() {
            const searchQuery = this.value;
            window.location.href = "?page=1&search=" + encodeURIComponent(searchQuery);
        });
    </script>

</body>
</html>
