<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'db.php';
require_once 'session.php';
require_once 'functions.php';


$conn = getDBConnection();
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'register':
        handleRegister($conn);
        break;
    case 'check_username':
        handleCheckUsername($conn);
        break;
    case 'get_users':
        handleGetUsers($conn);
        break;
    case 'add_user':
        handleAddUser($conn);
        break;
    case 'get_session':
        handleGetSession();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'suspend_user':
        handleSuspendUser($conn);
        break;
    case 'add_product':
        handleAddProduct($conn);
        break;

    case 'get_products':
        handleGetProducts($conn);
        break;

    case 'get_orders':
        handleGetOrders($conn);
        break;
    case 'save_order':
        handleSaveOrder($conn);
        break;
    case 'void_order':
        handleVoidOrder($conn);
        break;
    case 'print_report':
        handlePrintReport($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();

// handle login
function handleLogin($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    $username = sanitizeInput($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        return;
    }

    $stmt = $conn->prepare("SELECT id, username, firstname, lastname, is_admin, password, is_suspended FROM users WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['is_suspended'] == 1) {
            echo json_encode(['success' => false, 'message' => 'Your account is suspended.']);
            return;
        }


        if (verifyPassword($password, $user['password'])) {
            setUserSession($user);
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'is_admin' => $user['is_admin']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }

    $stmt->close();
}

// handle registration
function handleRegister($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);

    $username = sanitizeInput($data['username'] ?? '');
    $firstname = sanitizeInput($data['firstname'] ?? '');
    $lastname = sanitizeInput($data['lastname'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    // validate username
    $usernameValidation = validateUsername($username);
    if (!$usernameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $usernameValidation['message']]);
        return;
    }

    // check if username exists
    if (usernameExists($conn, $username)) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }

    // validate firstname
    $firstnameValidation = validateName($firstname, 'firstname');
    if (!$firstnameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $firstnameValidation['message']]);
        return;
    }

    // validate lastname
    $lastnameValidation = validateName($lastname, 'lastname');
    if (!$lastnameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $lastnameValidation['message']]);
        return;
    }

    // validate password
    $passwordValidation = validatePassword($password);
    if (!$passwordValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $passwordValidation['message']]);
        return;
    }

    // check if passwords match
    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }

    // hash password and insert user
    $hashedPassword = hashPassword($password);
    $stmt = $conn->prepare("INSERT INTO users (username, firstname, lastname, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $firstname, $lastname, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }

    $stmt->close();
}

// handle check username
function handleCheckUsername($conn)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $username = sanitizeInput($data['username'] ?? '');

    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        return;
    }

    $exists = usernameExists($conn, $username);
    echo json_encode(['success' => true, 'exists' => $exists]);
}

// handle get users (admin only)
function handleGetUsers($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT id, username, firstname, lastname, is_admin, is_suspended, date_added 
                                FROM users 
                                WHERE username LIKE ? OR firstname LIKE ? OR lastname LIKE ? 
                                ORDER BY date_added DESC");
        $searchParam = "%{$search}%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    } else {
        $stmt = $conn->prepare("SELECT id, username, firstname, lastname, is_admin, is_suspended, date_added 
                                FROM users 
                                ORDER BY date_added DESC");
    }


    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode(['success' => true, 'users' => $users]);
    $stmt->close();
}

// handle add user (admin only)
function handleAddUser($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $username = sanitizeInput($data['username'] ?? '');
    $firstname = sanitizeInput($data['firstname'] ?? '');
    $lastname = sanitizeInput($data['lastname'] ?? '');
    $password = $data['password'] ?? '';
    $isAdmin = isset($data['is_admin']) ? (int)$data['is_admin'] : 0;

    // validate username
    $usernameValidation = validateUsername($username);
    if (!$usernameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $usernameValidation['message']]);
        return;
    }

    // check if username exists
    if (usernameExists($conn, $username)) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }

    // validate firstname
    $firstnameValidation = validateName($firstname, 'firstname');
    if (!$firstnameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $firstnameValidation['message']]);
        return;
    }

    // validate lastname
    $lastnameValidation = validateName($lastname, 'lastname');
    if (!$lastnameValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $lastnameValidation['message']]);
        return;
    }

    // validate password
    $passwordValidation = validatePassword($password);
    if (!$passwordValidation['valid']) {
        echo json_encode(['success' => false, 'message' => $passwordValidation['message']]);
        return;
    }

    // hash password and insert user
    $hashedPassword = hashPassword($password);
    $stmt = $conn->prepare("INSERT INTO users (username, firstname, lastname, is_admin, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $username, $firstname, $lastname, $isAdmin, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user']);
    }

    $stmt->close();
}

// handle get session
function handleGetSession()
{
    if (isLoggedIn()) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => getCurrentUser()
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}


// handle suspend user (admin only)
function handleSuspendUser($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Admin access required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $userId = intval($data['user_id'] ?? 0);
    $suspend = intval($data['suspend'] ?? 0);

    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }

    // Prevent admin from suspending themselves
    $currentUser = getCurrentUser();
    if ($currentUser['id'] == $userId) {
        echo json_encode(['success' => false, 'message' => 'You cannot suspend your own account']);
        return;
    }

    $stmt = $conn->prepare("UPDATE users SET is_suspended = ? WHERE id = ?");
    $stmt->bind_param("ii", $suspend, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $suspend ? 'User suspended successfully' : 'User unsuspended successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update suspension status']);
    }

    $stmt->close();
}




// handle logout
function handleLogout()
{
    destroyUserSession();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function handleAddProduct($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $name = sanitizeInput($data['name'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $image = sanitizeInput($data['image'] ?? '');
    $category = sanitizeInput($data['category'] ?? 'Food');

    $user = getCurrentUser();
    $added_by_name = $user['firstname'] . ' ' . $user['lastname'];
    $added_by_role = ($user['is_admin'] == 1) ? 'Admin' : 'Cashier';

    if (empty($name) || $price <= 0 || empty($category)) {
        echo json_encode(['success' => false, 'message' => 'Invalid inputs']);
        return;
    }

    $stmt = $conn->prepare("
        INSERT INTO products (name, price, image, category, added_by_name, added_by_role)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sdssss", $name, $price, $image, $category, $added_by_name, $added_by_role);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
    }

    $stmt->close();
}


function handleGetProducts($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    // ✅ Include date_added in the SELECT statement
    $query = "SELECT id, name, price, image, category, added_by_name, added_by_role, date_added 
              FROM products 
              ORDER BY date_added DESC";

    $result = $conn->query($query);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode(['success' => true, 'products' => $products]);
}

function handleGetOrders($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $date_start = $_GET['date_start'] ?? null;
    $date_end = $_GET['date_end'] ?? null;
    $include_voided = isset($_GET['include_voided']) ? intval($_GET['include_voided']) : 0;
    $today = isset($_GET['today']) ? intval($_GET['today']) : 0;

    // If 'today=1' is passed, use server's current date for start and end
    if ($today) {
        $current_date = date('Y-m-d'); // Server's current date in Y-m-d format
        $date_start = $current_date;
        $date_end = $current_date;
    }

    // Base query
    $query = "SELECT id, items, total_amount, cashier_name, date_ordered, voided FROM orders WHERE 1";

    // Filter by date
    if ($date_start && $date_end) {
        $query .= " AND DATE(date_ordered) BETWEEN ? AND ?";
    } elseif ($date_start) {
        $query .= " AND DATE(date_ordered) >= ?";
    }

    // Exclude voided orders for "today’s transactions" or if not explicitly including them
    if (!$include_voided) {
        $query .= " AND voided = 0";
    }

    $query .= " ORDER BY date_ordered DESC";

    // Prepare and bind
    if ($date_start && $date_end) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $date_start, $date_end);
    } elseif ($date_start) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $date_start);
    } else {
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    // Temporary debug: Log the orders to server error log
    error_log('DEBUG handleGetOrders: Found ' . count($orders) . ' orders for query. Orders: ' . json_encode($orders));
    echo json_encode(['success' => true, 'orders' => $orders]);
    $stmt->close();
}




function handleSaveOrder($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $items = sanitizeInput($data['items'] ?? '');
    $total_amount = floatval($data['total_amount'] ?? 0);
    $cashier_name = sanitizeInput($data['cashier_name'] ?? '');

    $stmt = $conn->prepare("INSERT INTO orders (items, total_amount, cashier_name, date_ordered, voided) VALUES (?, ?, ?, NOW(), 0)");
    $stmt->bind_param("sds", $items, $total_amount, $cashier_name);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order saved']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save order']);
    }

    $stmt->close();
}


function handleVoidOrder($conn)
{
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Only admins can void transactions']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        return;
    }

    $stmt = $conn->prepare("UPDATE orders SET voided = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order voided']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to void order']);
    }

    $stmt->close();
}

function handlePrintReport($conn)
{
    require_once __DIR__ . '/vendor/autoload.php'; // if you installed dompdf via composer
    // OR use reportlab if you want (below uses dompdf-like approach)

    $date_start = $_GET['date_start'] ?? null;
    $date_end = $_GET['date_end'] ?? null;

    // Fetch all orders (include voided)
    $query = "SELECT id, items, total_amount, cashier_name, date_ordered, voided FROM orders WHERE 1";
    if ($date_start && $date_end) {
        $query .= " AND DATE(date_ordered) BETWEEN ? AND ?";
    } elseif ($date_start) {
        $query .= " AND DATE(date_ordered) >= ?";
    }
    $query .= " ORDER BY date_ordered DESC";

    if ($date_start && $date_end) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $date_start, $date_end);
    } elseif ($date_start) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $date_start);
    } else {
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    $total_sum = 0;

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        if ($row['voided'] == 0) {
            $total_sum += floatval($row['total_amount']);
        }
    }

    $stmt->close();

    // ✅ Generate HTML for PDF
    ob_start();
?>
    <h2 style="text-align:center;">Mithi Café + Bistro</h2>
    <h4 style="text-align:center;">Transaction History Report</h4>
    <?php if ($date_start || $date_end): ?>
        <p style="text-align:center;">From <strong><?= htmlspecialchars($date_start ?: 'N/A') ?></strong> to <strong><?= htmlspecialchars($date_end ?: 'N/A') ?></strong></p>
    <?php endif; ?>
    <table border="1" cellspacing="0" cellpadding="6" width="100%" style="border-collapse:collapse; font-size:12px;">
        <thead>
            <tr style="background:#f1f1f1;">
                <th>ID</th>
                <th>Items</th>
                <th>Total (₱)</th>
                <th>Cashier</th>
                <th>Date Ordered</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr style="<?= $o['voided'] ? 'background:#f8d7da;' : '' ?>">
                    <td><?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['items']) ?></td>
                    <td><?= number_format($o['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($o['cashier_name']) ?></td>
                    <td><?= htmlspecialchars($o['date_ordered']) ?></td>
                    <td><?= $o['voided'] ? 'Voided' : 'Active' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;font-weight:bold;">TOTAL (Active Only):</td>
                <td colspan="4" style="font-weight:bold;">PHP <?= number_format($total_sum, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <p style="margin-top:20px; text-align:right; font-size:11px;">Generated on <?= date('Y-m-d H:i:s') ?></p>
<?php
    $html = ob_get_clean();

    // ✅ Generate PDF using ReportLab-compatible lib (built-in for you)
    require_once 'pdf_generator.php'; // we'll create this next
    generatePDF('Transaction_Report.pdf', $html);
}
