<?php
session_start();
require_once 'includes/ColorVariantManager.php';

$colorVariantManager = new ColorVariantManager();
$action = $_GET['action'] ?? 'list';
$variantId = $_GET['id'] ?? null;
$variant = null;

if ($action === 'edit' && $variantId) {
    $variant = $colorVariantManager->getColorVariant($variantId);
    if (!$variant) {
        header('Location: color-variants.php?error=Variant not found');
        exit;
    }
}

$products = $colorVariantManager->getAllProducts();
$allVariants = $colorVariantManager->getColorVariants();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Variants - LUSANO Admin</title>
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
                    <a href="carousel-banners.php" class="nav-item">
                        <i class="fas fa-images"></i>
                        Carousel Banners
                    </a>
                    <a href="youtube-videos.php" class="nav-item">
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
                    <a href="categories.php" class="nav-item">
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
                    <a href="color-variants.php" class="nav-item active">
                        <i class="fas fa-palette"></i>
                        Color Variants
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">System</div>
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
                    <h1><?php echo $action === 'add' ? 'Add New' : ($action === 'edit' ? 'Edit' : 'Manage'); ?> Color Variants</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">
                        <?php echo $action === 'list' ? 'Manage color options and images for your products' : 'Configure color variant details'; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if ($action === 'list'): ?>
                    <a href="color-variants.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Color Variant
                    </a>
                    <?php else: ?>
                    <a href="color-variants.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'list'): ?>
                <!-- Color Variants List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Color Variants (<?php echo count($allVariants); ?>)</h3>
                        <div class="flex gap-2">
                            <input type="text" placeholder="Search variants..." class="form-input" style="width: 250px;" id="search-input">
                            <select class="form-input form-select" style="width: 200px;" id="product-filter">
                                <option value="">All Products</option>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['title']); ?> (<?php echo $product['category']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="variants-table">
                            <thead>
                                <tr>
                                    <th>Product & Color</th>
                                    <th>Color Code</th>
                                    <th>Images</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allVariants as $var): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($var['product_name']); ?></strong>
                                            <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($var['color_name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: <?php echo $var['color_hex']; ?>; border: 1px solid #ddd; border-radius: 0.25rem;"></div>
                                            <code><?php echo htmlspecialchars($var['color_hex']); ?></code>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <?php 
                                            $imageCount = 0;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if (!empty($var['image_' . $i])) {
                                                    $imageCount++;
                                                }
                                            }
                                            ?>
                                            <span style="background: var(--admin-accent); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                                <?php echo $imageCount; ?> image(s)
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.875rem;">
                                            <?php echo htmlspecialchars($var['product_category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background: <?php echo $var['status'] === 'active' ? 'var(--admin-success)' : 'var(--admin-secondary)'; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($var['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="color-variants.php?action=edit&id=<?php echo $var['id']; ?>" 
                                               class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteVariant('<?php echo $var['id']; ?>')" 
                                                    class="btn btn-danger btn-sm">
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

                <?php else: ?>
                <!-- Add/Edit Form -->
                <form id="color-variant-form" enctype="multipart/form-data" data-validate>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($variant): ?>
                    <input type="hidden" name="id" value="<?php echo $variant['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Product Selection -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Product Information</h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Select Product *</label>
                                <select name="product_id" id="product-select" class="form-input form-select" required>
                                    <option value="">-- Choose a Product --</option>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($product['title']); ?>"
                                            data-category="<?php echo $product['category']; ?>"
                                            <?php echo ($variant && $variant['product_id'] === $product['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($product['title']); ?> (<?php echo $product['category']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Color Name *</label>
                                <input type="text" name="color_name" class="form-input" required 
                                       value="<?php echo $variant['color_name'] ?? ''; ?>"
                                       placeholder="e.g., Matte Black, Silver, Rose Gold">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Color Code *</label>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <input type="color" name="color_hex" id="color-picker" class="form-input" required 
                                       value="<?php echo $variant['color_hex'] ?? '#000000'; ?>"
                                       style="width: 100px; height: 50px; border: 1px solid var(--admin-border); border-radius: 0.5rem; cursor: pointer;">
                                <input type="text" name="color_hex_text" class="form-input" id="color-hex-text" 
                                       value="<?php echo $variant['color_hex'] ?? '#000000'; ?>"
                                       placeholder="#000000"
                                       style="flex: 1;">
                            </div>
                        </div>
                    </div>

                    <!-- Images Section -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Color Variant Images</h3>
                            <small style="color: var(--admin-text-muted);">Upload up to 5 images for this color variant</small>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="form-group">
                                <label class="form-label">Image <?php echo $i; ?></label>
                                <div class="image-upload-wrapper" style="border: 2px dashed var(--admin-border); border-radius: 0.5rem; padding: 1rem; text-align: center; cursor: pointer; background: var(--admin-card-hover); min-height: 200px; display: flex; flex-direction: column; justify-content: center; align-items: center;" 
                                     id="upload-area-<?php echo $i; ?>"
                                     data-image-index="<?php echo $i; ?>"
                                     <?php if ($variant && !empty($variant['image_' . $i])): ?>
                                     data-has-image="true"
                                     data-image-src="<?php echo htmlspecialchars($variant['image_' . $i]); ?>"
                                     <?php else: ?>
                                     data-has-image="false"
                                     <?php endif; ?>>
                                    
                                    <?php if ($variant && !empty($variant['image_' . $i])): 
                                        $imgSrc = $variant['image_' . $i];
                                        if (strpos($imgSrc, '/uploads/') === 0) {
                                            $imgSrc = ltrim($imgSrc, '/');
                                        }
                                    ?>
                                    <!-- Display existing image with proper styling for edit mode -->
                                    <img class="preview-image" 
                                         src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                         alt="Preview <?php echo $i; ?>" 
                                         style="max-width: 100%; max-height: 200px; border-radius: 0.25rem; margin-bottom: 0.5rem; object-fit: contain;">
                                    <div style="color: var(--admin-text-muted); font-size: 0.875rem;">Click to change image</div>
                                    <?php else: ?>
                                    <!-- Show upload placeholder for new images -->
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--admin-primary); margin-bottom: 0.5rem;"></i>
                                    <div style="color: var(--admin-text-muted);">Click to upload or drag and drop</div>
                                    <div style="color: var(--admin-text-muted); font-size: 0.75rem; margin-top: 0.25rem;">PNG, JPG, GIF (Max. 5MB)</div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="image_<?php echo $i; ?>" id="image-input-<?php echo $i; ?>" 
                                       class="image-input" accept="image/*" style="display: none;">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="admin-card">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input form-select">
                                <option value="active" <?php echo ($variant['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($variant['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-2 justify-between">
                        <a href="color-variants.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $action === 'edit' ? 'Update Variant' : 'Save Variant'; ?>
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script src="assets/js/color-variants.js"></script>
</body>
</html>
