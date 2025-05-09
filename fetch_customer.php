<?php
// Include database connection
include "config.php";

// Get search query and page number from the request
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Define number of results per page
$limit = 8;
$offset = ($page - 1) * $limit;

// Prepare search query condition
$searchCondition = $searchQuery ? "WHERE name LIKE '%$searchQuery%'" : "";

// Fetch total number of customers with the search condition
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM customers $searchCondition");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch customers with the search condition and pagination
$sql = "SELECT * FROM customers $searchCondition ORDER BY customer_id ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Generate HTML for customer table
$html = '';
if ($result->num_rows > 0) {
    $html .= '<table class="table table-bordered"><thead class="table-dark"><tr><th>#</th><th>Customer Name</th><th>Phone</th><th>Address</th><th>Description</th></tr></thead><tbody>';
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr><td>' . htmlspecialchars($row['customer_id']) . '</td><td>' . htmlspecialchars($row['name']) . '</td><td>' . htmlspecialchars($row['phone']) . '</td><td>' . htmlspecialchars($row['address']) . '</td><td>' . htmlspecialchars($row['description']) . '</td></tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<div class="alert alert-warning">No customers found.</div>';
}

// Generate pagination HTML
$pagination = '';
if ($totalPages > 1) {
    $pagination .= '<ul class="pagination justify-content-center">';
    if ($page > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="javascript:fetchCustomers(\'' . $searchQuery . '\', 1)">First</a></li>';
        $pagination .= '<li class="page-item"><a class="page-link" href="javascript:fetchCustomers(\'' . $searchQuery . '\', ' . ($page - 1) . ')">Previous</a></li>';
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $pagination .= '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="javascript:fetchCustomers(\'' . $searchQuery . '\', ' . $i . ')">' . $i . '</a></li>';
    }
    if ($page < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="javascript:fetchCustomers(\'' . $searchQuery . '\', ' . ($page + 1) . ')">Next</a></li>';
        $pagination .= '<li class="page-item"><a class="page-link" href="javascript:fetchCustomers(\'' . $searchQuery . '\', ' . $totalPages . ')">Last</a></li>';
    }
    $pagination .= '</ul>';
}

// Return response as JSON
echo json_encode(['html' => $html, 'pagination' => $pagination]);
?>
