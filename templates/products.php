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
    <title>Added Menu Product</title>
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
            font-weight: normal;
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
            color: #fff;
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
            background-color: #fff;
            border-bottom: 1px solid #e5e5e5;
            padding: 1rem 1.5rem;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ccc;
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
                <a href="products.php" class="nav-link fw-bold text-decoration-underline">Added Products</a>
                <a href="order_history.php" class="nav-link">Order History</a>
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
                <h4 class="mb-0 fw-bold text-black">Added Menu History</h4>
                <!-- <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    + Add Product
                </button> -->
            </div>

            <div class="card-body p-4">
                <!-- Search -->
                <div class="mb-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by product name or added by...">
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price (PHP)</th>
                                <th>Added By</th>
                                <th>Role</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="spinner-border text-dark" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold text-black">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label for="addProductName" class="form-label fw-semibold">Product Name</label>
                            <input type="text" class="form-control" id="addProductName" placeholder="Enter product name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addProductPrice" class="form-label fw-semibold">Price (PHP)</label>
                            <input type="number" class="form-control" id="addProductPrice" placeholder="Enter price" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center">
                    <button type="button" class="btn btn-primary px-5 fw-semibold" onclick="handleAddProduct()">Add Product</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../app.js"></script>

    <script>
        let addProductModal;

        document.addEventListener('DOMContentLoaded', async () => {
            addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
            await loadProducts();
        });

        async function loadProducts(search = '') {
            const res = await fetch(`../api.php?action=get_products&search=${encodeURIComponent(search)}`);
            const data = await res.json();
            const tbody = document.getElementById('productsTableBody');
            tbody.innerHTML = '';

            if (data.success && data.products.length > 0) {
                data.products.forEach(p => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${p.id}</td>
                            <td>${p.name}</td>
                            <td>${p.price}</td>
                            <td>${p.added_by_name}</td>
                            <td>${p.added_by_role}</td>
                            <td>${p.date_added ? new Date(p.date_added).toLocaleString('en-PH', { timeZone: 'Asia/Manila' }) : 'N/A'}</td>

                        </tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No products found.</td></tr>';
            }
        }

        const debouncedSearch = debounce(async function() {
            const searchTerm = document.getElementById('searchInput').value;
            await loadProducts(searchTerm);
        }, 500);

        document.getElementById('searchInput').addEventListener('input', debouncedSearch);

        async function handleAddProduct() {
            const name = document.getElementById('addProductName').value;
            const price = document.getElementById('addProductPrice').value;

            if (!name || !price) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all fields.'
                });
                return;
            }

            const res = await fetch('../api.php?action=add_product', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    price
                })
            });

            const data = await res.json();

            if (data.success) {
                addProductModal.hide();
                document.getElementById('addProductForm').reset();
                await loadProducts();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to add product.'
                });
            }
        }
    </script>
</body>

</html>