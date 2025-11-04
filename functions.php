<?php
// validate username
function validateUsername($username)
{
    if (empty($username)) {
        return ['valid' => false, 'message' => 'Username is required'];
    }
    if (strlen($username) < 3) {
        return ['valid' => false, 'message' => 'Username must be at least 3 characters'];
    }
    return ['valid' => true];
}

// validate password
function validatePassword($password)
{
    if (empty($password)) {
        return ['valid' => false, 'message' => 'Password is required'];
    }
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters'];
    }
    return ['valid' => true];
}

// validate name fields
function validateName($name, $field)
{
    if (empty($name)) {
        return ['valid' => false, 'message' => ucfirst($field) . ' is required'];
    }
    return ['valid' => true];
}

// check if username exists
function usernameExists($conn, $username)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// hash password
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// verify password
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

// sanitize input
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
