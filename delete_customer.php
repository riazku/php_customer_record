<?php
// Include database connection
include "config.php";

// Check if ID is provided
if (isset($_GET['id'])) {
    $customer_id = intval($_GET['id']);

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    
    if ($stmt->execute()) {
        // Redirect back to the customer list with a success message
        header("Location: view.php?message=Customer deleted successfully");
    } else {
        // Redirect with an error message
        header("Location: view.php?error=Failed to delete customer");
    }

    $stmt->close();
} else {
    // Redirect if no ID is provided
    header("Location: view.php?error=Invalid customer ID");
}

$conn->close();
?>
