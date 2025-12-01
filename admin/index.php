<?php
session_start();

// Check if user is authenticated via JavaScript (client-side check)
// For production, implement proper server-side session management

$totalProducts = 0;
$totalDigitalProducts = 0;
$totalHardwareProducts = 0;
$totalCategories = 0;
$digitalProducts = [];
$hardwareProducts = [];

// Load digital products
$digitalProductsFile = 'data/digital_products.csv';
if (file_exists($digitalProductsFile)) {
    $digitalProducts = array_map('str_getcsv', file($digitalProductsFile));
    if (!empty($digitalProducts)) {
        array_shift($digitalProducts); // Remove header
        $totalDigitalProducts = count($digitalProducts);
    }
}

// Load hardware products
$hardwareProductsFile = 'data/hardware_products.csv';
if (file_exists($hardwareProductsFile)) {
    $hardwareProducts = array_map('str_getcsv', file($hardwareProductsFile));
    if (!empty($hardwareProducts)) {
        array_shift($hardwareProducts); // Remove header
        $totalHardwareProducts = count($hardwareProducts);
    }
}

// Calculate total products
$totalProducts = $totalDigitalProducts + $totalHardwareProducts;

// Load categories
$categoriesFile = 'data/categories.csv';
if (file_exists($categoriesFile)) {
    $categories = array_map('str_getcsv', file($categoriesFile));
    if (!empty($categories)) {
        array_shift($categories); // Remove header
        $totalCategories = count($categories);
    }
}

// Convert CSV rows to associative arrays for easier access
$digitalProducts = array_map(function($row) {
    return [
        'id' => $row[0] ?? '',
        'title' => $row[1] ?? '',
        'price' => floatval($row[2] ?? 0),
        'original_price' => floatval($row[3] ?? 0),
        'icon' => $row[4] ?? '',
        'badge' => $row[5] ?? '',
        'tagline' => $row[6] ?? '',
        'features' => $row[7] ?? '',
        'detailed_features' => $row[8] ?? '',
        'specs' => $row[9] ?? '',
        'categories' => $row[10] ?? '',
        'status' => $row[11] ?? 'active'
    ];
}, $digitalProducts);

$hardwareProducts = array_map(function($row) {
    return [
        'id' => $row[0] ?? '',
        'title' => $row[1] ?? '',
        'price' => floatval($row[2] ?? 0),
        'original_price' => floatval($row[3] ?? 0),
        'badge' => $row[4] ?? '',
        'tagline' => $row[5] ?? '',
        'features' => $row[6] ?? '',
        'specs' => $row[7] ?? '',
        'status' => $row[8] ?? 'active',
        'categories' => $row[11] ?? ''
    ];
}, $hardwareProducts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUSANO Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Added auth.js script before other scripts -->
    <script src="assets/js/auth.js"></script>

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
                    <a href="index.php" class="nav-item active">
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
                    <!-- Added Color Variants menu item -->
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
                    <h1>Dashboard</h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">Welcome to LUSANO Product Management System</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <a href="digital-products.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Product
                    </a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalProducts; ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalDigitalProducts; ?></div>
                        <div class="stat-label">Digital Locks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalHardwareProducts; ?></div>
                        <div class="stat-label">Hardware Products</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $totalCategories; ?></div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Digital Lock Products</h3>
                        <a href="digital-products.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Categories</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recentProducts = !empty($digitalProducts) ? array_slice($digitalProducts, 0, 5) : [];
                                foreach ($recentProducts as $product): 
                                ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                            <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($product['badge']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600;">$<?php echo number_format($product['price']); ?></span>
                                        <?php if ($product['original_price'] > $product['price']): ?>
                                        <div style="color: var(--admin-text-muted); font-size: 0.75rem; text-decoration: line-through;">
                                            $<?php echo number_format($product['original_price']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $cats = explode(',', $product['categories']);
                                        foreach ($cats as $cat): 
                                        ?>
                                        <span style="background: var(--admin-primary); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; margin-right: 0.25rem;">
                                            <?php echo trim($cat); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <span style="background: var(--admin-success); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="digital-products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteProduct('<?php echo $product['id']; ?>', 'digital')" class="btn btn-danger btn-sm">
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

                <!-- Recent Hardware Products -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Hardware Products</h3>
                        <a href="hardware-products.php" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recentHardware = !empty($hardwareProducts) ? array_slice($hardwareProducts, 0, 5) : [];
                                foreach ($recentHardware as $product): 
                                ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                            <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                                <?php echo htmlspecialchars($product['badge']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600;">$<?php echo number_format($product['price']); ?></span>
                                        <?php if ($product['original_price'] > $product['price']): ?>
                                        <div style="color: var(--admin-text-muted); font-size: 0.75rem; text-decoration: line-through;">
                                            $<?php echo number_format($product['original_price']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="background: var(--admin-success); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="hardware-products.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteProduct('<?php echo $product['id']; ?>', 'hardware')" class="btn btn-danger btn-sm">
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
    </script>
</body>
</html>
