<?php
session_start();
require_once 'includes/ProductManager.php';
require_once 'includes/NewModelManager.php';

$productManager = new ProductManager();
$newModelManager = new NewModelManager();

$digitalProducts = $productManager->getDigitalProducts();
foreach ($digitalProducts as &$p) { $p['type'] = 'digital'; }

$hardwareProducts = $productManager->getHardwareProducts();
foreach ($hardwareProducts as &$p) { $p['type'] = 'hardware'; }

$allProducts = array_merge($digitalProducts, $hardwareProducts);

$newModels = $newModelManager->getNewModels();

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!empty($_POST['product_id']) && !empty($_POST['product_type'])) {
                    $result = $newModelManager->addNewModel($_POST);
                    $message = $result ? 'New model added successfully!' : 'Error adding new model or model already exists.';
                    $messageType = $result ? 'success' : 'error';
                } else {
                    $message = 'Invalid product data.';
                    $messageType = 'error';
                }
                break;

            case 'remove':
                if (!empty($_POST['product_id'])) {
                    $result = $newModelManager->removeNewModel($_POST['product_id']);
                    $message = $result ? 'Model removed successfully!' : 'Error removing model.';
                    $messageType = $result ? 'success' : 'error';
                } else {
                    $message = 'Invalid product ID.';
                    $messageType = 'error';
                }
                break;
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . urlencode($message) . '&type=' . $messageType);
        exit;
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Models - LUSANO Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .no-image-placeholder {
            width: 100%;
            height: 150px;
            background: var(--admin-card-bg);
            border: 2px dashed var(--admin-border);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--admin-text-muted);
            font-size: 0.875rem;
        }
        
        .no-image-placeholder i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="admin-sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="../assets/image/lusano_logo_white.avif" alt="LUSANO Admin">
                <h3 style="color: var(--admin-primary); margin-top: 1rem;">Admin Panel</h3>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="index.php" class="nav-item"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                </div>
                  <div class="nav-section">
                    <div class="nav-section-title">Content</div>
                    <a href="carousel-banners.php" class="nav-item ">
                        <i class="fas fa-images"></i>
                        Carousel Banners
                    </a>
                    <a href="youtube-videos.php" class="nav-item ">
                        <i class="fab fa-youtube"></i>
                        YouTube Videos
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Products</div>
                    <a href="digital-products.php" class="nav-item"><i class="fas fa-lock"></i>Digital Locks</a>
                    <a href="categories.php" class="nav-item"><i class="fas fa-tags"></i>Digital Categories</a>
                    <a href="hardware-products.php" class="nav-item"><i class="fas fa-tools"></i>Hardware Products</a>
                     <a href="hardware-categories.php" class="nav-item">
                        <i class="fas fa-wrench"></i>
                        Hardware Categories
                    </a>
                    <a href="new-models.php" class="nav-item active"><i class="fas fa-star"></i>New Models</a>
                    <a href="color-variants.php" class="nav-item">
                        <i class="fas fa-palette"></i>
                        Color Variants
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <!-- <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i>Settings</a> -->
                    <a href="../index.html" class="nav-item" target="_blank"><i class="fas fa-external-link-alt"></i>View Website</a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1>New Models Management</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">Manage products displayed in the New Models section</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                </div>
            </header>

            <div class="admin-content">
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo ($messageType === 'error' ? 'danger' : 'success'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Current New Models -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Current New Models (<?php echo count($newModels); ?>/3)</h3>
                        <p style="color: var(--admin-text-muted); margin: 0; font-size: 0.875rem;">Maximum 3 products can be displayed</p>
                    </div>
                    
                    <div class="models-grid">
                        <?php if (!empty($newModels)): ?>
                        <?php foreach ($newModels as $model): ?>
                        <div class="model-card">
                            <div class="model-image">
                                <?php 
                                $imagePath = isset($model['image']) ? trim($model['image']) : '';
                                $title = isset($model['title']) ? $model['title'] : 'Untitled';
                                
                                // Debug: Check if file exists
                                $fileExists = file_exists($imagePath);
                                // echo ' Debug: Image path: ' . htmlspecialchars($imagePath) . ', File exists: ' . ($fileExists ? 'Yes' : 'No') . ' ';
                                
                                if ($fileExists) {
                                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($title) . '" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">';
                                } else {
                                    echo '<div class="no-image-placeholder"><i class="fas fa-image"></i><span>Image Missing</span><small style="font-size: 0.75rem; margin-top: 0.25rem;">' . htmlspecialchars($imagePath) . '</small></div>';
                                }
                                ?>
                            </div>
                            <div class="model-info">
                                <h4><?php echo htmlspecialchars($title); ?></h4>
                                <p><?php echo htmlspecialchars(isset($model['description']) ? $model['description'] : ''); ?></p>
                                <div class="model-price">From <strong>$<?php echo number_format(isset($model['price']) ? $model['price'] : 0); ?></strong></div>
                                <div class="model-category">
                                    <span class="category-badge <?php echo isset($model['type']) ? $model['type'] : 'digital'; ?>">
                                        <?php echo ucfirst(isset($model['type']) ? $model['type'] : 'digital'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="model-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($model['product_id']); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this model from New Models section?')">
                                        <i class="fas fa-trash"></i>Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p>No models added yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add New Model -->
                <?php if (count($newModels) < 3): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Model</h3>
                        <p style="color: var(--admin-text-muted); margin: 0; font-size: 0.875rem;">Select a product to add</p>
                    </div>
                    
                    <div class="filter-section">
                        <label>Filter by Category:</label>
                        <select id="categoryFilter" class="form-control">
                            <option value="">All Products</option>
                            <option value="digital">Digital Lock Products</option>
                            <option value="hardware">Hardware Products</option>
                        </select>
                    </div>

                    <div class="products-selection">
                        <?php 
                        $usedProductIds = array_column($newModels, 'product_id');
                        foreach ($allProducts as $product): 
                            if (in_array($product['id'], $usedProductIds)) continue;
                            $productType = $product['type'];
                        ?>
                        <div class="product-selection-card" data-category="<?php echo $productType; ?>">
                            <div class="product-image">
                                <?php 
                                $imagePath = isset($product['image']) ? trim($product['image']) : '';
                                $title = isset($product['title']) ? $product['title'] : 'Untitled';
                                
                                // Debug: Check if file exists
                                $fileExists = file_exists($imagePath);
                                // echo ' Debug: Image path: ' . htmlspecialchars($imagePath) . ', File exists: ' . ($fileExists ? 'Yes' : 'No') . ' ';
                                
                                if ($fileExists) {
                                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($title) . '" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">';
                                } else {
                                    echo '<div class="no-image-placeholder"><i class="fas fa-image"></i><span>Image Missing</span><small style="font-size: 0.75rem; margin-top: 0.25rem;">' . htmlspecialchars($imagePath) . '</small></div>';
                                }
                                ?>
                            </div>
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($title); ?></h4>
                                <p><?php echo htmlspecialchars(isset($product['badge']) ? $product['badge'] : ''); ?></p>
                                <div class="product-price">$<?php echo number_format(isset($product['price']) ? $product['price'] : 0); ?></div>
                                <div class="product-category">
                                    <span class="category-badge <?php echo $productType; ?>"><?php echo ucfirst($productType); ?></span>
                                </div>
                            </div>
                            <div class="product-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <input type="hidden" name="product_type" value="<?php echo $productType; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i>Add
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="admin-card">
                    <h3 class="card-title">Maximum Models Reached</h3>
                    <p style="color: var(--admin-text-muted); margin: 0;">You have 3 models already. Remove one to add new.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script>
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const selectedCategory = this.value;
            const productCards = document.querySelectorAll('.product-selection-card');
            productCards.forEach(card => {
                card.style.display = (selectedCategory === '' || card.dataset.category === selectedCategory) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
