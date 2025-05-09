<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
if (!file_exists("config.php")) {
    die("Error: config.php file not found!");
}
include "config.php";

// Fetch products from database
$query = "SELECT * FROM products";
$result = $conn->query($query);
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <h2 class="mb-4">Product List</h2>
            
            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search products by name..." onkeyup="filterProducts()">
            </div>

            <div class="row" id="productList">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="col-md-3 product-item" data-name="<?php echo strtolower($row['product_name']); ?>" id="product-<?php echo $row['product_id']; ?>">
                        <div class="card mb-3" style="width: 14rem;">
                            <img src="<?php echo htmlspecialchars($row['image_path']) . '?t=' . time(); ?>" class="card-img-top" alt="Product Image" style="height: 120px; object-fit: cover;">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?php echo htmlspecialchars($row['product_name']); ?></h6>
                                <p class="card-text"><strong>Price:</strong> $<?php echo htmlspecialchars($row['price']); ?></p>
                                <p class="card-text"><strong>Stock:</strong> <?php echo htmlspecialchars($row['stock']); ?></p>
                                <button class="btn btn-sm btn-primary edit-product" data-id="<?php echo $row['product_id']; ?>"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $row['product_id']; ?>"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" enctype="multipart/form-data">
                        <input type="hidden" id="editProductId" name="product_id">
                        <div class="mb-3">
                            <label for="editProductName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editProductName" name="product_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductPrice" class="form-label">Price</label>
                            <input type="text" class="form-control" id="editProductPrice" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductStock" class="form-label">Stock</label>
                            <input type="text" class="form-control" id="editProductStock" name="stock" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProductImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="editProductImage" name="image">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filter products based on search input
        function filterProducts() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var productList = document.getElementById("productList");
            var products = productList.getElementsByClassName("product-item");

            for (var i = 0; i < products.length; i++) {
                var productName = products[i].getAttribute("data-name");
                if (productName.indexOf(filter) > -1) {
                    products[i].style.display = "";
                } else {
                    products[i].style.display = "none";
                }
            }
        }

        $(document).ready(function() {
            $('.delete-product').click(function() {
                if (confirm('Are you sure you want to delete this product?')) {
                    var id = $(this).data('id');
                    $.post('delete_product.php', { id: id }, function(response) {
                        $('#product-' + id).fadeOut();
                    });
                }
            });

            $('.edit-product').click(function() {
                var id = $(this).data('id');
                $.get('fetch_product.php', { id: id }, function(data) {
                    var product = JSON.parse(data);
                    $('#editProductId').val(product.product_id);
                    $('#editProductName').val(product.product_name);
                    $('#editProductPrice').val(product.price);
                    $('#editProductStock').val(product.stock);

                    // Remove previous image preview if any
                    $('#productImagePreview').remove();

                    // Show existing product image
                    if (product.image_path) {
                        $('<img>', {
                            src: product.image_path + '?t=' + new Date().getTime(), // Cache-busting
                            id: 'productImagePreview',
                            class: 'img-fluid mt-2',
                            style: 'max-height: 120px; display: block; margin: auto;'
                        }).insertBefore('#editProductImage');
                    }

                    $('#editProductModal').modal('show');
                });
            });

            $('#editProductForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'update_product.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        location.reload(true); // Reload to reflect updates
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
