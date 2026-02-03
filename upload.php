<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'models/Image.php';
require_once 'models/Category.php';

requireLogin();

$database = new Database();
$db = $database->connect();

$imageModel = new Image($db);
$categoryModel = new Category($db);

$user_id = getUserId();

// Get categories for dropdown
$categoriesStmt = $categoryModel->getAll($user_id);
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $upload_date = $_POST['upload_date'] ?? date('Y-m-d');
    
    // Validate
    if (empty($name)) {
        $error = 'Nama foto harus diisi';
    } elseif (empty($category_id)) {
        $error = 'Kategori harus dipilih';
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'File foto harus diupload';
    } else {
        // Validate file
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 10 * 1024 * 1024; // 100MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP';
        } elseif ($file['size'] > $max_size) {
            $error = 'Ukuran file terlalu besar. Maksimal 100MB';
        } else {
            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $upload_path = 'uploads/' . $filename;
            
            // Create uploads directory if not exists
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Save to database
                if ($imageModel->create($name, $filename, $category_id, $upload_date, $user_id)) {
                    $success = 'Foto berhasil diupload!';
                    $_POST = []; // Clear form
                } else {
                    $error = 'Gagal menyimpan data ke database';
                    unlink($upload_path); // Delete uploaded file
                }
            } else {
                $error = 'Gagal mengupload file';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .upload-container {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .file-upload {
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        
        .file-upload.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
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
        
        .category-option {
            padding: 0.5rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .category-option:hover {
            background-color: #f3f4f6;
        }
        
        .category-option.selected {
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="index.php" class="flex items-center gap-3 text-gray-700 hover:text-blue-600">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Galeri</span>
                </a>
                <div class="text-gray-600">
                    <i class="fas fa-user-circle mr-2"></i>
                    <?php echo htmlspecialchars(getUsername()); ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="upload-container overflow-hidden">
            <!-- Header -->
            <div class="p-8 border-b border-gray-100">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Upload Foto Baru</h1>
                <p class="text-gray-600">Tambahkan foto ke galeri Anda</p>
            </div>

            <!-- Messages -->
            <?php if ($error): ?>
                <div class="m-8 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="m-8 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" class="p-8">
                <div class="space-y-6">
                    <!-- Photo Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Foto *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               placeholder="Contoh: Foto Kegiatan Olahraga"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>

                    <!-- Category Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Kategori *
                        </label>
                        <?php if (empty($categories)): ?>
                            <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Belum ada kategori. 
                                <a href="categories.php" class="text-blue-600 hover:underline">Tambah kategori terlebih dahulu</a>
                            </div>
                        <?php else: ?>
                            <select id="category_id" 
                                    name="category_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                        style="color: <?php echo $cat['color']; ?>"
                                        <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <?php echo $cat['user_id'] ? ' (Pribadi)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <div class="mt-2 text-sm text-gray-600">
                                <a href="categories.php" class="text-blue-600 hover:underline">
                                    <i class="fas fa-plus mr-1"></i>Tambah kategori baru
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Upload Date -->
                    <div>
                        <label for="upload_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Upload
                        </label>
                        <input type="date" 
                               id="upload_date" 
                               name="upload_date" 
                               value="<?php echo htmlspecialchars($_POST['upload_date'] ?? date('Y-m-d')); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Foto *
                        </label>
                        <div class="file-upload p-8 text-center"
                             onclick="document.getElementById('image').click()"
                             id="dropArea">
                            <input type="file" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*"
                                   class="hidden"
                                   onchange="previewImage(event)">
                            
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600 mb-2">
                                <span class="text-blue-600 font-medium">Klik untuk upload</span> atau drag & drop
                            </p>
                            <p class="text-gray-500 text-sm">
                                Format: JPG, PNG, GIF, WebP • Maks: 100MB
                            </p>
                            
                            <!-- Preview -->
                            <div id="previewContainer" class="mt-6 hidden">
                                <img id="preview" 
                                     src="" 
                                     alt="Preview" 
                                     class="mx-auto max-w-xs max-h-48 rounded-lg shadow-md">
                                <p id="fileName" class="text-gray-700 text-sm mt-2"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                                class="btn-primary w-full py-3 rounded-lg font-medium">
                            <i class="fas fa-cloud-upload-alt mr-2"></i>
                            Upload Foto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('image');
        const previewContainer = document.getElementById('previewContainer');
        const preview = document.getElementById('preview');
        const fileName = document.getElementById('fileName');

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('dragover');
        }

        function unhighlight() {
            dropArea.classList.remove('dragover');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                previewImage({ target: fileInput });
            }
        }

        // Preview image
        function previewImage(event) {
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    fileName.textContent = file.name;
                    previewContainer.classList.remove('hidden');
                }
                
                reader.readAsDataURL(file);
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const category = document.getElementById('category_id');
            const file = document.getElementById('image').files[0];
            
            if (!name) {
                e.preventDefault();
                alert('Nama foto harus diisi');
                document.getElementById('name').focus();
                return;
            }
            
            if (!category || !category.value) {
                e.preventDefault();
                alert('Kategori harus dipilih');
                category.focus();
                return;
            }
            
            if (!file) {
                e.preventDefault();
                alert('Silakan pilih file foto');
                return;
            }
        });

        // Auto-redirect on success
        <?php if ($success): ?>
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>