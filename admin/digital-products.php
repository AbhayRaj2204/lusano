<?php
session_start();
require_once 'includes/ProductManager.php';
require_once 'includes/CategoryManager.php';

$productManager = new ProductManager();
$categoryManager = new CategoryManager();

$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;
$product = null;
$categories = $categoryManager->getCategories();

if ($action === 'edit' && $productId) {
    $product = $productManager->getDigitalProduct($productId);
    if (!$product) {
        header('Location: digital-products.php?error=Product not found');
        exit;
    }
}

$products = $productManager->getDigitalProducts(''); // Empty string to get all products
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Lock Products - LUSANO Admin</title>
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
                    <a href="digital-products.php" class="nav-item active">
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
                    <h1><?php echo $action === 'add' ? 'Add New' : ($action === 'edit' ? 'Edit' : 'Manage'); ?> Digital Lock Products</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">
                        <?php echo $action === 'list' ? 'Manage your digital lock product catalog' : 'Configure product details and specifications'; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if ($action === 'list'): ?>
                    <a href="digital-products.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </a>
                    <?php else: ?>
                    <a href="digital-products.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i>
                        Back to List
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'list'): ?>
                <!-- Product List -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Digital Lock Products (<?php echo count($products); ?>)</h3>
                        <div class="flex gap-2">
                            <input type="text" placeholder="Search products..." class="form-input" style="width: 250px;" id="search-input">
                            <select class="form-input form-select" style="width: 150px;" id="status-filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Categories</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $prod): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <?php if (!empty($prod['images'])): ?>
                                            <!-- Fixed image path display in admin table -->
                                            <img src="<?php echo htmlspecialchars($prod['images'][0]['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($prod['title'] ?? 'Untitled Product'); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem;"
                                                 onerror="this.src='../assets/image/placeholder.jpg'; this.onerror=null;">
                                            <?php else: ?>
                                            <!-- Added placeholder when no image -->
                                            <div style="width: 50px; height: 50px; background: var(--admin-border); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: var(--admin-text-muted);"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <!-- Added null coalescing operators to prevent undefined array key warnings -->
                                                <strong><?php echo htmlspecialchars($prod['title'] ?? 'Untitled Product'); ?></strong>
                                                <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                    <?php echo htmlspecialchars($prod['badge'] ?? 'No Badge'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Added null coalescing and proper number formatting with fallbacks -->
                                        <span style="font-weight: 600;">$<?php echo number_format($prod['price'] ?? 0); ?></span>
                                        <?php if (($prod['original_price'] ?? 0) > ($prod['price'] ?? 0)): ?>
                                        <div style="color: var(--admin-text-muted); font-size: 0.75rem; text-decoration: line-through;">
                                            $<?php echo number_format($prod['original_price'] ?? 0); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $cats = explode(',', $prod['categories'] ?? 'uncategorized');
                                        foreach ($cats as $cat): 
                                        ?>
                                        <span style="background: var(--admin-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; margin-right: 0.25rem;">
                                            <?php echo trim($cat); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <!-- Added null coalescing for status with default value -->
                                        <span style="background: <?php echo ($prod['status'] ?? 'inactive') === 'active' ? 'var(--admin-success)' : 'var(--admin-secondary)'; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($prod['status'] ?? 'inactive'); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                        <!-- Added safe date handling with fallback -->
                                        <?php echo date('M j, Y', strtotime($prod['created_date'] ?? 'now')); ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="digital-products.php?action=edit&id=<?php echo $prod['id'] ?? ''; ?>" 
                                               class="btn btn-secondary btn-sm" data-tooltip="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- <button onclick="duplicateProduct('<?php echo $prod['id'] ?? ''; ?>')" 
                                                    class="btn btn-outline btn-sm" data-tooltip="Duplicate Product">
                                                <i class="fas fa-copy"></i>
                                            </button> -->
                                            <button onclick="deleteProduct('<?php echo $prod['id'] ?? ''; ?>', 'digital')" 
                                                    class="btn btn-danger btn-sm" data-tooltip="Delete Product">
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
                <form id="product-form" data-validate enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($product): ?>
                    <input type="hidden" name="original_id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Basic Information -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Product ID *</label>
                                <input type="text" name="id" class="form-input" required 
                                       value="<?php echo $product['id'] ?? ''; ?>"
                                       placeholder="e.g., x1, s3-slim, k2">
                                <small style="color: var(--admin-text-muted);">Unique identifier for the product</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Product Title *</label>
                                <input type="text" name="title" class="form-input" required 
                                       value="<?php echo $product['title'] ?? ''; ?>"
                                       placeholder="e.g., LUSANO X1">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Price *</label>
                                <input type="number" name="price" class="form-input" required step="0.01"
                                       value="<?php echo $product['price'] ?? ''; ?>"
                                       placeholder="329">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Original Price</label>
                                <input type="number" name="original_price" class="form-input" step="0.01"
                                       value="<?php echo $product['original_price'] ?? ''; ?>"
                                       placeholder="399">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Icon Class *</label>
                                <input type="text" name="icon" class="form-input" required 
                                       value="<?php echo $product['icon'] ?? ''; ?>"
                                       placeholder="fa-fingerprint">
                                <small style="color: var(--admin-text-muted);">FontAwesome icon class</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Badge *</label>
                                <input type="text" name="badge" class="form-input" required 
                                       value="<?php echo $product['badge'] ?? ''; ?>"
                                       placeholder="Premium Series">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tagline *</label>
                            <textarea name="tagline" class="form-input form-textarea" required 
                                      placeholder="Brief description of the product"><?php echo $product['tagline'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Categories *</label>
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                                <?php 
                                $selectedCategories = $product ? explode(',', $product['categories']) : [];
                                foreach ($categories as $category): 
                                ?>
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                                           <?php echo in_array($category['id'], $selectedCategories) ? 'checked' : ''; ?>>
                                    <i class="fas <?php echo $category['icon']; ?>"></i>
                                    <?php echo $category['name']; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>



                    <!-- Features -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Product Features</h3>
                            <button type="button" class="btn btn-outline btn-sm" onclick="addFeature()">
                                <i class="fas fa-plus"></i>
                                Add Feature
                            </button>
                        </div>
                        
                        <div id="features-container">
                            <?php 
                            $features = $product ? $product['features_array'] : [''];
                            foreach ($features as $index => $feature): 
                            ?>
                            <div class="form-field-group" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                <input type="text" name="features[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($feature); ?>"
                                       placeholder="Feature description">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Detailed Features -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Detailed Features</h3>
                            <button type="button" class="btn btn-outline btn-sm" onclick="addDetailedFeature()">
                                <i class="fas fa-plus"></i>
                                Add Detailed Feature
                            </button>
                        </div>
                        
                        <div id="detailed-features-container">
                            <?php 
                            $detailedFeatures = $product ? $product['detailed_features_array'] : [['title' => '', 'description' => '', 'icon' => '']];
                            foreach ($detailedFeatures as $index => $feature): 
                            ?>
                            <div class="form-field-group" style="border: 1px solid var(--admin-border); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                                    <input type="text" name="detailed_features[<?php echo $index; ?>][title]" class="form-input" 
                                           value="<?php echo htmlspecialchars($feature['title']); ?>"
                                           placeholder="Feature title">
                                    <input type="text" name="detailed_features[<?php echo $index; ?>][icon]" class="form-input" 
                                           value="<?php echo htmlspecialchars($feature['icon']); ?>"
                                           placeholder="fa-icon-name">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <textarea name="detailed_features[<?php echo $index; ?>][description]" class="form-input form-textarea" 
                                          placeholder="Detailed description"><?php echo htmlspecialchars($feature['description']); ?></textarea>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Technical Specifications</h3>
                            <button type="button" class="btn btn-outline btn-sm" onclick="addSpecification()">
                                <i class="fas fa-plus"></i>
                                Add Specification
                            </button>
                        </div>
                        
                        <div id="specs-container">
                            <?php 
                            $specs = $product ? $product['specs_array'] : [];
                            if (empty($specs)) {
                                // Always show at least one empty row for new product or if no specs exist
                                $specs = ['' => ''];
                            }
                            foreach ($specs as $key => $value): 
                            ?>
                            <div class="form-field-group" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                                <input type="text" name="specs_keys[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($key); ?>"
                                       placeholder="Specification name">
                                <input type="text" name="specs_values[]" class="form-input" 
                                       value="<?php echo htmlspecialchars($value); ?>"
                                       placeholder="Specification value">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Installation Guide Video -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Installation Guide Video</h3>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">YouTube Video URL</label>
                            <input type="url" name="installation_guide_video" class="form-input"
                                   value="<?php echo $product['installation_guide_video'] ?? ''; ?>"
                                   placeholder="https://www.youtube.com/watch?v=...">
                            <small style="color: var(--admin-text-muted);">Paste the full YouTube URL for the installation guide video. Leave empty if no video available.</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Product Status</h3>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input form-select">
                                <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-2 justify-between">
                        <a href="digital-products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <div class="flex gap-2">
                            <!-- <button type="button" class="btn btn-outline" onclick="previewProduct()">
                                <i class="fas fa-eye"></i>
                                Preview
                            </button> -->
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $action === 'edit' ? 'Update Product' : 'Create Product'; ?>
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script src="assets/js/digital-products.js"></script>
    <script>
        function removeExistingImage(button, imagePath) {
            if (confirm('Are you sure you want to remove this image?')) {
                button.parentElement.remove();
                console.log('[v0] Removed existing image:', imagePath);
            }
        }

        function addSpecification() {
            const container = document.getElementById('specs-container');
            const div = document.createElement('div');
            div.className = 'form-field-group';
            div.style = "display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;";
            div.innerHTML = `
                <input type="text" name="specs_keys[]" class="form-input" placeholder="Specification name">
                <input type="text" name="specs_values[]" class="form-input" placeholder="Specification value">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }

        // Prevent empty specification fields from being submitted
        document.getElementById('product-form')?.addEventListener('submit', function(e) {
            // Remove empty spec rows before submit
            const specsKeys = this.querySelectorAll('input[name="specs_keys[]"]');
            const specsValues = this.querySelectorAll('input[name="specs_values[]"]');
            for (let i = specsKeys.length - 1; i >= 0; i--) {
                if (!specsKeys[i].value.trim() && !specsValues[i].value.trim()) {
                    specsKeys[i].parentElement.remove();
                }
            }
        });

        function removeFormField(button) {
            button.parentElement.remove();
        }
    // Add Feature dynamically
    function addFeature() {
        const container = document.getElementById('features-container');
        const div = document.createElement('div');
        div.className = 'form-field-group';
        div.style = "display: flex; gap: 1rem; margin-bottom: 1rem;";
        div.innerHTML = `
            <input type="text" name="features[]" class="form-input" placeholder="Feature description">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    }

    // Add Detailed Feature dynamically
    function addDetailedFeature() {
        const container = document.getElementById('detailed-features-container');
        const index = container.querySelectorAll('.form-field-group').length;
        const div = document.createElement('div');
        div.className = 'form-field-group';
        div.style = "border: 1px solid var(--admin-border); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;";
        div.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="detailed_features[${index}][title]" class="form-input" placeholder="Feature title">
                <input type="text" name="detailed_features[${index}][icon]" class="form-input" placeholder="fa-icon-name">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFormField(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <textarea name="detailed_features[${index}][description]" class="form-input form-textarea" placeholder="Detailed description"></textarea>
        `;
        container.appendChild(div);
    }
    </script>
</body>
</html>
