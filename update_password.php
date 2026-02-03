<?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$userModel = new User($db);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Semua field harus diisi';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'Password minimal 6 karakter';
    } elseif (!$userModel->verifyPassword($user_id, $current_password)) {
        $_SESSION['error'] = 'Password saat ini salah';
    } else {
        // Update password
        if ($userModel->updatePassword($user_id, $new_password)) {
            $_SESSION['success'] = 'Password berhasil diubah!';
        } else {
            $_SESSION['error'] = 'Gagal mengubah password';
        }
    }
}

header('Location: profile.php');
exit;
?>