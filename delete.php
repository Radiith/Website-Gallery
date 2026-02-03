<?php
// Include required files
require_once 'config/database.php';
require_once 'models/Image.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?message=delete_error');
    exit;
}

$id = $_GET['id'];

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Get image details first to delete the file
$imageModel = new Image($db);
$image = $imageModel->getById($id);

if ($image) {
    // Delete the file
    $filepath = 'uploads/' . $image['filename'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Delete from database
    if ($imageModel->delete($id)) {
        header('Location: index.php?message=deleted');
    } else {
        header('Location: index.php?message=delete_error');
    }
} else {
    header('Location: index.php?message=delete_error');
}
exit;