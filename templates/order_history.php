<?php
require_once '../session.php';
requireLogin();
requireAdmin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../style.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9f9f9;
            color: #222;
        }

        header {
            background-color: #000;
            color: #fff;
            padding: 1rem 0;
            border-bottom: 2px solid #222;
        }

        header .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #fff;
            margin-right: 20px;
        }

        header .nav-link {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
        }

        header .nav-link:hover {
            color: #ccc;
        }

        header .btn-light {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            color: #000;
        }

        header .btn-light:hover {
            background-color: #eaeaea;
        }

        .btn-primary {
            background-color: #000;
            border: none;
        }

        .btn-primary:hover {
            background-color: #333;
        }

        main {
            padding: 3rem 0;
        }

        .welcome-admin {
            text-align: center;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #000;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #fff;
            margin: 0 auto;
            max-width: 1000px;
        }

        .card-header {
            background-color: #fff !important;
            border-bottom: 1px solid #ddd;
        }

        thead th {
            background-color: #f1f1f1;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid #ccc;
        }

        tbody td {
            vertical-align: middle;
            font-size: 0.95rem;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background-color: #fafafa;
        }

        /* More specific selector for voided rows to override Bootstrap */
        table tbody tr.voided-row {
            background-color: #f8d7da !important;
            /* Light red background for the whole row */
        }

        /* Slightly darker red on hover for voided rows */
        table tbody tr.voided-row:hover {
            background-color: #f5c6cb !important;
        }

        tfoot td {
            font-weight: 700;
            background-color: #f9f9f9;
        }

        #totalSum {
            color: #198754;
            font-weight: 700;
        }

        .status-active {
            color: #198754;
            background-color: #d1e7dd;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-voided {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        footer {
            padding: 1rem 0;
            background-color: #f5f5f5;
            color: #777;
            font-size: 0.9rem;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="navbar-brand">Mithi Café + Bistro</span>
                <a href="index.php" class="nav-link">Point of Sale System</a>
                <a href="all_users.php" class="nav-link">All Users</a>
                <a href="products.php" class="nav-link">Products</a>
                <a href="order_history.php" class="nav-link fw-bold text-decoration-underline">Order History</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#" onclick="logout()" class="btn btn-light btn-sm fw-semibold">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container flex-grow-1">
        <div class="welcome-admin">
            Welcome, Admin <?php echo htmlspecialchars($user['firstname']); ?>!
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-black">Order Transaction History</h4>
                <button class="btn btn-primary fw-semibold" onclick="printReport()">🖨️ Print Report (PDF)</button>
            </div>

            <div class="card-body p-4">
                <!-- Filters -->
                <div class="row mb-4 g-3">
                    <div class="col-md-4">
                        <input type="date" class="form-control" id="date_start">
                    </div>
                    <div class="col-md-4">
                        <input type="date" class="form-control" id="date_end">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 fw-semibold" onclick="loadOrders()">Filter</button>
                    </div>


                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Items</th>
                                <th>Total (₱)</th>
                                <th>Cashier</th>
                                <th>Date Ordered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-dark" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end">TOTAL SUM:</td>
                                <td id="totalSum">₱0.00</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-auto text-center">
        <div class="container">
            &copy; <?php echo date('Y'); ?> Mithi Café + Bistro. All Rights Reserved.
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../app.js"></script>

    <script>
        async function printReport() {
            const start = document.getElementById('date_start').value;
            const end = document.getElementById('date_end').value;

            let url = `../api.php?action=print_report`;
            if (start && end) url += `&date_start=${start}&date_end=${end}`;
            else if (start) url += `&date_start=${start}`;

            window.open(url, '_blank');
        }

        async function loadOrders() {
            const start = document.getElementById('date_start').value;
            const end = document.getElementById('date_end').value;

            // Always include voided orders in the history
            let url = `../api.php?action=get_orders&include_voided=1`;
            if (start && end) url += `&date_start=${start}&date_end=${end}`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                const tbody = document.getElementById('ordersTableBody');
                const totalSum = document.getElementById('totalSum');

                tbody.innerHTML = '';
                let runningTotal = 0;

                if (data.success && data.orders.length > 0) {
                    data.orders.forEach(o => {
                        const isVoided = o.voided == 1;
                        const statusText = isVoided ? 'Voided' : 'Active';
                        const statusClass = isVoided ? 'status-voided' : 'status-active';
                        const rowClass = isVoided ? 'voided-row' : ''; // Applies light red background to the whole row if voided

                        // Debug: Log the row class to console
                        console.log(`Order ID ${o.id}: isVoided=${isVoided}, rowClass="${rowClass}"`);

                        // Only add to total if not voided and total_amount is a valid number
                        if (!isVoided && !isNaN(parseFloat(o.total_amount))) {
                            runningTotal += parseFloat(o.total_amount);
                        }

                        // Format date safely
                        const dateOrdered = o.date_ordered ? new Date(o.date_ordered).toLocaleString('en-PH', {
                            timeZone: 'Asia/Manila'
                        }) : 'N/A';

                        tbody.innerHTML += `
                            <tr class="${rowClass}">
                                <td>${o.id || 'N/A'}</td>
                                <td>${o.items || 'N/A'}</td>
                                <td>${parseFloat(o.total_amount || 0).toFixed(2)}</td>
                                <td>${o.cashier_name || 'N/A'}</td>
                                <td>${dateOrdered}</td>
                                <td><span class="${statusClass}">${statusText}</span></td>
                            </tr>`;
                    });

                    totalSum.textContent = `₱${runningTotal.toFixed(2)}`;
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No transactions found.</td></tr>';
                    totalSum.textContent = '₱0.00';
                }
            } catch (error) {
                console.error('Error loading orders:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error loading data. Please try again.</td></tr>';
                totalSum.textContent = '₱0.00';
            }
        }

        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>

</html>