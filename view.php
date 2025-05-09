<?php
// Include database connection
include "config.php";

// Get search query if provided
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Define number of results per page
$limit = 8;

// Get current page number from URL, default is 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare search query if there's a search term
$searchCondition = $searchQuery ? "WHERE name LIKE '%$searchQuery%'" : "";

// Fetch total number of customers with the search condition
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM customers $searchCondition");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch customer data
$customerResult = $conn->query("SELECT * FROM customers $searchCondition LIMIT $offset, $limit");
$customers = [];
while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Customers - POS Dashboard</title>
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

        <!-- View Customers Content -->
        <div class="content">
            <h2 class="mb-4">View Customers</h2>

            <!-- Search Bar -->
            <input type="text" id="search" class="form-control mb-4" placeholder="Search by customer name..." value="<?php echo $searchQuery; ?>">

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customers) > 0) { ?>
                            <?php foreach ($customers as $customer) { ?>
                                <tr>
                                    <td><?php echo $customer['customer_id']; ?></td>
                                    <td><?php echo $customer['name']; ?></td>
                                    <td><?php echo $customer['phone']; ?></td>
                                    <td><?php echo $customer['address']; ?></td>
                                    <td><?php echo $customer['description']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="5">No customers found</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1&search=<?php echo $searchQuery; ?>">First</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $searchQuery; ?>">Previous</a>
                        </li>
                    <?php } ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $searchQuery; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>

                    <?php if ($page < $totalPages) { ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $searchQuery; ?>">Next</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&search=<?php echo $searchQuery; ?>">Last</a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>

    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>

    <script>
        // Function to fetch customer data based on search query and pagination
        document.getElementById('search').addEventListener('input', function() {
            const searchQuery = this.value;
            window.location.href = "?page=1&search=" + searchQuery;
        });
    </script>

</body>
</html>
