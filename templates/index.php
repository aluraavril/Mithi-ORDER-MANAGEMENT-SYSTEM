<?php
require_once '../session.php';

// Require login
requireLogin();

$user = getCurrentUser();

// // If admin, redirect straight to all_users.php
// if ($user['is_admin']) {
//     header("Location: all_users.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mithi Café + Bistro POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Georgia', serif;
            background-color: #fffaf5;
        }

        h3,
        h4,
        h5 {
            font-weight: bold;
            color: #4b2e2e;
        }

        .menu-card {
            width: 200px;
            margin-bottom: 30px;
        }

        .menu-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .menu-card img {
            height: 140px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .order-box {
            background-color: #fff3eb;
            padding: 15px;
            border-radius: 8px;
            min-height: 300px;
            border: 1px solid #eeddd2;
        }

        .void-btn {
            color: red;
            cursor: pointer;
            font-weight: bold;
            margin-left: 10px;
        }

        .menu-section {
            margin-bottom: 50px;
        }

        .menu-section h4 {
            margin-bottom: 20px;
            border-bottom: 2px solid #decfc1;
            padding-bottom: 5px;
        }

        .btn-primary {
            background-color: #865439;
            border: none;
        }

        .btn-primary:hover {
            background-color: #a96f4d;
        }

        .btn-success {
            background-color: #c28f70;
            border: none;
        }

        .btn-success:hover {
            background-color: #a9765c;
        }

        .btn-danger {
            background-color: #d96f58;
            border: none;
        }

        .btn-danger:hover {
            background-color: #b85742;
        }

        .add-product-form {
            background-color: #fff3eb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eeddd2;
            margin-bottom: 20px;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-item .badge {
            font-size: 0.75rem;
            padding: 5px 8px;
            border-radius: 10px;
        }
    </style>
</head>

<body class="p-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Mithi Café + Bistro POS</h3>
                    <?php if ($user['is_admin']): ?>
                        <button class="btn btn-outline-danger" onclick="goToDashboard()">Go back to Dashboard</button>
                    <?php else: ?>
                        <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                    <?php endif; ?>
                </div>

                <div class="menu-section">
                    <h4>Food</h4>
                    <div class="d-flex flex-wrap gap-3" id="foodMenu"></div>
                </div>

                <div class="menu-section">
                    <h4>Drinks</h4>
                    <div class="d-flex flex-wrap gap-3" id="drinksMenu"></div>
                </div>

                <div class="menu-section">
                    <h4>Dessert</h4>
                    <div class="d-flex flex-wrap gap-3" id="dessertMenu"></div>
                </div>
            </div>

            <div class="col-md-3">
                <h4>Add New Product</h4>
                <div class="add-product-form">
                    <input type="text" id="productName" class="form-control mb-2" placeholder="Product Name">
                    <input type="number" id="productPrice" class="form-control mb-2" placeholder="Price (PHP)">
                    <input type="url" id="productImage" class="form-control mb-2" placeholder="Image URL">
                    <select id="productCategory" class="form-control mb-2">
                        <option value="Food">Food</option>
                        <option value="Drinks">Drinks</option>
                        <option value="Dessert">Dessert</option>
                    </select>
                    <button class="btn btn-primary w-100" onclick="addNewProduct()">Add Product</button>
                </div>

                <h4>Ordered Items</h4>
                <div id="orderList" class="order-box mb-3"></div>
                <h5>Total: <span id="totalAmount">0 PHP</span></h5>
                <input id="cashInput" type="number" placeholder="Enter the amount here" class="form-control mb-2">
                <button class="btn btn-success w-100 mb-2" onclick="payOrder()">Pay!</button>
                <button class="btn btn-primary w-100 mb-2" onclick="printReceipt()">Print Receipt</button>
                <button class="btn btn-danger w-100" onclick="nextCustomer()" id="nextBtn" style="display:none;">Next Customer</button>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h4 class="mb-0">Today's Transactions</h4>
                    <span onclick="printTodaysReport()" style="cursor: pointer; font-size: 1.2rem;" title="Print Report (PDF)">🖨️</span>
                </div>
                <div id="todaysTransactions" class="order-box mb-3">
                    <div class="text-center text-muted">Loading today's orders...</div>
                </div>
            </div>





        </div>
    </div>

    <script>
        let menuItems = [
            // Food
            {
                name: 'Steak & Potato Crisps',
                price: 3545,
                image: 'https://i.pinimg.com/736x/a4/74/04/a474044255a85d3e1481caa322aed43f.jpg',
                category: 'Food'
            },
            {
                name: 'Beef Salpicao',
                price: 1200,
                image: 'https://lacarne.ph/cdn/shop/articles/salpicao_1024x1024.jpg?v=1674301696',
                category: 'Food'
            },
            {
                name: 'Truffle Pasta',
                price: 658,
                image: 'https://i0.wp.com/www.angsarap.net/wp-content/uploads/2022/02/Four-Mushroom-and-Truffle-Pasta-Wide.jpg?ssl=1',
                category: 'Food'
            },
            {
                name: 'Croissant Sandwich',
                price: 350,
                image: 'https://thehealthfulideas.com/wp-content/uploads/2022/07/Croissant-Breakfast-Sandwich-SQUARE2.jpg',
                category: 'Food'
            },

            // Drinks
            {
                name: 'Cold Brew Passion Sparkle',
                price: 237,
                image: 'https://i.pinimg.com/736x/55/fb/8a/55fb8ae79d3f751ac254c3dfd8ed8ba1.jpg',
                category: 'Drinks'
            },
            {
                name: 'Spanish Latte Einspänner',
                price: 196,
                image: 'https://143656509.cdn6.editmysite.com/uploads/1/4/3/6/143656509/YJDZHUB5GMI5DO4FBMDLD4VA.png',
                category: 'Drinks'
            },
            {
                name: 'Ube Latte Cloud',
                price: 220,
                image: 'https://i.pinimg.com/736x/9d/a6/7e/9da67e41cf20d6c8961db886ed7ca473.jpg',
                category: 'Drinks'
            },
            {
                name: 'Lychee Rose Fizz',
                price: 230,
                image: 'https://i.pinimg.com/736x/03/3b/f1/033bf1dd7405c739ba761fcf4dc57720.jpg',
                category: 'Drinks'
            },

            // Dessert
            {
                name: 'Basque Burnt Cheesecake',
                price: 1680,
                image: 'https://i.pinimg.com/736x/8f/19/6b/8f196b670f1631438dd6d3c72589216b.jpg',
                category: 'Dessert'
            },
            {
                name: 'New York Cheesecake',
                price: 1220,
                image: 'https://sugarspunrun.com/wp-content/uploads/2024/11/New-York-cheesecake-1-of-1-2.jpg',
                category: 'Dessert'
            },
            {
                name: 'Mango Mille Crepe Cake',
                price: 960,
                image: 'https://flouringkitchen.com/wp-content/uploads/2024/05/mango_crepe_cake_thumbnail.jpg',
                category: 'Dessert'
            }
        ];

        let orders = [];
        let payment = null;


        // Added bago
        async function loadProductsFromDB() {
            try {
                const res = await fetch('../api.php?action=get_products');
                const data = await res.json();

                if (data.success) {
                    data.products.forEach(p => {
                        menuItems.push({
                            name: p.name,
                            price: parseFloat(p.price),
                            image: p.image || 'https://cdn-icons-png.flaticon.com/512/3081/3081559.png',
                            category: p.category
                        });
                    });
                }
            } catch (err) {
                console.error('Error loading DB products:', err);
            }
        }



        function renderMenu() {
            const foodContainer = document.getElementById("foodMenu");
            const drinksContainer = document.getElementById("drinksMenu");
            const dessertContainer = document.getElementById("dessertMenu");

            // Clear existing content
            foodContainer.innerHTML = "";
            drinksContainer.innerHTML = "";
            dessertContainer.innerHTML = "";

            menuItems.forEach((item, index) => {
                const card = document.createElement("div");
                card.className = "card menu-card";

                card.innerHTML = `
                    <img src="${item.image}" class="card-img-top" alt="${item.name}">
                    <div class="card-body">
                        <div>
                            <h6 class="card-title">${item.name}</h6>
                            <p>${item.price} PHP</p>
                            <input type="number" class="form-control mb-2" min="1" value="1" id="qty-${index}">
                        </div>
                        <button class="btn btn-primary w-100 mt-auto" onclick="addToOrder(${index})">Add to order</button>
                    </div>
                `;

                if (item.category === 'Food') foodContainer.appendChild(card);
                else if (item.category === 'Drinks') drinksContainer.appendChild(card);
                else if (item.category === 'Dessert') dessertContainer.appendChild(card);
            });
        }

        function addToOrder(index) {
            const qty = parseInt(document.getElementById(`qty-${index}`).value);
            if (qty <= 0) return;

            const existing = orders.find(o => o.name === menuItems[index].name);
            if (existing) {
                existing.qty += qty;
            } else {
                orders.push({
                    ...menuItems[index],
                    qty
                });
            }
            renderOrders();
        }

        function renderOrders() {
            const orderList = document.getElementById("orderList");
            orderList.innerHTML = "";
            let total = 0;

            orders.forEach((item, idx) => {
                total += item.price * item.qty;

                const div = document.createElement("div");
                div.className = "mb-2 d-flex justify-content-between align-items-center";

                div.innerHTML = `
                    <div>
                        <strong>${item.name}</strong><br>
                        Qty: ${item.qty}
                    </div>
                    <span class="void-btn" onclick="voidItem(${idx})">×</span>
                `;
                orderList.appendChild(div);
            });

            document.getElementById("totalAmount").textContent = `${total} PHP`;
        }

        function voidItem(index) {
            orders.splice(index, 1);
            renderOrders();
        }

        async function payOrder() {
            const total = orders.reduce((sum, item) => sum + item.price * item.qty, 0);
            const cash = parseInt(document.getElementById("cashInput").value);

            if (isNaN(cash) || cash < total) {
                alert("Insufficient payment!");
                return;
            }

            const change = cash - total;
            payment = {
                cash,
                change,
                total
            };
            alert(`Thanks for ordering! Here's your ${change} pesos change.`);

            // ✅ Save order transaction (now with error checking)
            try {
                const userResponse = await fetch('../api.php?action=get_session');
                const userData = await userResponse.json();
                const cashierName = userData.user.firstname + ' ' + userData.user.lastname;

                const saveResponse = await fetch('../api.php?action=save_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items: orders.map(o => `${o.name} x${o.qty}`).join(', '),
                        total_amount: total,
                        cashier_name: cashierName
                    })
                });

                const saveData = await saveResponse.json();
                if (!saveData.success) {
                    alert("❌ Failed to save order: " + saveData.message);
                    return; // Stop here if save failed
                }

                document.getElementById("nextBtn").style.display = "block";
                await loadTodaysTransactions(); // Refresh today's transactions
            } catch (error) {
                console.error('Error saving order:', error);
                alert("❌ Error saving order. Please try again.");
            }
        }

        async function loadTodaysTransactions() {
            try {
                // Use &today=1 to let the server handle the date (avoids timezone issues)
                const res = await fetch(`../api.php?action=get_orders&today=1`);
                const data = await res.json();

                // Debug: Log the full API response to console
                console.log('API Response for today\'s transactions:', data);

                const container = document.getElementById("todaysTransactions");
                container.innerHTML = "";

                if (data.success && data.orders.length > 0) {
                    const userRes = await fetch('../api.php?action=get_session');
                    const userData = await userRes.json();
                    const isAdmin = userData.user && userData.user.is_admin == 1;

                    data.orders.forEach(o => {
                        const div = document.createElement("div");
                        div.className = "transaction-item border-bottom py-2";

                        div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${o.cashier_name}</strong>
                            <small class="text-muted d-block">${new Date(o.date_ordered)
                                .toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })}</small>
                            <div class="text-muted small">${o.items}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱${parseFloat(o.total_amount).toFixed(2)}</div>
                            ${isAdmin ? `<button class="btn btn-sm btn-danger mt-1" onclick="voidTransaction(${o.id})">Void</button>` : ''}
                        </div>
                    </div>
                `;
                        container.appendChild(div);
                    });
                } else {
                    container.innerHTML = `<div class="text-center text-muted py-2">No transactions yet today.</div>`;
                }
            } catch (error) {
                console.error('Error loading today\'s transactions:', error);
                document.getElementById("todaysTransactions").innerHTML = `<div class="text-center text-danger py-2">Error loading transactions.</div>`;
            }
        }

        function printReceipt() {
            if (!orders.length || !payment) {
                alert("No completed payment to print.");
                return;
            }

            let receipt = "===== Mithi Café + Bistro Receipt =====\n\n";
            orders.forEach(item => {
                receipt += `${item.name} x ${item.qty} = ${item.price * item.qty} PHP\n`;
            });

            receipt += `\nTotal: ${payment.total} PHP`;
            receipt += `\nCash: ${payment.cash} PHP`;
            receipt += `\nChange: ${payment.change} PHP`;
            receipt += "\n\n=======================================";

            const receiptWindow = window.open('', '', 'width=600,height=400');
            receiptWindow.document.write(`<pre>${receipt}</pre>`);
            receiptWindow.document.close();
            receiptWindow.print();
        }

        function nextCustomer() {
            orders = [];
            payment = null;
            renderOrders();
            document.getElementById("cashInput").value = "";
            document.getElementById("nextBtn").style.display = "none";
        }
        // OLD ADD NEW PRODUCT
        // function addNewProduct() {
        //     const name = document.getElementById("productName").value.trim();
        //     const price = parseFloat(document.getElementById("productPrice").value);
        //     const image = document.getElementById("productImage").value.trim();
        //     const category = document.getElementById("productCategory").value;

        //     if (!name || isNaN(price) || price <= 0 || !image || !category) {
        //         alert("Please fill in all fields correctly.");
        //         return;
        //     }

        //     // Add to menuItems array
        //     menuItems.push({
        //         name: name,
        //         price: price,
        //         image: image,
        //         category: category
        //     });

        //     // Clear form
        //     document.getElementById("productName").value = "";
        //     document.getElementById("productPrice").value = "";
        //     document.getElementById("productImage").value = "";

        //     // Re-render menu
        //     renderMenu();

        //     alert("Product added successfully!");
        // }

        // ADD NEW PRODUCT NEW
        async function addNewProduct() {
            const name = document.getElementById("productName").value.trim();
            const price = parseFloat(document.getElementById("productPrice").value);
            const image = document.getElementById("productImage").value.trim();
            const category = document.getElementById("productCategory").value;

            if (!name || isNaN(price) || price <= 0) {
                alert("Please complete product details.");
                return;
            }

            const response = await fetch('../api.php?action=add_product', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    price,
                    image,
                    category
                })
            });

            const result = await response.json();

            if (result.success) {
                alert("✅ Product Added!");
                menuItems = menuItems.slice(0, 11); // keep original static items
                await loadProductsFromDB();
                renderMenu();
            } else {
                alert("❌ " + result.message);
            }

            document.getElementById("productName").value = "";
            document.getElementById("productPrice").value = "";
            document.getElementById("productImage").value = "";
        }

        function printTodaysReport() {
            // Open the PDF report for today's transactions in a new tab
            window.open(`../api.php?action=print_report&today=1`, '_blank');
        }


        function logout() {
            fetch('../api.php?action=logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to login page after successful logout
                        window.location.href = 'login.php'; // Adjust if your login page has a different name/path
                    } else {
                        alert('Logout failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during logout:', error);
                    alert('An error occurred during logout.');
                });
        }

        function goToDashboard() {
            // Assuming the admin dashboard is all_users.php; adjust if different
            window.location.href = 'all_users.php';
        }


        // ==============================
        // ✅ TODAY'S TRANSACTIONS SECTION
        // ==============================
        async function loadTodaysTransactions() {
            try {
                // Use &today=1 to let the server handle the date (avoids timezone issues)
                const res = await fetch(`../api.php?action=get_orders&today=1`);
                const data = await res.json();
                const container = document.getElementById("todaysTransactions");

                container.innerHTML = "";

                if (data.success && data.orders.length > 0) {
                    const userRes = await fetch('../api.php?action=get_session');
                    const userData = await userRes.json();
                    const isAdmin = userData.user && userData.user.is_admin == 1;

                    data.orders.forEach(o => {
                        const div = document.createElement("div");
                        div.className = "transaction-item border-bottom py-2";

                        div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${o.cashier_name}</strong>
                            <small class="text-muted d-block">${new Date(o.date_ordered)
                                .toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })}</small>
                            <div class="text-muted small">${o.items}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱${parseFloat(o.total_amount).toFixed(2)}</div>
                            ${isAdmin ? `<button class="btn btn-sm btn-danger mt-1" onclick="voidTransaction(${o.id})">Void</button>` : ''}
                        </div>
                    </div>
                `;
                        container.appendChild(div);
                    });
                } else {
                    container.innerHTML = `<div class="text-center text-muted py-2">No transactions yet today.</div>`;
                }
            } catch (error) {
                console.error('Error loading today\'s transactions:', error);
                document.getElementById("todaysTransactions").innerHTML = `<div class="text-center text-danger py-2">Error loading transactions.</div>`;
            }
        }



        // Function to handle voiding (Admin only)
        async function voidTransaction(orderId) {
            if (!confirm("Are you sure you want to void this order?")) return;

            const res = await fetch(`../api.php?action=void_order`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: orderId
                })
            });

            const data = await res.json();

            if (data.success) {
                alert("✅ Transaction voided successfully.");
                loadTodaysTransactions();
            } else {
                alert("❌ Failed to void transaction: " + data.message);
            }
        }


        (async () => {
            await loadProductsFromDB();
            renderMenu();
            await loadTodaysTransactions(); // load today's transactions on page load
        })();
    </script>
</body>

</html>