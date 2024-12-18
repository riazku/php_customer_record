<?php

include 'config.php'; // Database connection

$uploadDirectory = "uploads/"; // Directory to store uploaded receipt images

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $customerName = $_POST['name']; // Customer name
    $totalAmount = $_POST['total_amount']; // Total amount
    $paidAmount = $_POST['paid_amount']; // Paid amount
    $purchaseDate = $_POST['purchase_date']; // Purchase date
    
    $purchases = isset($_POST['purchases']) ? json_decode($_POST['purchases'], true) : []; // Purchase items

    // Calculate balance
    $balance = $totalAmount - $paidAmount;

    // Insert customer data into the customers table
    $customerQuery = "INSERT INTO customers (name) VALUES ('$customerName')";
    if (mysqli_query($conn, $customerQuery)) {
        $customerId = mysqli_insert_id($conn); // Get the inserted customer ID

        // Handle receipt upload
        $receiptImageName = null;
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == UPLOAD_ERR_OK) {
            $receipt = $_FILES['receipt'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB max file size

            if (in_array($receipt['type'], $allowedTypes) && $receipt['size'] <= $maxFileSize) {
                $receiptImageName = uniqid() . "_" . basename($receipt['name']);
                $targetPath = $uploadDirectory . $receiptImageName;
                move_uploaded_file($receipt['tmp_name'], $targetPath);
            }
        }

        // Insert the purchase details into the purchases table
        $purchaseQuery = "
            INSERT INTO purchases (customer_id, total_amount, paid_amount, remaining_amount, purchase_date, receipt_image)
            VALUES ('$customerId', '$totalAmount', '$paidAmount', '$balance', '$purchaseDate', '$receiptImageName')
        ";

        if (mysqli_query($conn, $purchaseQuery)) {
            $purchaseId = mysqli_insert_id($conn); // Get the inserted purchase ID

            // Insert each item in the purchase into the purchase_items table
            foreach ($purchases as $purchase) {
                $itemName = $purchase['itemName'];
                $price = $purchase['price'];
                $quantity = $purchase['quantity'];
                $total = $purchase['total'];

                // Insert item details into purchase_items table
                $itemQuery = "
                    INSERT INTO purchase_items (purchase_id, item_name, price, quantity, total)
                    VALUES ('$purchaseId', '$itemName', '$price', '$quantity', '$total')
                ";

                if (!mysqli_query($conn, $itemQuery)) {
                    echo "Error inserting item: " . mysqli_error($conn);
                }
            }

            echo "Data saved successfully!";
        } else {
            echo "Error saving purchase: " . mysqli_error($conn);
        }
    } else {
        echo "Error saving customer: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request method.";
}
?>
