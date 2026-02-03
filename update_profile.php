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
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $bio = $_POST['bio'] ?? '';
    
    // Basic validation
    if (empty($email)) {
        $_SESSION['error'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format email tidak valid';
    } else {
        // Update profile
        if ($userModel->updateProfile($user_id, $fullname, $email, $bio)) {
            // Update session data
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            
            $_SESSION['success'] = 'Profile berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui profile';
        }
    }
}

header('Location: profile.php');
exit;
?>