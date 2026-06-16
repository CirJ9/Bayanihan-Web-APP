<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        // If not logged in, go to login page
        header("Location: ../auth/login.php");
        exit();
    }
}

function requireRole($role) {
    // If logged in but wrong role (e.g. user trying to access admin)
    if ($_SESSION['role'] !== $role) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit();
    }
}