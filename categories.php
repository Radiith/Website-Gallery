<?php
require_once 'config/session.php';
require_once 'config/database.php';
require_once 'models/Category.php';

requireLogin();

$database = new Database();
$db = $database->connect();
$categoryModel = new Category($db);

$user_id = getUserId();
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = $_POST['name'] ?? '';
            $color = $_POST['color'] ?? '#667eea';
            
            if (empty($name)) {
                $error = 'Nama kategori harus diisi';
            } elseif ($categoryModel->exists($name, $user_id)) {
                $error = 'Kategori dengan nama tersebut sudah ada';
            } else {
                if ($categoryModel->create($name, $color, $user_id)) {
                    $success = 'Kategori berhasil ditambahkan!';
                } else {
                    $error = 'Gagal menambahkan kategori';
                }
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if ($id > 0) {
                if ($categoryModel->delete($id, $user_id)) {
                    $success = 'Kategori berhasil dihapus!';
                } else {
                    $error = 'Gagal menghapus kategori';
                }
            }
            break;
    }
}

// Get all categories (global + user's)
$stmt = $categoryModel->getAll($user_id);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Color options
$colorOptions = [
    '#ef4444' => 'Red',
    '#f59e0b' => 'Orange',
    '#10b981' => 'Green',
    '#3b82f6' => 'Blue',
    '#8b5cf6' => 'Purple',
    '#ec4899' => 'Pink',
    '#667eea' => 'Indigo',
    '#000000' => 'Black'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .category-card {
            transition: all 0.3s ease;
            background: white;
        }
        
        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .color-preview {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
        }
        
        .color-option {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border-color: #374151;
            transform: scale(1.1);
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
                        <?php echo htmlspecialchars(getUsername()); ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="min-h-screen px-4 md:px-8 lg:px-16 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Kelola Kategori</h1>
            <p class="text-gray-600">
                Tambah, edit, atau hapus kategori untuk foto Anda
            </p>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div class="bg-white rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Tambah Kategori Baru</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kategori *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               placeholder="Contoh: Liburan Keluarga"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Warna
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($colorOptions as $color => $name): ?>
                                <div class="color-option" 
                                     style="background: <?php echo $color; ?>"
                                     onclick="selectColor('<?php echo $color; ?>')"
                                     data-color="<?php echo $color; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="color" name="color" value="#667eea">
                    </div>
                </div>
                
                <div>
                    <div class="flex items-center gap-4">
                        <div id="colorPreview" class="color-preview" style="background: #667eea"></div>
                        <span id="colorName" class="text-sm text-gray-600">Warna yang dipilih: Indigo</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-lg">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Kategori</span>
                </button>
            </form>
        </div>

        <!-- Categories List -->
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Daftar Kategori</h2>
            
            <?php if (empty($categories)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-tags text-gray-300 text-4xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada kategori</h3>
                    <p class="text-gray-500">Tambahkan kategori pertama Anda di atas</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($categories as $category): ?>
                        <div class="category-card rounded-lg border border-gray-100 p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="color-preview" style="background: <?php echo $category['color']; ?>"></div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                                        <p class="text-xs text-gray-500"><?php echo $category['user_id'] ? 'Kategori pribadi' : 'Kategori global'; ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($category['user_id'] == $user_id || $category['user_id'] === null): ?>
                                    <form method="POST" onsubmit="return confirm('Hapus kategori ini? Foto dengan kategori ini akan kehilangan kategorinya.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-sm text-gray-600">
                                <p class="font-mono text-xs bg-gray-100 px-2 py-1 rounded inline-block">
                                    <?php echo htmlspecialchars($category['slug']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Color selection
        function selectColor(color) {
            document.getElementById('color').value = color;
            document.getElementById('colorPreview').style.background = color;
            
            // Update color name
            const colorNames = {
                '#ef4444': 'Merah',
                '#f59e0b': 'Oranye',
                '#10b981': 'Hijau',
                '#3b82f6': 'Biru',
                '#8b5cf6': 'Ungu',
                '#ec4899': 'Merah Muda',
                '#667eea': 'Indigo',
                '#000000': 'Hitam'
            };
            
            document.getElementById('colorName').textContent = 'Warna yang dipilih: ' + (colorNames[color] || 'Kustom');
            
            // Update selected state
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('selected');
                if (option.getAttribute('data-color') === color) {
                    option.classList.add('selected');
                }
            });
        }
        
        // Set initial color
        selectColor('#667eea');
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            if (!name) {
                e.preventDefault();
                alert('Nama kategori harus diisi');
                document.getElementById('name').focus();
            }
        });
        
        // Auto-hide messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-red-50, .bg-green-50');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s ease';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>