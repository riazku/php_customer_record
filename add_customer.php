<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
if (!file_exists("config.php")) {
    die("Error: config.php file not found!");
}
include "config.php";

// Initialize variables for form submission
$message = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["customerName"]);
    $phone = trim($_POST["customerPhone"]);
    $address = trim($_POST["customerAddress"]);
    $description = trim($_POST["customerDescription"]);

    // Validate inputs
    if (empty($name) || empty($phone) || empty($address)) {
        $error = "Please fill in all required fields!";
    } else {
        // Prepare SQL query using prepared statements
        $stmt = $conn->prepare("INSERT INTO customers (name, phone, address, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $phone, $address, $description);

        if ($stmt->execute()) {
            $message = "Customer added successfully!";
        } else {
            $error = "Error adding customer: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Customer - POS Dashboard</title>
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

        <!-- Add Customer Content -->
        <div class="content">
            <h2 class="mb-4">Add New Customer</h2>

            <!-- Display success or error messages -->
            <?php if (!empty($message)) { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } elseif (!empty($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <div class="card p-4">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="customerPhone" name="customerPhone" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="customerAddress" name="customerAddress" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="customerDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="customerDescription" name="customerDescription" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </form>
            </div>
        </div>

    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>
</body>
</html>
