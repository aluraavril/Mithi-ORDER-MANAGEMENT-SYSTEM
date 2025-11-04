<?php
// start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// check if user is admin
function isAdmin()
{
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// get current user data
function getCurrentUser()
{
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'firstname' => $_SESSION['firstname'],
            'lastname' => $_SESSION['lastname'],
            'is_admin' => $_SESSION['is_admin']
        ];
    }
    return null;
}

// set user session
function setUserSession($user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['firstname'] = $user['firstname'];
    $_SESSION['lastname'] = $user['lastname'];
    $_SESSION['is_admin'] = $user['is_admin'];
}

// destroy user session
function destroyUserSession()
{
    session_unset();
    session_destroy();
}

// redirect if not logged in
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// redirect if not admin
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
