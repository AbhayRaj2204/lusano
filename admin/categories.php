<?php
session_start();
require_once 'includes/CategoryManager.php';

$categoryManager = new CategoryManager();

$action = $_GET['action'] ?? 'list';
$categoryId = $_GET['id'] ?? null;
$category = null;

if ($action === 'edit' && $categoryId) {
    $category = $categoryManager->getCategory($categoryId);
    if (!$category) {
        header('Location: categories.php?error=Category not found');
        exit;
    }
}

$categories = $categoryManager->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - LUSANO Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
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
                    <a href="digital-products.php" class="nav-item">
                        <i class="fas fa-lock"></i>
                        Digital Locks
                    </a>
                    <a href="categories.php" class="nav-item active">
                        <i class="fas fa-tags"></i>
                        Digital Categories
                    </a>
                    <a href="hardware-products.php" class="nav-item">
                        <i class="fas fa-tools"></i>
                        Hardware Products
                    </a>
                    <a href="hardware-categories.php" class="nav-item">
                        <i class="fas fa-wrench"></i>
                        Hardware Categories
                    </a>
                      <a href="new-models.php" class="nav-item">
                        <i class="fas fa-star"></i>
                        New Models
                    </a>
                    <a href="color-variants.php" class="nav-item">
                        <i class="fas fa-palette"></i>
                        Color Variants
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
                    <!-- <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a> -->
                    <a href="../index.html" class="nav-item" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        View Website
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1><?php echo $action === 'add' ? 'Add New' : ($action === 'edit' ? 'Edit' : 'Manage'); ?> Categories</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">
                        <?php echo $action === 'list' ? 'Manage digital lock product categories and filters' : 'Configure category details and appearance'; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if ($action === 'list'): ?>
                    <a href="categories.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Category
                    </a>
                    <?php else: ?>
                    <a href="categories.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'list'): ?>
                <!-- Category List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Digital Lock Categories (<?php echo count($categories); ?>)</h3>
                        <div class="flex gap-2">
                            <input type="text" placeholder="Search categories..." class="form-input" style="width: 250px;" id="search-input">
                            <select class="form-input form-select" style="width: 150px;" id="status-filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="categories-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Products</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div style="background: var(--admin-primary); color: white; padding: 0.5rem; border-radius: 0.5rem; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                                <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                    ID: <?php echo htmlspecialchars($cat['id']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <p style="margin: 0; color: var(--admin-text-muted);">
                                            <?php echo htmlspecialchars($cat['description']); ?>
                                        </p>
                                    </td>
                                    <td>
                                        <?php
                                        // Count products in this category
                                        require_once 'includes/ProductManager.php';
                                        $productManager = new ProductManager();
                                        $products = $productManager->getDigitalProducts();
                                        $count = 0;
                                        foreach ($products as $product) {
                                            if (strpos($product['categories'], $cat['id']) !== false) {
                                                $count++;
                                            }
                                        }
                                        ?>
                                        <span style="background: var(--admin-accent); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">
                                            <?php echo $count; ?> products
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background: <?php echo $cat['status'] === 'active' ? 'var(--admin-success)' : 'var(--admin-secondary)'; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($cat['status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                        <?php echo date('M j, Y', strtotime($cat['created_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" 
                                               class="btn btn-secondary btn-sm" data-tooltip="Edit Category">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteCategory('<?php echo $cat['id']; ?>')" 
                                                    class="btn btn-danger btn-sm" data-tooltip="Delete Category"
                                                    <?php echo $count > 0 ? 'disabled title="Cannot delete category with products"' : ''; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Category Usage Statistics -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Category Usage Statistics</h3>
                    </div>
                    
                    <div class="stats-grid">
                        <?php foreach ($categories as $cat): ?>
                        <?php
                        $products = $productManager->getDigitalProducts();
                        $count = 0;
                        foreach ($products as $product) {
                            if (strpos($product['categories'], $cat['id']) !== false) {
                                $count++;
                            }
                        }
                        ?>
                        <div class="stat-card">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div style="background: var(--admin-primary); color: white; padding: 0.75rem; border-radius: 0.5rem;">
                                    <i class="fas <?php echo $cat['icon']; ?>"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0;"><?php echo $cat['name']; ?></h4>
                                    <p style="margin: 0; color: var(--admin-text-muted); font-size: 0.875rem;"><?php echo $cat['description']; ?></p>
                                </div>
                            </div>
                            <div class="stat-value"><?php echo $count; ?></div>
                            <div class="stat-label">Products</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php else: ?>
                <!-- Add/Edit Form -->
                <form id="category-form" data-validate>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($category): ?>
                    <input type="hidden" name="original_id" value="<?php echo $category['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Basic Information -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Category Information</h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Category ID *</label>
                                <input type="text" name="id" class="form-input" required 
                                       value="<?php echo $category['id'] ?? ''; ?>"
                                       placeholder="e.g., fingerprint, keypad, wifi"
                                       <?php echo $category ? 'readonly' : ''; ?>>
                                <small style="color: var(--admin-text-muted);">Unique identifier for the category (cannot be changed after creation)</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="name" class="form-input" required 
                                       value="<?php echo $category['name'] ?? ''; ?>"
                                       placeholder="e.g., Fingerprint, Keypad, Wi-Fi/App">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Icon Class *</label>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <input type="text" name="icon" class="form-input" required 
                                       value="<?php echo $category['icon'] ?? ''; ?>"
                                       placeholder="fa-fingerprint"
                                       style="flex: 1;">
                                <div id="icon-preview" style="background: var(--admin-primary); color: white; padding: 1rem; border-radius: 0.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    <i class="fas <?php echo $category['icon'] ?? 'fa-tag'; ?>"></i>
                                </div>
                            </div>
                            <small style="color: var(--admin-text-muted);">FontAwesome icon class (e.g., fa-fingerprint, fa-keyboard, fa-wifi)</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-input form-textarea" required 
                                      placeholder="Brief description of this category"><?php echo $category['description'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input form-select">
                                <option value="active" <?php echo ($category['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($category['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <small style="color: var(--admin-text-muted);">Inactive categories won't appear in product filters</small>
                        </div>
                    </div>

                    <!-- Icon Selection Helper -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Popular Icons</h3>
                            <small style="color: var(--admin-text-muted);">Click an icon to use it</small>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem;">
                            <?php
                            $popularIcons = [
                                'fa-fingerprint' => 'Fingerprint',
                                'fa-keyboard' => 'Keypad',
                                'fa-wifi' => 'Wi-Fi',
                                'fa-mobile-alt' => 'Mobile App',
                                'fa-key' => 'Key',
                                'fa-lock' => 'Lock',
                                'fa-shield-alt' => 'Security',
                                'fa-bluetooth' => 'Bluetooth',
                                'fa-qrcode' => 'QR Code',
                                'fa-id-card' => 'Card Access',
                                'fa-eye' => 'Biometric',
                                'fa-microchip' => 'Smart Tech'
                            ];
                            
                            foreach ($popularIcons as $iconClass => $iconName):
                            ?>
                            <div class="icon-option" onclick="selectIcon('<?php echo $iconClass; ?>')" 
                                 style="background: var(--admin-card-hover); border: 1px solid var(--admin-border); border-radius: 0.5rem; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s ease;">
                                <i class="fas <?php echo $iconClass; ?>" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--admin-primary);"></i>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo $iconName; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-2 justify-between">
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <div class="flex gap-2">
                            <!-- <button type="button" class="btn btn-outline" onclick="previewCategory()">
                                <i class="fas fa-eye"></i>
                                Preview
                            </button> -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $action === 'edit' ? 'Update Category' : 'Create Category'; ?>
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script src="assets/js/categories.js"></script>
</body>
</html>
