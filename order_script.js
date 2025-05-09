document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("submitOrder").addEventListener("click", function() {
        let customerNameElement = document.getElementById("customerName");

        if (customerNameElement) {
            let customerName = customerNameElement.value.trim();  // Get input value

            if (customerName === "") {
                alert("Please enter your name.");
                return;
            }

            // Send data via AJAX to process_order.php
            fetch("process_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `customerName=${encodeURIComponent(customerName)}`
            })
            .then(response => response.text())
            .then(data => {
                alert("Order submitted successfully!");
                console.log("Response:", data);
            })
            .catch(error => {
                alert("Error submitting order.");
                console.error("Error:", error);
            });

        } else {
            console.error("Error: Element with ID 'customerName' not found!");
        }
    });
});
