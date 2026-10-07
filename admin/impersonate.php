<?php
session_start();
require_once '../config.php';

// Security: Check if current user is an admin
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$is_admin = $stmt->fetchColumn();

if (!$is_admin && !isset($_SESSION['admin_user_id'])) {
    die("Access Denied");
}

if (isset($_GET['action']) && $_GET['action'] === 'stop') {
    // Stop impersonation
    if (isset($_SESSION['admin_user_id'])) {
        $_SESSION['user_id'] = $_SESSION['admin_user_id'];
        unset($_SESSION['admin_user_id']);
        header("Location: users.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

if (isset($_GET['id'])) {
    $target_id = (int) $_GET['id'];

    // Check if target user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$target_id]);
    if ($stmt->fetchColumn()) {
        // Save current admin ID
        if (!isset($_SESSION['admin_user_id'])) {
            $_SESSION['admin_user_id'] = $user_id;
        }

        // Switch to target user
        $_SESSION['user_id'] = $target_id;
        header("Location: ../dashboard.php");
        exit;
    }
}

header("Location: users.php");
exit;
?>