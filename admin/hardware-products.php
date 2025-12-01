<?php
session_start();
require_once 'includes/ProductManager.php';
require_once 'includes/HardwareCategoryManager.php';

$productManager = new ProductManager();
$categoryManager = new HardwareCategoryManager();

$action = $_GET['action'] ?? 'list';
$productId = $_GET['id'] ?? null;
$product = null;

if ($action === 'edit' && $productId) {
    $product = $productManager->getHardwareProduct($productId);
    if (!$product) {
        header('Location: hardware-products.php?error=Product not found');
        exit;
    }
}

$products = $productManager->getHardwareProducts();
$categories = $categoryManager->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hardware Products - LUSANO Admin</title>
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
                    <a href="categories.php" class="nav-item">
                        <i class="fas fa-tags"></i>
                        Digital Categories
                    </a>
                    <a href="hardware-products.php" class="nav-item active">
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
                    <h1><?php echo $action === 'add' ? 'Add New' : ($action === 'edit' ? 'Edit' : 'Manage'); ?> Hardware Products</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">
                        <?php echo $action === 'list' ? 'Manage your hardware product catalog' : 'Configure hardware product details and specifications'; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if ($action === 'list'): ?>
                    <a href="hardware-products.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </a>
                    <?php else: ?>
                    <a href="hardware-products.php" class="btn btn-secondary btn-sm">
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
                        <h3 class="card-title">Hardware Products (<?php echo count($products); ?>)</h3>
                        <div class="flex gap-2">
                            <input type="text" placeholder="Search products..." class="form-input" style="width: 250px;" id="search-input">
                            <!-- Removed status filter to show all products in admin panel -->
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table" id="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
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
                                            <img src="<?php echo htmlspecialchars($prod['images'][0]['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($prod['title']); ?>"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($prod['title']); ?></strong>
                                                <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                    <?php echo htmlspecialchars($prod['badge']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $productCategories = explode(',', $prod['categories'] ?? '');
                                        foreach ($productCategories as $catId):
                                            $category = null;
                                            foreach ($categories as $cat) {
                                                if ($cat['id'] === trim($catId)) {
                                                    $category = $cat;
                                                    break;
                                                }
                                            }
                                            if ($category):
                                        ?>
                                        <span style="background: var(--admin-accent); color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; margin-right: 0.25rem;">
                                            <i class="fas <?php echo $category['icon']; ?>"></i>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </span>
                                        <?php endif; endforeach; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600;">$<?php echo number_format($prod['price']); ?></span>
                                        <?php if ($prod['original_price'] > $prod['price']): ?>
                                        <div style="color: var(--admin-text-muted); font-size: 0.75rem; text-decoration: line-through;">
                                            $<?php echo number_format($prod['original_price']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="background: <?php echo $prod['status'] === 'active' ? 'var(--admin-success)' : 'var(--admin-secondary)'; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($prod['status']); ?>
                                        </span>
                                    </td>
                                    <td style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                        <?php echo date('M j, Y', strtotime($prod['created_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="hardware-products.php?action=edit&id=<?php echo $prod['id']; ?>" 
                                               class="btn btn-secondary btn-sm" data-tooltip="Edit Product">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteProduct('<?php echo $prod['id']; ?>', 'hardware')" 
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
                <!-- Hardware Product Form -->
                <form id="hardware-product-form" enctype="multipart/form-data" data-validate>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="type" value="hardware">
                    <?php if ($product): ?>
                    <input type="hidden" name="original_id" value="<?php echo $product['id']; ?>">
                    <?php endif; ?>
                    
                    <!-- Basic Information -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Product Information</h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Product ID *</label>
                                <input type="text" name="id" class="form-input" required 
                                       value="<?php echo $product['id'] ?? ''; ?>"
                                       placeholder="e.g., door-closer-hc200"
                                       <?php echo $product ? 'readonly' : ''; ?>>
                                <small style="color: var(--admin-text-muted);">Unique identifier for the product (cannot be changed after creation)</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Product Title *</label>
                                <input type="text" name="title" class="form-input" required 
                                       value="<?php echo $product['title'] ?? ''; ?>"
                                       placeholder="e.g., HC-200 Door Closer">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Price *</label>
                                <input type="number" name="price" class="form-input" required step="0.01" min="0"
                                       value="<?php echo $product['price'] ?? ''; ?>"
                                       placeholder="129.99">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Original Price</label>
                                <input type="number" name="original_price" class="form-input" step="0.01" min="0"
                                       value="<?php echo $product['original_price'] ?? ''; ?>"
                                       placeholder="159.99">
                                <small style="color: var(--admin-text-muted);">Leave empty if same as price</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Badge/Series *</label>
                                <input type="text" name="badge" class="form-input" required 
                                       value="<?php echo $product['badge'] ?? ''; ?>"
                                       placeholder="Professional Series">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-input form-select">
                                    <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Tagline *</label>
                            <textarea name="tagline" class="form-input form-textarea" required 
                                      placeholder="Brief description or tagline for the product"><?php echo $product['tagline'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Categories</h3>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Product Categories</label>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <?php foreach ($categories as $category): ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                                           <?php echo (isset($product['categories_array']) && in_array($category['id'], $product['categories_array'])) ? 'checked' : ''; ?>>
                                    <span class="checkbox-custom"></span>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas <?php echo $category['icon']; ?>" style="color: var(--admin-primary);"></i>
                                        <span><?php echo $category['name']; ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <small style="color: var(--admin-text-muted);">Select one or more categories that apply to this product</small>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Product Features</h3>
                        </div>
                        
                        <div id="features-container">
                            <?php if (isset($product['features_array']) && !empty($product['features_array'])): ?>
                                <?php foreach ($product['features_array'] as $index => $feature): ?>
                                <div class="feature-item" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; padding: 1rem; background: var(--admin-card-hover); border-radius: 0.5rem;">
                                    <input type="text" name="features[<?php echo $index; ?>][text]" class="form-input" 
                                           placeholder="Feature description" value="<?php echo htmlspecialchars($feature['text']); ?>" style="flex: 1;">
                                    <input type="text" name="features[<?php echo $index; ?>][icon]" class="form-input" 
                                           placeholder="fa-icon-name" value="<?php echo htmlspecialchars($feature['icon']); ?>" style="width: 150px;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" onclick="addFeature()">
                            <i class="fas fa-plus"></i>
                            Add Feature
                        </button>
                    </div>

                    <!-- Specifications -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h3 class="card-title">Technical Specifications</h3>
                        </div>
                        
                        <div id="specs-container">
                            <?php if (isset($product['specs_array']) && !empty($product['specs_array'])): ?>
                                <?php $index = 0; foreach ($product['specs_array'] as $key => $value): ?>
                                <div class="spec-item" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; padding: 1rem; background: var(--admin-card-hover); border-radius: 0.5rem;">
                                    <input type="text" name="specs_keys[]" class="form-input" 
                                           placeholder="Specification name" value="<?php echo htmlspecialchars($key); ?>" style="flex: 1;">
                                    <input type="text" name="specs_values[]" class="form-input" 
                                           placeholder="Specification value" value="<?php echo htmlspecialchars($value); ?>" style="flex: 1;">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSpec(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php $index++; endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" onclick="addSpec()">
                            <i class="fas fa-plus"></i>
                            Add Specification
                        </button>
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

                    <!-- Submit Buttons -->
                    <div class="flex gap-2 justify-between">
                        <a href="hardware-products.php" class="btn btn-secondary">
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
    <script>
        
        function deleteProduct(id, type) {
            if (confirm('Are you sure you want to delete this product?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                formData.append('type', type);
                
                fetch('api/products.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting product: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting product');
                });
            }
        }

        let featureIndex = <?php echo isset($product['features_array']) ? count($product['features_array']) : 0; ?>;
        let specIndex = <?php echo isset($product['specs_array']) ? count($product['specs_array']) : 0; ?>;

        function addFeature() {
            const container = document.getElementById('features-container');
            const featureDiv = document.createElement('div');
            featureDiv.className = 'feature-item';
            featureDiv.style.cssText = 'display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; padding: 1rem; background: var(--admin-card-hover); border-radius: 0.5rem;';
            featureDiv.innerHTML = `
                <input type="text" name="features[${featureIndex}][text]" class="form-input" 
                       placeholder="Feature description" style="flex: 1;">
                <input type="text" name="features[${featureIndex}][icon]" class="form-input" 
                       placeholder="fa-icon-name" style="width: 150px;">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(featureDiv);
            featureIndex++;
        }

        function removeFeature(button) {
            button.closest('.feature-item').remove();
        }

        function addSpec() {
            const container = document.getElementById('specs-container');
            const specDiv = document.createElement('div');
            specDiv.className = 'spec-item';
            specDiv.style.cssText = 'display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; padding: 1rem; background: var(--admin-card-hover); border-radius: 0.5rem;';
            specDiv.innerHTML = `
                <input type="text" name="specs_keys[]" class="form-input" 
                       placeholder="Specification name" style="flex: 1;">
                <input type="text" name="specs_values[]" class="form-input" 
                       placeholder="Specification value" style="flex: 1;">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSpec(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(specDiv);
            specIndex++;
        }

        function removeSpec(button) {
            button.closest('.spec-item').remove();
        }

        function previewProduct() {
            alert('Preview functionality would show how the product appears on the website');
        }

        // Form submission
        document.getElementById('hardware-product-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product saved successfully!');
                    window.location.href = 'hardware-products.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving product');
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
