<?php
require_once '../session.php';

// require login
requireLogin();

// require admin access
requireAdmin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
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

        .btn-light {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
        }

        .btn-light:hover {
            background-color: #eaeaea;
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
            letter-spacing: -0.5px;
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

        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
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

        .badge {
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 20px;
            padding: 0.4em 0.8em;
        }

        .bg-success {
            background-color: #000 !important;
            color: #fff !important;
        }

        .bg-secondary {
            background-color: #777 !important;
            color: #fff !important;
        }

        footer {
            padding: 1rem 0;
            background-color: #f5f5f5;
            color: #777;
            font-size: 0.9rem;
            border-top: 1px solid #ddd;
        }

        .suspended-row {
            opacity: 0.7;
            pointer-events: none;
            /* optional: disables clicks on the row */
        }

        .suspended-row:hover {
            opacity: 0.7;
        }

        .suspended-row button {
            pointer-events: all;
            opacity: 1;
            /* allow only the Unsuspend button to be clickable */
        }


        /* Center the action buttons properly */
        .table td:last-child {
            text-align: center;
            vertical-align: middle;
        }

        /* Make action buttons consistent in size */
        .table td button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
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
                <a href="all_users.php" class="nav-link fw-bold text-decoration-underline">All Users</a>
                <a href="products.php" class="nav-link">Added Products</a>
                <a href="order_history.php" class="nav-link">Order History</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="#" onclick="logout()" class="btn btn-light btn-sm fw-semibold">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container flex-grow-1">

        <!-- Welcome Admin Text -->
        <div class="welcome-admin">
            Welcome, Admin <?php echo htmlspecialchars($user['firstname']); ?>!
        </div>

        <!-- Users Table -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold text-black">All Users</h4>
                <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    + Add User
                </button>
            </div>

            <div class="card-body p-4">
                <!-- Search -->
                <div class="mb-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by username, first name, or last name...">
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Role</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold text-black">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label for="addUsername" class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" id="addUsername" placeholder="Enter username" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="addFirstname" class="form-label fw-semibold">First Name</label>
                                <input type="text" class="form-control" id="addFirstname" placeholder="Enter first name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addLastname" class="form-label fw-semibold">Last Name</label>
                                <input type="text" class="form-control" id="addLastname" placeholder="Enter last name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="addPassword" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control" id="addPassword" placeholder="At least 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label for="addConfirmPassword" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control" id="addConfirmPassword" placeholder="Re-enter password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="addIsAdmin">
                            <label class="form-check-label fw-semibold" for="addIsAdmin">Administrator</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center">
                    <button type="button" class="btn btn-primary px-5 fw-semibold" onclick="handleAddUser()">Add User</button>
                </div>

            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../app.js"></script>
    <script>
        let addUserModal;

        document.addEventListener('DOMContentLoaded', async function() {
            addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            await loadUsers();
        });

        async function loadUsers(search = '') {
            const users = await getAllUsers(search);
            displayUsers(users);
        }

        const debouncedSearch = debounce(async function() {
            const searchTerm = document.getElementById('searchInput').value;
            await loadUsers(searchTerm);
        }, 500);

        document.getElementById('searchInput').addEventListener('input', debouncedSearch);

        async function handleAddUser() {
            const username = document.getElementById('addUsername').value;
            const firstname = document.getElementById('addFirstname').value;
            const lastname = document.getElementById('addLastname').value;
            const password = document.getElementById('addPassword').value;
            const confirmPassword = document.getElementById('addConfirmPassword').value;
            const isAdmin = document.getElementById('addIsAdmin').checked;

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Passwords do not match'
                });
                return;
            }

            const success = await addUser(username, firstname, lastname, password, isAdmin);

            if (success) {
                addUserModal.hide();
                document.getElementById('addUserForm').reset();
                await loadUsers();
            }
        }
    </script>
</body>

</html>