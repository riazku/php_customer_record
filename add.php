<?php include 'header.php'; ?>
<?php include 'config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Al-Khiar Khatta System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 2rem;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
            margin-bottom: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        .form-group {
            margin-bottom: 15px;
        }

        #receiptPreview {
            margin-top: 20px;
            max-width: 300px;
            max-height: 300px;
            display: none;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <h2>Add Customer</h2>
        <!-- Main form -->
        <form action="save.php" method="POST" enctype="multipart/form-data" id="mainForm">
            <div class="form-group">
                <label for="name">Customer Name:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <!-- Add purchase items dynamically -->
            <h3>Add Purchases</h3>
            <div id="purchaseForm">
                <div class="form-group">
                    <label for="itemName">Item Name:</label>
                    <input type="text" id="itemName" class="form-control">
                </div>
                <div class="form-group">
                    <label for="price">Price:</label>
                    <input type="number" id="price" class="form-control">
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" class="form-control">
                </div>
                <button type="button" class="btn btn-primary" onclick="addPurchase()">Add Purchase</button>
            </div>

            <!-- Display purchase table -->
            <table id="purchaseTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamically added rows -->
                </tbody>
            </table>

            <!-- Hidden input to hold purchase data -->
            <input type="hidden" name="purchases" id="purchasesData">

            <!-- Other form fields -->
            <div class="form-group">
                <label for="total">Total Amount:</label>
                <input type="number" name="total_amount" id="total" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="paid">Paid Amount:</label>
                <input type="number" name="paid_amount" id="paid" class="form-control" required oninput="calculateRemaining()">
            </div>
            <div class="form-group">
                <label for="remaining">Remaining Amount:</label>
                <input type="number" name="remaining_amount" id="remaining" class="form-control" readonly>
            </div>
            <div class="form-group">
                <label for="date">Purchase Date:</label>
                <input type="date" name="purchase_date" id="date" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="receipt">Upload Receipt:</label>
                <input type="file" name="receipt" id="receipt" class="form-control">
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-success" onclick="prepareData()">Save</button>
        </form>
    </div>
</div>

<script>
    const purchaseData = [];

    function addPurchase() {
        const itemName = document.getElementById('itemName').value;
        const price = parseFloat(document.getElementById('price').value);
        const quantity = parseInt(document.getElementById('quantity').value);
        const total = price * quantity;

        purchaseData.push({ itemName, price, quantity, total });
        updatePurchaseTable();

        document.getElementById('itemName').value = '';
        document.getElementById('price').value = '';
        document.getElementById('quantity').value = '';
    }

    function updatePurchaseTable() {
        const tableBody = document.querySelector('#purchaseTable tbody');
        tableBody.innerHTML = '';

        purchaseData.forEach((purchase, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${purchase.itemName}</td>
                <td>${purchase.price}</td>
                <td>${purchase.quantity}</td>
                <td>${purchase.total}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editPurchase(${index})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deletePurchase(${index})">Delete</button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Recalculate total amount
        let totalAmount = 0;
        purchaseData.forEach(purchase => {
            totalAmount += purchase.total;
        });

        document.getElementById('total').value = totalAmount;
        calculateRemaining(); // Recalculate remaining amount
    }

    function editPurchase(index) {
        const purchase = purchaseData[index];
        document.getElementById('itemName').value = purchase.itemName;
        document.getElementById('price').value = purchase.price;
        document.getElementById('quantity').value = purchase.quantity;

        purchaseData.splice(index, 1);
        updatePurchaseTable();
    }

    function deletePurchase(index) {
        purchaseData.splice(index, 1);
        updatePurchaseTable();
    }

    // Function to calculate remaining amount
    function calculateRemaining() {
        const totalAmount = parseFloat(document.getElementById('total').value) || 0;
        const paidAmount = parseFloat(document.getElementById('paid').value) || 0;
        const remainingAmount = totalAmount - paidAmount;
        document.getElementById('remaining').value = remainingAmount.toFixed(2);
    }

    function prepareData() {
        const purchasesField = document.getElementById('purchasesData');
        purchasesField.value = JSON.stringify(purchaseData);
    }
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
