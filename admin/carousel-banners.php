<?php
session_start();
require_once 'includes/CarouselManager.php';

$carouselManager = new CarouselManager();
$action = $_GET['action'] ?? 'list';
$bannerId = $_GET['id'] ?? null;

$banner = null;
if ($action === 'edit' && $bannerId) {
    $banner = $carouselManager->getBannerById($bannerId);
}

$banners = $carouselManager->getCarouselBanners();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carousel Banners - LUSANO Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Simplified styling to match admin design system */
        .banner-preview {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 1px solid var(--admin-border);
            box-shadow: 0 2px 4px var(--admin-shadow);
        }
        .banner-video-preview {
            width: 100px;
            height: 60px;
            border-radius: 0.5rem;
            border: 1px solid var(--admin-border);
            box-shadow: 0 2px 4px var(--admin-shadow);
        }
        .sortable-list {
            list-style: none;
            padding: 0;
        }
        .sortable-item {
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            cursor: move;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }
        .sortable-item:hover {
            background: var(--admin-card-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px var(--admin-shadow);
        }
        .drag-handle {
            color: var(--admin-text-muted);
            cursor: grab;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        
        /* Current media preview styling to match admin cards */
        .current-media-preview {
            margin-top: 0.75rem;
            padding: 1rem;
            background: var(--admin-card);
            border: 1px solid var(--admin-border);
            border-radius: 0.5rem;
        }
        
        .current-media-preview small {
            color: var(--admin-primary);
            font-weight: 500;
            margin: 0 0 0.5rem 0;
            display: block;
        }
        
        .current-media-preview img,
        .current-media-preview video {
            border-radius: 0.25rem;
            box-shadow: 0 2px 4px var(--admin-shadow);
        }
        
        /* Badge styling to match admin system */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: var(--admin-success);
            color: white;
        }
        
        .badge-warning {
            background: var(--admin-warning);
            color: white;
        }
        
        .badge-info {
            background: var(--admin-accent);
            color: white;
        }
        
        .badge-secondary {
            background: var(--admin-secondary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
         Sidebar 
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
                    <a href="carousel-banners.php" class="nav-item active">
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
                    <a href="../index.html" class="nav-item" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        View Website
                    </a>
                </div>
            </div>
        </nav>

         Main Content 
        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1><?php echo $action === 'add' ? 'Add New Banner' : ($action === 'edit' ? 'Edit Banner' : 'Carousel Banners'); ?></h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">Manage homepage carousel banners and videos</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if ($action === 'list'): ?>
                    <a href="carousel-banners.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i>
                        Add Banner
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="admin-card">
                    <!-- Updated form header to match admin card pattern -->
                    <div class="card-header">
                        <h3 class="card-title">Add/Edit Form</h3>
                    </div>
                    
                    <form id="bannerForm" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'add'; ?>">
                        <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $bannerId; ?>">
                        <?php endif; ?>
                        
                        <!-- Using standard admin form groups instead of custom grid -->
                        <div class="form-group">
                            <label class="form-label" for="title">Banner Title</label>
                            <input type="text" id="title" name="title" class="form-input" required 
                                   value="<?php echo $banner ? htmlspecialchars($banner['title']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="type">Media Type</label>
                            <select id="type" name="type" class="form-input form-select" required onchange="toggleMediaInput()">
                                <option value="image" <?php echo ($banner && $banner['type'] === 'image') ? 'selected' : ''; ?>>Image</option>
                                <option value="video" <?php echo ($banner && $banner['type'] === 'video') ? 'selected' : ''; ?>>Video</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="media_file">Upload Media File</label>
                            <input type="file" id="media_file" name="media_file" class="form-input"
                                   accept="image/*,video/*" 
                                   <?php echo $action === 'add' ? 'required' : ''; ?>>
                            <small style="color: var(--admin-text-muted); font-size: 0.875rem; margin-top: 0.5rem; display: block;">
                                Upload an image (JPG, PNG, GIF) or video (MP4, WebM) file
                            </small>
                            <?php if ($banner && $banner['media_url']): ?>
                            <div class="current-media-preview">
                                <small>Current file: <?php echo basename($banner['media_url']); ?></small>
                                <?php if ($banner['type'] === 'image'): ?>
                                    <img src="../<?php echo htmlspecialchars($banner['media_url']); ?>" 
                                         alt="Current banner" style="max-width: 200px; max-height: 100px;"
                                         onerror="this.style.display='none';">
                                <?php else: ?>
                                    <video style="max-width: 200px; max-height: 100px;" controls>
                                        <source src="../<?php echo htmlspecialchars($banner['media_url']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="order">Display Order</label>
                            <input type="number" id="order" name="order" class="form-input" min="1" required 
                                   value="<?php echo $banner ? $banner['order'] : (count($banners) + 1); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-input form-select" required>
                                <option value="active" <?php echo ($banner && $banner['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($banner && $banner['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Updated form actions to use flex gap utility -->
                        <div class="flex gap-2" style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--admin-border);">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $action === 'edit' ? 'Update Banner' : 'Add Banner'; ?>
                            </button>
                            <a href="carousel-banners.php" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <?php else: ?>
                <!-- List View -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">All Carousel Banners</h3>
                        <div class="flex gap-2">
                            <button onclick="toggleReorderMode()" class="btn btn-outline btn-sm" id="reorderBtn">
                                <i class="fas fa-sort"></i>
                                Reorder
                            </button>
                        </div>
                    </div>
                    
                    <div id="normalView">
                        <div style="overflow-x: auto;">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td>
                                            <?php if ($banner['type'] === 'video'): ?>
                                                <video class="banner-video-preview" muted>
                                                    <source src="../<?php echo htmlspecialchars($banner['media_url']); ?>" type="video/mp4">
                                                </video>
                                            <?php else: ?>
                                                <img src="../<?php echo htmlspecialchars($banner['media_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($banner['title']); ?>" 
                                                     class="banner-preview"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjYwIiB2aWV3Qm94PSIwIDAgMTAwIDYwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjZjNmNGY2Ii8+CjxwYXRoIGQ9Ik0zNSAyMEw0NSAzMEwzNSA0MEgyNVYyMEgzNVoiIGZpbGw9IiM5Y2EzYWYiLz4KPHN2Zz4K'; this.onerror=null;">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($banner['title']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $banner['type'] === 'video' ? 'badge-info' : 'badge-secondary'; ?>">
                                                <?php echo ucfirst($banner['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $banner['order']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $banner['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo ucfirst($banner['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <a href="carousel-banners.php?action=edit&id=<?php echo $banner['id']; ?>" 
                                                   class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteBanner('<?php echo $banner['id']; ?>')" 
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
                    
                    <div id="reorderView" style="display: none;">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Drag to reorder banners</h4>
                            <div class="flex gap-2">
                                <button onclick="saveOrder()" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save"></i>
                                    Save Order
                                </button>
                                <button onclick="cancelReorder()" class="btn btn-outline btn-sm">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </button>
                            </div>
                        </div>
                        
                        <ul class="sortable-list" id="sortableBanners">
                            <?php foreach ($banners as $banner): ?>
                            <li class="sortable-item" data-id="<?php echo $banner['id']; ?>">
                                <i class="fas fa-grip-vertical drag-handle"></i>
                                <div>
                                    <?php if ($banner['type'] === 'video'): ?>
                                        <video class="banner-video-preview" muted>
                                            <source src="../<?php echo htmlspecialchars($banner['media_url']); ?>" type="video/mp4">
                                        </video>
                                    <?php else: ?>
                                        <img src="../<?php echo htmlspecialchars($banner['media_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($banner['title']); ?>" 
                                             class="banner-preview"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjYwIiB2aWV3Qm94PSIwIDAgMTAwIDYwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjZjNmNGY2Ii8+CjxwYXRoIGQ9Ik0zNSAyMEw0NSAzMEwzNSA0MEgyNVYyMEgzNVoiIGZpbGw9IiM5Y2EzYWYiLz4KPHN2Zz4K'; this.onerror=null;">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <strong><?php echo htmlspecialchars($banner['title']); ?></strong>
                                    <div style="color: var(--admin-text-muted); font-size: 0.875rem;">
                                        <?php echo ucfirst($banner['type']); ?> • <?php echo ucfirst($banner['status']); ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        let sortable;
        
        function toggleMediaInput() {
            const type = document.getElementById('type').value;
            const mediaFile = document.getElementById('media_file');
            
            if (type === 'video') {
                mediaFile.accept = 'video/*';
            } else {
                mediaFile.accept = 'image/*';
            }
        }
        
        function toggleReorderMode() {
            const normalView = document.getElementById('normalView');
            const reorderView = document.getElementById('reorderView');
            const reorderBtn = document.getElementById('reorderBtn');
            
            if (normalView.style.display === 'none') {
                // Switch back to normal view
                normalView.style.display = 'block';
                reorderView.style.display = 'none';
                reorderBtn.innerHTML = '<i class="fas fa-sort"></i> Reorder';
                if (sortable) {
                    sortable.destroy();
                }
            } else {
                // Switch to reorder view
                normalView.style.display = 'none';
                reorderView.style.display = 'block';
                reorderBtn.innerHTML = '<i class="fas fa-times"></i> Cancel';
                
                // Initialize sortable
                const sortableList = document.getElementById('sortableBanners');
                sortable = Sortable.create(sortableList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            }
        }
        
        function cancelReorder() {
            toggleReorderMode();
        }
        
        function saveOrder() {
            const sortableList = document.getElementById('sortableBanners');
            const bannerIds = Array.from(sortableList.children).map(item => item.dataset.id);
            
            const formData = new FormData();
            formData.append('action', 'reorder');
            formData.append('banner_ids', JSON.stringify(bannerIds));
            
            fetch('api/carousel-banners.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Banner order updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating order: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating order');
            });
        }
        
        function deleteBanner(id) {
            if (confirm('Are you sure you want to delete this banner?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                fetch('api/carousel-banners.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting banner: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting banner');
                });
            }
        }
        
        // Handle form submission
        document.getElementById('bannerForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/carousel-banners.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'carousel-banners.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving banner');
            });
        });
    </script>
</body>
</html>
