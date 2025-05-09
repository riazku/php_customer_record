<?php
include "config.php";

// Check if 'id' is set and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Prepare and execute the query
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode($product);
        } else {
            echo json_encode(["error" => "Product not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Database query failed"]);
    }
} else {
    echo json_encode(["error" => "Invalid product ID"]);
}
?>
