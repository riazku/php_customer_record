<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include "config.php";

// Ensure no output before JSON
ob_start();

try {
    // Read raw JSON input
    $json = file_get_contents("php://input");

    // Debug: Log received JSON
    error_log("Raw JSON Received: " . $json);

    // Decode JSON
    $data = json_decode($json, true);

    // Validate JSON decoding
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format: " . json_last_error_msg());
    }

    // Debug: Log decoded JSON
    error_log("Decoded JSON: " . print_r($data, true));

    // Validate required fields
    if (
        !isset($data['customerId']) || !is_numeric($data['customerId']) ||
        !isset($data['totalAmount']) || !is_numeric($data['totalAmount']) ||
        !isset($data['orderDate']) || empty($data['orderDate']) ||
        !isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0
    ) {
        throw new Exception("Missing or invalid required fields: customerId, totalAmount, orderDate, or items");
    }

    // Default paidAmount to 0 if not provided
    if (!isset($data['paidAmount']) || !is_numeric($data['paidAmount'])) {
        $data['paidAmount'] = 0;
    }

    // Calculate remainingAmount if not provided
    if (!isset($data['remainingAmount'])) {
        $data['remainingAmount'] = $data['totalAmount'] - $data['paidAmount'];
    }

    // Start database transaction
    $conn->begin_transaction();

    // Insert into `orders` table
    $stmt = $conn->prepare("INSERT INTO `orders` (customer_id, total_amount, paid_amount, remaining_amount, order_date) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iddds", $data['customerId'], $data['totalAmount'], $data['paidAmount'], $data['remainingAmount'], $data['orderDate']);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Process each order item
    foreach ($data['items'] as $item) {
        if (!isset($item['productId']) || !isset($item['quantity']) || !isset($item['price']) ||
            !is_numeric($item['productId']) || !is_numeric($item['quantity']) || !is_numeric($item['price'])) {
            throw new Exception("Invalid order item structure.");
        }

        // Check product stock
        $stmt = $conn->prepare("SELECT stock FROM `products` WHERE product_id = ?");
        $stmt->bind_param("i", $item['productId']);
        $stmt->execute();
        $stmt->bind_result($currentStock);
        $stmt->fetch();
        $stmt->close();

        if ($currentStock === null) {
            throw new Exception("Product ID " . $item['productId'] . " not found.");
        }

        error_log("Current stock for Product ID {$item['productId']}: {$currentStock}");

        if ($item['quantity'] > $currentStock) {
            throw new Exception("Insufficient stock for Product ID: " . $item['productId']);
        }

        // Insert into `order_items`
        $stmt = $conn->prepare("INSERT INTO `order_items` (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $orderId, $item['productId'], $item['quantity'], $item['price']);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();

        // Update stock
        $stmt = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE product_id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['productId']);
        if (!$stmt->execute()) {
            throw new Exception("Stock update failed: " . $stmt->error);
        }
        $stmt->close();

        // Verify updated stock
        $stmt = $conn->prepare("SELECT stock FROM `products` WHERE product_id = ?");
        $stmt->bind_param("i", $item['productId']);
        $stmt->execute();
        $stmt->bind_result($newStock);
        $stmt->fetch();
        $stmt->close();

        error_log("Updated stock for Product ID {$item['productId']}: {$newStock}");
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(["success" => true, "message" => "Order submitted successfully.", "orderId" => $orderId]);
} catch (Exception $e) {
    // Rollback transaction if error occurs
    if ($conn->in_transaction) {
        $conn->rollback();
    }

    error_log("Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    ob_end_flush();
}
?>
