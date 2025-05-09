

<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
if (!file_exists("config.php")) {
    die("Error: config.php file not found!");
}
include "config.php";


// Fetch all customers
$customersResult = $conn->query("SELECT * FROM Customers");
$customers = [];
if ($customersResult->num_rows > 0) {
    while ($row = $customersResult->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch all products
$productsResult = $conn->query("SELECT * FROM Products");
$products = [];
if ($productsResult->num_rows > 0) {
    while ($row = $productsResult->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Place Order - POS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>

.product-container {
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    width: 75%;
    margin-bottom: 20px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.product-card {
    text-align: center;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #fff;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
}

.product-card img {
    width: 50%;
    height: 100px;
    object-fit: cover;
}

.quantity-input {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 5px;
}

.quantity-input button {
    width: 30px;
    height: 30px;
    border: none;
    background: #007bff;
    color: white;
    font-size: 18px;
    cursor: pointer;
    border-radius: 5px;
}

.quantity-input input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 5px;
}

.receipt-container {
    width: 35%;
    border: 1px solid #ddd;
    background: white;
    padding: 15px;
    border-radius: 5px;
    height: 350px;
    overflow-y: auto;
    margin-left: 20px;
}

.receipt-table th, .receipt-table td {
    text-align: center;
    padding: 5px;
}

    /* Responsive Design */
    @media (max-width: 1024px) {
        .product-container {
            width: 100%;
        }

        .product-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .receipt-container {
            width: 100%;
            margin-left: 0;
            margin-top: 20px;
        }

        .container.d-flex {
            flex-direction: column;
        }
    }

    @media (max-width: 768px) {
        .product-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }

        .receipt-container {
            width: 100%;
            margin-left: 0;
        }

        .container.d-flex {
            flex-direction: column;
            align-items: center;
        }
    }

    @media (max-width: 480px) {
        .product-container {
            padding: 5px;
        }

        .product-grid {
            grid-template-columns: 1fr;
        }

        .product-card img {
            width: 60%;
            height: 80px;
        }

        .quantity-input button {
            width: 25px;
            height: 25px;
        }

        .quantity-input input {
            width: 40px;
        }

        .receipt-container {
            width: 100%;
            margin-left: 0;
        }

        .receipt-table th, .receipt-table td {
            padding: 3px;
        }

        .container.d-flex {
            flex-direction: column;
            align-items: center;
        }
        .out-of-stock {
    opacity: 0.5;
    pointer-events: none;
}

    }
        
    </style>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include "menu.php"; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Include Header -->
        <?php include "header.php"; ?>

        <!-- Order Form -->
        <div class="content">
            <h2 class="mb-4">Place New Order</h2>

                     <!-- Searchable Customer Dropdown -->
            <div class="mb-2 d-flex align-items-center" style="max-width: 500px;">
                <label for="customerSearch" class="form-label me-2">Select Customer:</label>
                <input type="text" id="customerSearch" class="form-control" placeholder="Search Customer...">
                <span id="selectedCustomerDisplay" class="ms-2 text-muted"></span> <!-- For showing selected customer -->
            </div>

            <div id="customerDropdown" class="dropdown-menu w-30" style="display: none; max-height: 200px; overflow-y: auto;">
                <?php foreach ($customers as $customer): ?>
                    <div class="dropdown-item" data-id="<?= $customer['customer_id']; ?>" data-name="<?= htmlspecialchars($customer['name']); ?>">
                        <?= htmlspecialchars($customer['name']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" id="selectedCustomerId">

            
                <div class="container d-flex">
                    <!-- Product Section -->
                    <div class="product-container">
                        <input type="text" id="searchProduct" class="form-control mb-3" placeholder="Search Product...">
                        <div class="product-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card" data-id="<?= $product['product_id']; ?>" data-name="<?= htmlspecialchars($product['product_name']); ?>" data-price="<?= $product['price']; ?>">
                                    <img src="<?= $product['image_path']; ?>" alt="<?= htmlspecialchars($product['product_name']); ?>">
                                    <h6><?= htmlspecialchars($product['product_name']); ?></h6>
                                    <p>$<?= number_format($product['price'], 2); ?></p>
                                    <p class="stock-text">Stock: <?= $product['stock']; ?></p>

                                    <div class="quantity-input">
                                        <button class="decrement" data-id="<?= $product['product_id']; ?>" <?= $product['stock'] == 0 ? 'disabled' : ''; ?>>-</button>
                                        <button class="increment" data-id="<?= $product['product_id']; ?>" <?= $product['stock'] == 0 ? 'disabled' : ''; ?>>+</button>
                                    </div>

                                
                                    <input type="number" min="0" value="0" class="form-control quantity" data-id="<?= $product['product_id']; ?>" <?= $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Receipt Section -->
                    <div class="receipt-container">
                    <h5>Receipt</h5>
                    <table class="table table-bordered receipt-table">
                        <thead>
                            <tr>
                                <th>No</th>  <!-- Add new column for item numbers -->
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody id="receipt-list"></tbody>
                    </table>
                    <h6 class="mt-3">Total: $<span id="totalAmount">0.00</span></h6>
                </div>

            </div>


         <!-- Fields and Button in One Line -->
            <div class="row mb-5 d-flex align-items-center">
                <div class="col-md-2">
                    <label for="totalAmountField" class="form-label">Total Amount</label>
                    <input type="text" id="totalAmountField" class="form-control" placeholder="Enter Total Amount" readonly>
                </div>
                <div class="col-md-2">
                    <label for="paidAmountField" class="form-label">Paid Amount</label>
                    <input type="text" id="paidAmountField" class="form-control" placeholder="Enter Paid Amount">
                </div>
                <div class="col-md-2">
                    <label for="remainingAmountField" class="form-label">Remaining Amount</label>
                    <input type="text" id="remainingAmountField" class="form-control" placeholder="Remaining Amount" readonly>
                </div>
                <div class="col-md-2">
                    <label for="orderDateField" class="form-label">Date</label>
                    <input type="date" id="orderDateField" class="form-control">
                </div>

                <!-- Order Button -->
                <div class="col-md-2 d-flex justify-content ">
                    <button id="submitOrder" class="btn btn-primary mt-4"><i class="fas fa-check"></i>
                        Submit Order
                    </button>
                </div>
            </div> 
 
        </div>
 

    </div>


    <!-- Include Footer -->
    <?php include "footer.php"; ?>

    <script src="script.js"></script>
    <!-- <script src="order_script.js"></script>  -->


<script>




document.addEventListener("DOMContentLoaded", function () {
        // Initialize total amount
        let totalAmount = 0;



        document.querySelectorAll(".increment").forEach(button => {
        button.addEventListener("click", function () {
            let productId = this.getAttribute("data-id");
            let stockElement = document.getElementById("stock-" + productId);
            let stock = parseInt(stockElement.textContent);
            let inputField = document.querySelector(`.quantity[data-id='${productId}']`);
            let decrementButton = document.querySelector(`.decrement[data-id='${productId}']`);
            let incrementButton = document.querySelector(`.increment[data-id='${productId}']`);
            let productCard = document.querySelector(`.product-card[data-id='${productId}']`);

            if (stock > 0) {
                stock--; // Reduce stock count
                stockElement.textContent = stock; // Update UI stock count

                // If stock reaches zero, disable buttons and input field
                if (stock === 0) {
                    inputField.disabled = true;
                    decrementButton.disabled = true;
                    incrementButton.disabled = true;
                    productCard.classList.add("out-of-stock");
                }
            }
        });
    });

        // Event delegation for increment, decrement, and quantity changes
        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("increment")) {
                let input = event.target.parentElement.nextElementSibling;
                input.value = parseInt(input.value) + 1;
                updateReceipt(event.target.closest(".product-card"), input.value);
                updateTotal();
            } else if (event.target.classList.contains("decrement")) {
                let input = event.target.parentElement.nextElementSibling;
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                    updateReceipt(event.target.closest(".product-card"), input.value);
                    updateTotal();
                }
            }
        });

        document.addEventListener("change", function (event) {
            if (event.target.classList.contains("quantity")) {
                let value = parseInt(event.target.value);
                if (isNaN(value) || value < 0) {
                    event.target.value = 0; // Prevent negative quantities
                }
                updateReceipt(event.target.closest(".product-card"), event.target.value);
                updateTotal();
            }
        });


        // Search functionality
        document.getElementById("searchProduct").addEventListener("input", function () {
            let searchText = this.value.trim().toLowerCase();
            document.querySelectorAll(".product-card").forEach(card => {
                let productName = card.dataset.name.toLowerCase();
                card.style.display = searchText === "" || productName.includes(searchText) ? "block" : "none";
            });
        });


             // Customer dropdown functionality
            let customerSearch = document.getElementById("customerSearch");
            let customerDropdown = document.getElementById("customerDropdown");
            let customerItems = document.querySelectorAll("#customerDropdown .dropdown-item");

            customerSearch.addEventListener("focus", function () {
                customerDropdown.style.display = "block"; // Show dropdown on focus
            });

            customerSearch.addEventListener("input", function () {
                let searchText = this.value.trim().toLowerCase();
                let hasResults = false;

                customerItems.forEach(item => {
                    let customerName = item.textContent.toLowerCase();
                    if (customerName.includes(searchText)) {
                        item.style.display = "block";
                        hasResults = true;
                    } else {
                        item.style.display = "none";
                    }
                });

                customerDropdown.style.display = hasResults ? "block" : "none";
            });

            customerItems.forEach(item => {
                item.addEventListener("click", function () {
                    customerSearch.value = this.textContent;
                    document.getElementById("selectedCustomerId").value = this.dataset.id;
                    customerDropdown.style.display = "none"; // Hide dropdown after selection
                });
            });

            // Hide dropdown when clicking outside
            document.addEventListener("click", function (event) {
                if (!customerSearch.contains(event.target) && !customerDropdown.contains(event.target)) {
                    customerDropdown.style.display = "none";
                }
            });

        

        // Update receipt and total functions
        function updateReceipt(productCard, quantity) {
            let productId = productCard.dataset.id;
            let productName = productCard.dataset.name;
            let productPrice = parseFloat(productCard.dataset.price);
            let receiptList = document.getElementById("receipt-list");
            let existingRow = document.querySelector(`#receipt-list tr[data-id="${productId}"]`);

            if (quantity > 0) {
                let totalPrice = (productPrice * quantity).toFixed(2);

                if (existingRow) {
                    existingRow.querySelector(".item-quantity").textContent = quantity;
                    existingRow.querySelector(".item-price").textContent = formatCurrency(totalPrice);
                } else {
                    let row = document.createElement("tr");
                    row.setAttribute("data-id", productId);

                    // Generate dynamic item number
                    let itemNumber = receiptList.children.length + 1;

                    row.innerHTML = `
                        <td>${itemNumber}</td>
                        <td>${productName}</td>
                        <td class="item-quantity">${quantity}</td>
                        <td class="item-price">${formatCurrency(totalPrice)}</td>
                    `;
                    receiptList.appendChild(row);
                }
            } else if (existingRow) {
                existingRow.remove();
            }

            updateTotal(); // Update the total after modifying the receipt
        }

        function updateTotal() {
            totalAmount = 0;
            document.querySelectorAll("#receipt-list .item-price").forEach(item => {
                totalAmount += parseFloat(item.textContent.replace("$", ""));
            });
            document.getElementById("totalAmount").textContent = formatCurrency(totalAmount);
            document.getElementById("totalAmountField").value = totalAmount.toFixed(2);

            // Update remaining amount if paid amount is set
            let paidAmount = parseFloat(document.getElementById("paidAmountField").value) || 0;
            let remaining = totalAmount - paidAmount;
            document.getElementById("remainingAmountField").value = formatCurrency(remaining);
        }

        // Currency formatting function
        function formatCurrency(amount) {
            return `$${parseFloat(amount).toFixed(2)}`;
        }

        // Update remaining amount when paid amount changes
        document.getElementById("paidAmountField").addEventListener("input", function () {
            let paidAmount = parseFloat(this.value) || 0;
            let remaining = totalAmount - paidAmount;
            document.getElementById("remainingAmountField").value = formatCurrency(remaining);
        });
    });


    document.getElementById("submitOrder").addEventListener("click", function () {
    // Validate if a customer is selected
    let customerId = document.getElementById("selectedCustomerId").value;
    if (!customerId) {
        alert("Please select a customer.");
        return;
    }

    // Validate if there are items in the receipt
    let receiptItems = document.querySelectorAll("#receipt-list tr");
    if (receiptItems.length === 0) {
        alert("Please add at least one product to the order.");
        return;
    }

    // Collect order data
    let orderData = {
        customerId: customerId,
        totalAmount: parseFloat(document.getElementById("totalAmountField").value),
        paidAmount: parseFloat(document.getElementById("paidAmountField").value),
        remainingAmount: parseFloat(document.getElementById("remainingAmountField").value),
        orderDate: document.getElementById("orderDateField").value,
        items: []
    };

    // Collect order items
    receiptItems.forEach(row => {
        let productId = row.getAttribute("data-id");
        let quantity = row.querySelector(".item-quantity").textContent;
        let price = parseFloat(row.querySelector(".item-price").textContent.replace("$", ""));

        orderData.items.push({
            productId: productId,
            quantity: quantity,
            price: price
        });
    });

    // Send data to the server
    fetch("submit_order.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Order submitted successfully!");
            window.location.reload(); // Reload the page to clear the form
        } else {
            alert("Error submitting order: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while submitting the order.");
    });
});




  

</script>
</body>
</html>
