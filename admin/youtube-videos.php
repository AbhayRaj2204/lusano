<?php
session_start();
require_once 'includes/YouTubeManager.php';

$youtubeManager = new YouTubeManager();

// Create table if it doesn't exist
$youtubeManager->createTable();

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $data = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'youtube_url' => $_POST['youtube_url'],
                    'status' => $_POST['status'],
                    'display_order' => (int)$_POST['display_order']
                ];
                
                if ($youtubeManager->addVideo($data)) {
                    $message = 'YouTube video added successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error adding video. Please check the YouTube URL.';
                    $messageType = 'error';
                }
                break;
                
            case 'edit':
                $data = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'youtube_url' => $_POST['youtube_url'],
                    'status' => $_POST['status'],
                    'display_order' => (int)$_POST['display_order']
                ];
                
                if ($youtubeManager->updateVideo($_POST['id'], $data)) {
                    $message = 'YouTube video updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating video. Please check the YouTube URL.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get video for editing
$editVideo = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editVideo = $youtubeManager->getVideo($_GET['id']);
}

// Get all videos
$videos = $youtubeManager->getAllVideos();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Videos - LUSANO Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
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
                    <a href="carousel-banners.php" class="nav-item">
                        <i class="fas fa-images"></i>
                        Carousel Banners
                    </a>
                    <a href="youtube-videos.php" class="nav-item active">
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

         Main Content 
        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1><?php echo $editVideo ? 'Edit YouTube Video' : 'YouTube Videos'; ?></h1>
                    <p style="color: var(--admin-text-muted); margin: 0;">Manage YouTube videos for the homepage section</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn btn-outline btn-sm" id="mobile-menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <?php if (!$editVideo): ?>
                    <button class="btn btn-primary btn-sm" onclick="showAddForm()">
                        <i class="fab fa-youtube"></i>
                        Add Video
                    </button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                 Add/Edit Form 
                <div class="admin-card" id="video-form" style="<?php echo !$editVideo ? 'display: none;' : ''; ?>">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $editVideo ? 'Edit Video' : 'Add New Video'; ?></h3>
                        <?php if (!$editVideo): ?>
                        <button class="btn btn-outline btn-sm" onclick="hideAddForm()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="action" value="<?php echo $editVideo ? 'edit' : 'add'; ?>">
                        <?php if ($editVideo): ?>
                        <input type="hidden" name="id" value="<?php echo $editVideo['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="form-label">Video Title</label>
                            <input type="text" name="title" class="form-input" required 
                                   value="<?php echo $editVideo ? htmlspecialchars($editVideo['title']) : ''; ?>"
                                   placeholder="Enter video title">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="3"
                                      placeholder="Enter video description"><?php echo $editVideo ? htmlspecialchars($editVideo['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-input" required 
                                   value="<?php echo $editVideo ? htmlspecialchars($editVideo['youtube_url']) : ''; ?>"
                                   placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
                            <small class="form-help">Paste the full YouTube video URL</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-input" min="1" 
                                   value="<?php echo $editVideo ? $editVideo['display_order'] : '1'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editVideo && $editVideo['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editVideo && $editVideo['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fab fa-youtube"></i>
                                <?php echo $editVideo ? 'Update Video' : 'Add Video'; ?>
                            </button>
                            <?php if ($editVideo): ?>
                            <a href="youtube-videos.php" class="btn btn-outline">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                 Videos List 
                <div class="admin-card">
                    <div class="card-header">
                        <h3 class="card-title">All YouTube Videos</h3>
                        <button class="btn btn-outline btn-sm" onclick="toggleReorder()">
                            <i class="fas fa-sort"></i>
                            Reorder
                        </button>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="videos-list">
                                <?php foreach ($videos as $video): ?>
                                <tr data-id="<?php echo $video['id']; ?>">
                                    <td>
                                        <div class="video-preview">
                                            <img src="<?php echo $youtubeManager->getThumbnailUrl($video['video_id']); ?>" 
                                                 alt="<?php echo htmlspecialchars($video['title']); ?>"
                                                 style="width: 80px; height: 45px; object-fit: cover; border-radius: 4px;">
                                            <div class="play-overlay">
                                                <i class="fab fa-youtube"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($video['title']); ?></strong>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($video['description']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $video['display_order']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $video['status']; ?>">
                                            <?php echo ucfirst($video['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <a href="?action=edit&id=<?php echo $video['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="deleteVideo(<?php echo $video['id']; ?>)" class="btn btn-danger btn-sm">
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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        function showAddForm() {
            document.getElementById('video-form').style.display = 'block';
        }
        
        function hideAddForm() {
            document.getElementById('video-form').style.display = 'none';
        }
        
        function deleteVideo(id) {
            if (confirm('Are you sure you want to delete this video?')) {
                fetch('api/youtube-videos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting video: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting video');
                });
            }
        }
        
        let sortable;
        
        function toggleReorder() {
            const tbody = document.getElementById('videos-list');
            
            if (sortable) {
                sortable.destroy();
                sortable = null;
                document.querySelector('[onclick="toggleReorder()"]').innerHTML = '<i class="fas fa-sort"></i> Reorder';
            } else {
                sortable = Sortable.create(tbody, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: function(evt) {
                        const videoIds = Array.from(tbody.children).map(row => row.dataset.id);
                        
                        fetch('api/youtube-videos.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'reorder',
                                video_ids: videoIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                alert('Error updating order');
                                location.reload();
                            }
                        });
                    }
                });
                document.querySelector('[onclick="toggleReorder()"]').innerHTML = '<i class="fas fa-check"></i> Done';
            }
        }
    </script>
    
    <style>
        .video-preview {
            position: relative;
            display: inline-block;
        }
        
        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 1.2rem;
            text-shadow: 0 0 4px rgba(0,0,0,0.8);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background: var(--admin-success);
            color: white;
        }
        
        .status-inactive {
            background: var(--admin-text-muted);
            color: white;
        }
        
        .sortable-ghost {
            opacity: 0.4;
        }
    </style>
</body>
</html>
