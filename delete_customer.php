<?php 
include 'header.php'; 
include 'config.php'; 

if (isset($_GET['customer_id'])) {
    $customer_id = $_GET['customer_id'];

    // Start the transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete related purchase records first
        $delete_purchase_query = "DELETE FROM purchases WHERE customer_id = ?";
        $delete_purchase_stmt = mysqli_prepare($conn, $delete_purchase_query);
        mysqli_stmt_bind_param($delete_purchase_stmt, 'i', $customer_id);
        mysqli_stmt_execute($delete_purchase_stmt);

        // Now delete the customer record
        $delete_customer_query = "DELETE FROM customers WHERE id = ?";
        $delete_customer_stmt = mysqli_prepare($conn, $delete_customer_query);
        mysqli_stmt_bind_param($delete_customer_stmt, 'i', $customer_id);
        mysqli_stmt_execute($delete_customer_stmt);

        // Commit the transaction if both deletes are successful
        mysqli_commit($conn);

        // Close prepared statements
        mysqli_stmt_close($delete_purchase_stmt);
        mysqli_stmt_close($delete_customer_stmt);

        // Redirect to the customer view page
        header('Location: view.php');
        exit;
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        mysqli_roll_back($conn);

        echo "<div class='alert alert-danger'>Error deleting customer. Please try again later.</div>";
    }

    // Close connection
    mysqli_close($conn);
} else {
    echo "<div class='alert alert-danger'>Invalid customer ID.</div>";
}
?>
