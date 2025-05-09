<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
if (!file_exists("config.php")) {
    die("Error: config.php file not found!");
}
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image_path = "";

    // Check if a new image is uploaded
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name; // Unique filename
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validate image file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Error: Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            die("Error: Failed to upload image.");
        }
    }

    // Update product in the database
    if ($image_path) {
        $query = "UPDATE products SET product_name = ?, price = ?, stock = ?, image_path = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdisi", $product_name, $price, $stock, $image_path, $product_id);
    } else {
        $query = "UPDATE products SET product_name = ?, price = ?, stock = ? WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdii", $product_name, $price, $stock, $product_id);
    }

    if ($stmt->execute()) {
        echo "Product updated successfully";
    } else {
        echo "Error updating product: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
