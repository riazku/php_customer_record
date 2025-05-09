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
    $productName = trim($_POST["productName"]);
    $productPrice = trim($_POST["productPrice"]);
    $productStock = trim($_POST["productStock"]);
    $productDescription = trim($_POST["productDescription"]);
    $productImage = "";

    // Validate inputs
    if (empty($productName) || empty($productPrice) || empty($productStock)) {
        $error = "Please fill in all required fields!";
    } else {
        // Handle image upload
        if (!empty($_FILES["productImage"]["name"])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $targetFile = $targetDir . basename($_FILES["productImage"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = array("jpg", "jpeg", "png", "gif");

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $targetFile)) {
                    $productImage = $targetFile;
                } else {
                    $error = "Error uploading image.";
                }
            } else {
                $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF allowed.";
            }
        }

        // Prepare SQL query using prepared statements
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, product_description, image_path) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $productName, $productPrice, $productStock, $productDescription, $productImage);
                
                if ($stmt->execute()) {
                    $message = "Product added successfully!";
                } else {
                    $error = "Error adding product: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Products - POS Dashboard</title>
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

        <!-- My Products Content -->
        <div class="content">
            <h2 class="mb-4">Add New Product</h2>

            <!-- Display success or error messages -->
            <?php if (!empty($message)) { ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php } elseif (!empty($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <div class="card p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="productName" required>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="productPrice" name="productPrice" required>
                    </div>
                    <div class="mb-3">
                        <label for="productStock" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="productStock" name="productStock" required>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="productDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>

            <!-- Display Uploaded Product Image -->
            <?php if (!empty($productImage)) { ?>
                <div class="mt-4">
                    <h4>Uploaded Product Image:</h4>
                    <img src="<?php echo htmlspecialchars($productImage); ?>" alt="Product Image" class="img-fluid" style="max-width: 200px;">
                </div>
            <?php } ?>
        </div>

    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>
</body>
</html>
