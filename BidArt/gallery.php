<?php
session_start();
require_once 'middlewares/auth_check.php';
require_once 'config/db.php';


// Get all artworks with images
$query = "SELECT a.*, ar.title, ar.description, ar.image_path, u.full_name as artist_name,
          TIMESTAMPDIFF(SECOND, NOW(), a.end_time) as time_remaining,
          (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.id) as total_bids
          FROM auctions a 
          JOIN artworks ar ON a.artwork_id = ar.id 
          JOIN users u ON ar.artist_id = u.id 
          WHERE ar.image_path IS NOT NULL AND ar.image_path != ''
          ORDER BY a.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery - BidArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-10px);
        }

        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 20px;
        }


        .masonry-grid {
            column-count: 3;
            column-gap: 20px;
        }

        .masonry-item {
            break-inside: avoid;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .masonry-grid {
                column-count: 1;
            }
        }

        @media (max-width: 992px) {
            .masonry-grid {
                column-count: 2;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Auctions</a>
                <a class="nav-link active" href="gallery.php">Gallery</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="views/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link" href="views/artist/manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="views/auth/login.php">Login</a>
                    <a class="nav-link" href="views/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section text-white py-5">
        <div class="container text-center">
            <h1 class="display-4">Art Gallery</h1>
            <p class="lead">Discover amazing digital artworks from talented artists</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-md-6">
                <h3>Featured Artworks</h3>
                <p class="text-muted">Browse through our collection of digital art masterpieces</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-light" onclick="switchView('grid')">Grid View</button>
                    <button type="button" class="btn btn-outline-light active" onclick="switchView('masonry')">Masonry View</button>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div id="gridView" class="row" style="display: none;">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($artwork = $result->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="gallery-item">
                            <img src="<?php echo htmlspecialchars($artwork['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($artwork['title']); ?>"
                                onclick="openLightbox('<?php echo htmlspecialchars($artwork['image_path']); ?>', '<?php echo htmlspecialchars($artwork['title']); ?>', '<?php echo htmlspecialchars($artwork['artist_name']); ?>', <?php echo $artwork['id']; ?>)">
                            <div class="gallery-overlay">
                                <h6 class="mb-1"><?php echo htmlspecialchars($artwork['title']); ?></h6>
                                <small>by <?php echo htmlspecialchars($artwork['artist_name']); ?></small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Rp. <?php echo number_format($artwork['current_price'], 2, ',', '.'); ?></span>
                                    <span class="badge bg-info"><?php echo $artwork['total_bids']; ?> bids</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Masonry View -->
        <div id="masonryView" class="masonry-grid">
            <?php
            $result->data_seek(0); // Reset result pointer
            if ($result && $result->num_rows > 0):
            ?>
                <?php while ($artwork = $result->fetch_assoc()): ?>
                    <div class="masonry-item">
                        <div class="gallery-item">
                            <img src="<?php echo htmlspecialchars($artwork['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($artwork['title']); ?>"
                                onclick="openLightbox('<?php echo htmlspecialchars($artwork['image_path']); ?>', '<?php echo htmlspecialchars($artwork['title']); ?>', '<?php echo htmlspecialchars($artwork['artist_name']); ?>', <?php echo $artwork['id']; ?>)"
                                style="height: auto;">
                            <div class="gallery-overlay">
                                <h6 class="mb-1"><?php echo htmlspecialchars($artwork['title']); ?></h6>
                                <small>by <?php echo htmlspecialchars($artwork['artist_name']); ?></small>
                                <div class="mt-2">
                                    <span class="badge bg-success">Rp. <?php echo number_format($artwork['current_price'], 2, ',', '.'); ?></span>
                                    <span class="badge bg-info"><?php echo $artwork['total_bids']; ?> bids</span>
                                    <span class="badge <?php echo $artwork['status'] === 'active' ? 'bg-warning' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($artwork['status']); ?>
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <a href="auction_details.php?id=<?php echo $artwork['id']; ?>" class="btn btn-sm">View Auction</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No Artworks Yet</h4>
                        <p>Artists haven't uploaded any artworks yet. Check back later!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lightbox Modal -->
    <div class="modal fade" id="lightboxModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="lightboxTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="lightboxImage" src="/placeholder.svg" alt="" class="img-fluid" style="max-height: 70vh;">
                </div>
                <div class="modal-footer border-0 justify-content-between">
                    <div>
                        <small class="text-muted">by <span id="lightboxArtist"></span></small>
                    </div>
                    <div>
                        <a id="lightboxViewAuction" href="" class="btn  btn-sm">View Auction</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchView(view) {
            const gridView = document.getElementById('gridView');
            const masonryView = document.getElementById('masonryView');
            const buttons = document.querySelectorAll('.btn-group .btn');

            buttons.forEach(btn => btn.classList.remove('active'));

            if (view === 'grid') {
                gridView.style.display = 'flex';
                masonryView.style.display = 'none';
                buttons[0].classList.add('active');
            } else {
                gridView.style.display = 'none';
                masonryView.style.display = 'block';
                buttons[1].classList.add('active');
            }
        }

        function openLightbox(imageSrc, title, artist, auctionId) {
            document.getElementById('lightboxImage').src = imageSrc;
            document.getElementById('lightboxTitle').textContent = title;
            document.getElementById('lightboxArtist').textContent = artist;
            document.getElementById('lightboxViewAuction').href = 'auction_details.php?id=' + auctionId;

            new bootstrap.Modal(document.getElementById('lightboxModal')).show();
        }

        // Initialize masonry view
        switchView('masonry');
    </script>
</body>

</html>