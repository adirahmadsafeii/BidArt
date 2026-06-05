<?php
session_start();
require_once '../../config/db.php';

// Hanya untuk artist yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header('Location: ../../views/auth/login.php');
    exit();
}

$artist_id = $_SESSION['user_id'];

// Handle hapus gambar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    $artwork_id = (int)$_POST['artwork_id'];

    // Ambil data artwork
    $query = "SELECT * FROM artworks WHERE id = $artwork_id AND artist_id = $artist_id";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $image_path = $row['image_path'];

        // Hapus file fisik
        if ($image_path && file_exists("../../" . $image_path)) {
            unlink("../../" . $image_path);
        }

        // Update image_path jadi NULL
        $update = "UPDATE artworks SET image_path = NULL WHERE id = $artwork_id AND artist_id = $artist_id";
        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = "Image deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete image.";
        }
    } else {
        $_SESSION['error'] = "Artwork not found or you don't have permission.";
    }

    header('Location: manage_images.php');
    exit();
}

// Ambil semua artwork milik artist
$query = "SELECT ar.*, a.status as auction_status, a.current_price, a.id as auction_id
          FROM artworks ar 
          LEFT JOIN auctions a ON ar.id = a.artwork_id 
          WHERE ar.artist_id = $artist_id 
          ORDER BY ar.created_at DESC";

$artworks = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Images - BidArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">Auctions</a>
                <a class="nav-link" href="../../gallery.php">Gallery</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link " href="dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link active" href="manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="../../middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="../login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Your Artwork Images</h2>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <?php if ($artworks && mysqli_num_rows($artworks) > 0): ?>
                <?php while ($artwork = mysqli_fetch_assoc($artworks)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card">
                            <?php if ($artwork['image_path'] && file_exists("../../" . $artwork['image_path'])): ?>
                                <img src="../../<?php echo htmlspecialchars($artwork['image_path']); ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($artwork['title']); ?>"
                                    style="height: 250px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p>No Image</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($artwork['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($artwork['description'], 0, 100)) . '...'; ?></p>

                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Status</small>
                                        <div class="fw-bold <?php echo $artwork['auction_status'] === 'active' ? 'text-success' : 'text-secondary'; ?>">
                                            <?php echo $artwork['auction_status'] ? ucfirst($artwork['auction_status']) : 'No Auction'; ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Current Price</small>
                                        <div class="fw-bold text-success">
                                            Rp. <?php echo $artwork['current_price'] ? number_format($artwork['current_price'], 2, ',', '.') : '0.00'; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <?php if ($artwork['image_path'] && file_exists("../../" . $artwork['image_path'])): ?>
                                        <button class="btn  btn-sm" onclick="viewImage('../../<?php echo htmlspecialchars($artwork['image_path']); ?>', '<?php echo htmlspecialchars($artwork['title']); ?>')">
                                            View Full Size
                                        </button>
                                        <button class="btn  btn-sm" onclick="replaceImage(<?php echo $artwork['id']; ?>)">
                                            Replace Image
                                        </button>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                            <input type="hidden" name="artwork_id" value="<?php echo $artwork['id']; ?>">
                                            <button type="submit" name="delete_image" class="btn btn-sm w-100 mt-2">
                                                Delete Image
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm" onclick="uploadImage(<?php echo $artwork['id']; ?>)">
                                            Upload Image
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($artwork['auction_id']): ?>
                                        <a href="../../auction_details.php?id=<?php echo $artwork['auction_id']; ?>" class="btn  btn-sm">
                                            View Auction
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No Artworks Yet</h4>
                        <p>You haven't created any artworks yet.</p>
                        <a href="dashboard.php" class="btn ">Create Your First Artwork</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageViewTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imageViewImg" src="/placeholder.svg" alt="" class="img-fluid" style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Upload/Replace Image Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalTitle">Upload Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" method="POST" action="edit_image.php" enctype="multipart/form-data">
                        <input type="hidden" id="artwork_id" name="artwork_id">
                        <div class="mb-3">
                            <label for="new_image" class="form-label">Select New Image</label>
                            <input type="file" class="form-control" id="new_image" name="new_image" accept="image/*" required>
                            <div class="form-text">JPG, PNG, GIF, WebP - Max 5MB</div>
                        </div>

                        <div id="newImagePreview" class="mb-3" style="display: none;">
                            <img id="newPreviewImg" src="/placeholder.svg" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 200px;">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn ">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewImage(imageSrc, title) {
            document.getElementById('imageViewImg').src = imageSrc;
            document.getElementById('imageViewTitle').textContent = title;
            new bootstrap.Modal(document.getElementById('imageViewModal')).show();
        }

        function uploadImage(artworkId) {
            document.getElementById('artwork_id').value = artworkId;
            document.getElementById('uploadModalTitle').textContent = 'Upload Image';
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        }

        function replaceImage(artworkId) {
            document.getElementById('artwork_id').value = artworkId;
            document.getElementById('uploadModalTitle').textContent = 'Replace Image';
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        }

        // Image preview for upload
        document.getElementById('new_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('newPreviewImg').src = e.target.result;
                    document.getElementById('newImagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>