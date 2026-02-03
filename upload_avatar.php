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
    if (!isset($_FILES['avatar_file']) || $_FILES['avatar_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'File foto harus diupload';
    } else {
        $file = $_FILES['avatar_file'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF';
        } elseif ($file['size'] > $max_size) {
            $_SESSION['error'] = 'Ukuran file terlalu besar. Maksimal 2MB';
        } else {
            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $extension;
            
            // Create uploads/profile directory if not exists
            $upload_dir = 'uploads/profile/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $upload_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update database
                if ($userModel->updateProfilePicture($user_id, $filename)) {
                    $_SESSION['success'] = 'Foto profile berhasil diupload!';
                } else {
                    $_SESSION['error'] = 'Gagal menyimpan data ke database';
                    // Delete uploaded file if db save fails
                    unlink($upload_path);
                }
            } else {
                $_SESSION['error'] = 'Gagal mengupload file';
            }
        }
    }
}

header('Location: profile.php');
exit;
?>