<?php
session_start();

// Simple session functions langsung di index.php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? null;
}
require_once 'config/database.php';
require_once 'models/Image.php';
require_once 'models/Category.php';

requireLogin(); // Require login to access

$database = new Database();
$db = $database->connect();

$imageModel = new Image($db);
$categoryModel = new Category($db);

$user_id = getUserId();
$current_username = getUsername();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'like':
                if (isset($_POST['id'])) {
                    $imageModel->addLike($_POST['id']);
                }
                header('Location: index.php');
                exit;
                
            case 'delete':
                if (isset($_POST['id'])) {
                    $imageModel->delete($_POST['id'], $user_id);
                    $_SESSION['message'] = 'Gambar berhasil dihapus!';
                }
                header('Location: index.php');
                exit;
        }
    }
}

// Get filter parameters
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Get data
$limit = 6;
$offset = ($page - 1) * $limit;

$stmt = $imageModel->getAll($limit, $offset, $user_id, $category_id);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$featuredStmt = $imageModel->getFeatured(3);
$featuredImages = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter and form
$categoriesStmt = $categoryModel->getAll($user_id);
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get totals
$totalImages = $imageModel->getTotal($user_id, $category_id);
$totalPages = ceil($totalImages / $limit);
$hasMore = $page < $totalPages;

// Get user's images count
$userImagesCount = $imageModel->getUserImagesCount($user_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Photo Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f9fafb;
        }
        
        .gallery-item {
            transition: all 0.3s ease;
            background: white;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .heart-icon {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .heart-icon:hover {
            transform: scale(1.1);
        }
        
        .heart-icon.active {
            fill: #ef4444;
            stroke: #ef4444;
        }
        
        .featured-image {
            transition: transform 0.5s ease;
            height: 294px;
        }
        
        .featured-image:hover {
            transform: scale(1.05);
        }
        
        .delete-btn {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .delete-btn {
            opacity: 1;
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
        
        .user-menu {
            position: relative;
        }
        
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
            overflow: hidden;
        }
        
        .user-menu:hover .user-dropdown {
            display: block;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="flex items-center gap-3">
                        <i class="fas fa-images text-2xl text-blue-600"></i>
                        <span class="text-xl font-bold text-gray-900">Gallery</span>
                    </a>
                    <div class="text-sm text-gray-600">
                        Selamat datang, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($current_username); ?></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-6">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="categories.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        <i class="fas fa-tags mr-2"></i>Kategori
                    </a>
                    <a href="upload.php" class="btn-primary inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload</span>
                    </a>
                    
                    <!-- User Menu -->
                    <div class="user-menu">
                        <button class="flex items-center gap-2 text-gray-700 hover:text-blue-600">
                            <i class="fas fa-user-circle text-xl"></i>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="user-dropdown">
                            <div class="p-4 border-b border-gray-100">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($current_username); ?></p>
                                <p class="text-sm text-gray-600"><?php echo $userImagesCount; ?> foto</p>
                            </div>
                            <div class="p-2">
                                <a href="profile.php" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <a href="logout.php" class="block px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen px-4 md:px-8 lg:px-16 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">Galeri Foto</h1>
            <p class="text-gray-600">
                Foto dan video diunggah dari halaman Kalender & Aktivitas.
            </p>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span><?php echo $_SESSION['message']; ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Featured Images Banner -->
            <?php if (!empty($featuredImages)): ?>
                <div class="mb-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                        <?php foreach($featuredImages as $featured): ?>
                            <div class="featured-image rounded-2xl overflow-hidden">
                                <img 
                                    src="image/<?php echo htmlspecialchars($featured['filename']); ?>" 
                                    alt="Featured Image"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='image/idol-2.jpg'; this.onerror=null;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <!-- Filter Section -->
        <div class="bg-white rounded-xl p-6 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <span class="text-gray-700 font-medium">Filter:</span>
                    <form method="GET" class="flex items-center gap-4">
                        <select name="category" 
                                onchange="this.form.submit()"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Kategori</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php if ($category_id): ?>
                            <a href="index.php" class="px-4 py-2 text-gray-700 hover:text-blue-600">
                                <i class="fas fa-times mr-2"></i>Reset Filter
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="upload.php" class="btn-primary inline-flex items-center gap-2 px-5 py-2.5 rounded-lg">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Foto Baru</span>
                    </a>
                    <a href="categories.php" class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Kategori</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Gallery Grid -->
        <?php if (!empty($images)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="galleryGrid">
                <?php foreach($images as $image): ?>
                    <div class="gallery-item rounded-xl border border-gray-100 p-6 shadow-sm">
                        <!-- Image -->
                        <div class="relative mb-4">
                            <img 
                                src="uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                                alt="<?php echo htmlspecialchars($image['name']); ?>"
                                class="w-full h-48 object-cover rounded-lg"
                                onerror="this.src='https://images.unsplash.com/photo-1541963463532-d68292c34b19?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&q=80'">
                            
                            <!-- Delete Button -->
                            <form method="POST" class="absolute top-3 right-3 delete-btn">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                                <button type="submit" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')"
                                        class="p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors shadow-md">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Image Info -->
                        <div class="space-y-3">
                            <h3 class="text-xl font-semibold text-gray-900">
                                <?php echo htmlspecialchars($image['name']); ?>
                            </h3>
                            
                            <p class="text-gray-500 text-sm">
                                <i class="far fa-calendar mr-1"></i>
                                <?php echo htmlspecialchars($image['upload_date']); ?>
                            </p>
                            
                            <?php if ($image['category_name']): ?>
                                <div>
                                    <span class="category-badge" style="background: <?php echo $image['category_color'] ?? '#667eea'; ?>">
                                        <?php echo htmlspecialchars($image['category_name']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                <div class="text-sm text-gray-600">
                                    <i class="far fa-user mr-1"></i>
                                    <?php echo $image['user_id'] == $user_id ? 'Anda' : 'User ' . $image['user_id']; ?>
                                </div>
                                
                                <!-- Like Button -->
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="like">
                                    <input type="hidden" name="id" value="<?php echo $image['id']; ?>">
                                    <button type="submit" class="flex items-center gap-2 text-gray-600 hover:text-red-500">
                                        <span class="text-sm font-medium"><?php echo $image['likes']; ?></span>
                                        <svg class="w-5 h-5 heart-icon <?php echo $image['likes'] > 0 ? 'active' : ''; ?>" 
                                             viewBox="0 0 24 24"
                                             fill="<?php echo $image['likes'] > 0 ? '#ef4444' : 'none'; ?>"
                                             stroke="currentColor"
                                             stroke-width="2">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-images text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-3">Belum ada foto</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">
                    <?php echo $category_id ? 'Tidak ada foto pada kategori ini' : 'Mulai dengan mengupload foto pertama Anda ke galeri'; ?>
                </p>
                <div class="flex gap-4 justify-center">
                    <a href="upload.php" 
                       class="btn-primary inline-flex items-center gap-3 px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Foto Pertama</span>
                    </a>
                    <?php if ($category_id): ?>
                        <a href="index.php" 
                           class="inline-flex items-center gap-3 px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-images"></i>
                            <span>Lihat Semua Foto</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                    </a>
                <?php endif; ?>
                
                <?php 
                // Show page numbers
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                       class="px-4 py-2 rounded-lg font-medium <?php echo $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Berikutnya <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <p class="text-center text-gray-500 text-sm mt-4">
                Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?> • 
                Total <?php echo $totalImages; ?> foto
            </p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-100 py-8 mt-12">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p>&copy; <?php echo date('Y'); ?> Gallery System. All rights reserved.</p>
            <p class="text-sm mt-2">Logged in as <?php echo htmlspecialchars($current_username); ?></p>
        </div>
    </footer>

    <script>
        // Auto-hide message after 5 seconds
        setTimeout(() => {
            const message = document.querySelector('.bg-green-50');
            if (message) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);

        // Confirm before deleting
        document.querySelectorAll('form[action="delete"]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
                    e.preventDefault();
                }
            });
        });

        // Like animation
        document.querySelectorAll('.heart-icon').forEach(icon => {
            icon.addEventListener('click', function(e) {
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'svg' || e.target.tagName === 'path') {
                    this.classList.toggle('active');
                    if (this.classList.contains('active')) {
                        this.style.fill = '#ef4444';
                        this.style.stroke = '#ef4444';
                    } else {
                        this.style.fill = 'none';
                        this.style.stroke = 'currentColor';
                    }
                }
            });
        });
    </script>
</body>
</html>