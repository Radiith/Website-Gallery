    <?php
session_start();
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Image.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->connect();

$userModel = new User($db);
$imageModel = new Image($db);

$user_id = $_SESSION['user_id'];
$user = $userModel->getById($user_id);

// Get user's images count
$userImagesCount = $imageModel->getUserImagesCount($user_id);

// Messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Gallery System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border: 5px solid white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stats-card {
            transition: all 0.3s ease;
            background: white;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tab-button {
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .avatar-upload {
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .avatar-upload:hover {
            border-color: #667eea;
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="flex items-center gap-3">
                        <i class="fas fa-arrow-left text-gray-600"></i>
                        <span class="text-gray-700 hover:text-blue-600">Kembali ke Galeri</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-gray-600">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="profile-container px-4 py-8">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header p-8 text-white">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Profile Avatar -->
                <div class="relative">
                    <img src="uploads/profile/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default-avatar.jpg'); ?>" 
                         alt="Profile Picture"
                         class="profile-avatar rounded-full object-cover"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname'] ?? $user['username']); ?>&background=667eea&color=fff&size=150'">
                    
                    <!-- Upload Avatar Button -->
                    <button onclick="openTab('avatar')" 
                            class="absolute bottom-0 right-0 bg-white text-blue-600 p-2 rounded-full shadow-lg hover:bg-gray-100">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                
                <!-- Profile Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></h1>
                    <p class="text-lg opacity-90 mb-4">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <div class="flex flex-wrap gap-6 mb-6">
                        <div>
                            <p class="text-2xl font-bold"><?php echo $userImagesCount; ?></p>
                            <p class="text-sm opacity-90">Foto</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">
                                <?php 
                                $joinDate = new DateTime($user['created_at']);
                                echo $joinDate->format('d M Y');
                                ?>
                            </p>
                            <p class="text-sm opacity-90">Bergabung</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['bio'])): ?>
                        <p class="opacity-90 italic">"<?php echo htmlspecialchars($user['bio']); ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-images text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $userImagesCount; ?></p>
                        <p class="text-gray-600">Total Foto</p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-gray-600">Username</p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-envelope text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 truncate"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-gray-600">Email</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex flex-wrap gap-2 mb-8">
            <button onclick="openTab('edit')" 
                    class="tab-button px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 active">
                <i class="fas fa-user-edit mr-2"></i>Edit Profile
            </button>
            
            <button onclick="openTab('password')" 
                    class="tab-button px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fas fa-key mr-2"></i>Ubah Password
            </button>
            
            <button onclick="openTab('avatar')" 
                    class="tab-button px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                <i class="fas fa-camera mr-2"></i>Foto Profile
            </button>
        </div>

        <!-- Tab Contents -->
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <!-- Edit Profile Tab -->
            <div id="edit-tab" class="tab-content active">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Edit Informasi Profile</h2>
                <form action="update_profile.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fullname" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap
                            </label>
                            <input type="text" 
                                   id="fullname" 
                                   name="fullname" 
                                   value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                            Bio (Opsional)
                        </label>
                        <textarea id="bio" 
                                  name="bio" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tulis sedikit tentang diri Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                        <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>

            <!-- Change Password Tab -->
            <div id="password-tab" class="tab-content">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Ubah Password</h2>
                <form action="update_password.php" method="POST" class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Saat Ini *
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Baru *
                            </label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required
                                   minlength="6"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password *
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                            <i class="fas fa-key mr-2"></i>Ubah Password
                        </button>
                        <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>

            <!-- Avatar Upload Tab -->
            <div id="avatar-tab" class="tab-content">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Ubah Foto Profil</h2>
                <form action="upload_avatar.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Foto Baru
                        </label>
                        <div class="avatar-upload p-8 text-center"
                             onclick="document.getElementById('avatar_file').click()">
                            <input type="file" 
                                   id="avatar_file" 
                                   name="avatar_file" 
                                   accept="image/*"
                                   class="hidden"
                                   onchange="previewAvatar(event)">
                            
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600 mb-2">
                                <span class="text-blue-600 font-medium">Klik untuk upload</span> atau drag & drop
                            </p>
                            <p class="text-gray-500 text-sm">
                                Format: JPG, PNG, GIF • Maks: 2MB
                            </p>
                            
                            <!-- Preview -->
                            <div id="avatarPreview" class="mt-6 hidden">
                                <img id="avatar_preview" 
                                     src="" 
                                     alt="Preview" 
                                     class="mx-auto w-32 h-32 rounded-full object-cover shadow-md">
                                <p id="avatar_filename" class="text-gray-700 text-sm mt-2"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                            <i class="fas fa-upload mr-2"></i>Upload Foto Profile
                        </button>
                        <a href="index.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.currentTarget.classList.add('active');
        }

        // Avatar preview
        function previewAvatar(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('avatar_preview');
            const filename = document.getElementById('avatar_filename');
            const previewContainer = document.getElementById('avatarPreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    filename.textContent = file.name;
                    previewContainer.classList.remove('hidden');
                }
                
                reader.readAsDataURL(file);
            }
        }

        // Password validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        if (newPassword && confirmPassword) {
            newPassword.onchange = validatePassword;
            confirmPassword.onkeyup = validatePassword;
        }

        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-green-50, .bg-red-50');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s ease';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>