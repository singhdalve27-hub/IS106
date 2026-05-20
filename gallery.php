<?php 
require_once 'includes/header.php'; 

// Query para kunin ang lahat ng larawan mula sa gallery table
// Kasama ang pangalan at presyo ng ulam kung naka-link ito sa isang item sa menu_items
$query = "
    SELECT g.*, mi.name AS food_name, mi.price AS food_price 
    FROM gallery g
    LEFT JOIN menu_items mi ON g.menu_item_id = mi.id
    ORDER BY g.uploaded_at DESC
";

try {
    $stmt = $pdo->query($query);
    $gallery_items = $stmt->fetchAll();
} catch (\PDOException $e) {
    $gallery_items = [];
}
?>

<!-- Gallery Page Title -->
<div class="text-center my-4">
    <h1 class="fw-bold text-dark">📸 Aming Galerya</h1>
    <p class="text-muted">Silipin ang mga kaganapan sa aming kusina at ang mga paborito ninyong lutong bahay.</p>
</div>

<!-- Responsive Photo Grid -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php if (count($gallery_items) > 0): ?>
        <?php foreach ($gallery_items as $index => $item): ?>
            <?php 
                // Siguraduhing may default fallback image kung sakaling nawala ang file
                $img_path = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'assets/uploads/default-food.jpg';
                $title = !empty($item['title']) ? htmlspecialchars($item['title']) : 'Sariwang Luto';
            ?>
            <div class="col">
                <!-- Card Container na pwedeng i-click para mag-trigger ng Bootstrap Modal -->
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden custom-gallery-card" 
                     data-bs-toggle="modal" 
                     data-bs-target="#imageModal<?php echo $index; ?>" 
                     style="cursor: pointer;">
                    
                    <!-- Image Wrapper na may fixed proportions -->
                    <div style="height: 240px; overflow: hidden;" class="bg-dark position-relative group">
                        <img src="<?php echo $img_path; ?>" class="w-100 h-100 gallery-thumbnail" alt="<?php echo $title; ?>" style="object-fit: cover;">
                        <!-- Zoom Icon Overlay gamit ang purong CSS -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25 opacity-0 gallery-overlay">
                            <span class="btn btn-light btn-sm fw-bold rounded-pill shadow-sm">🔍 Tingnan</span>
                        </div>
                    </div>
                    
                    <!-- Card Footer / Caption Text -->
                    <?php if (!empty($item['title']) || !empty($item['food_name'])): ?>
                        <div class="card-body p-3 bg-white border-top">
                            <h6 class="fw-bold text-dark mb-1 text-truncate"><?php echo $title; ?></h6>
                            <?php if (!empty($item['food_name'])): ?>
                                <span class="badge bg-danger-subtle text-danger small">
                                    🍱 <?php echo htmlspecialchars($item['food_name']); ?> (₱<?php echo number_format($item['food_price'], 2); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bootstrap 5 Lightbox Modal para sa larawang ito -->
            <div class="modal fade" id="imageModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $index; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 bg-transparent">
                        <div class="modal-header border-0 p-0 justify-content-end">
                            <button type="button" class="btn-close btn-close-white p-3 fs-4" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0 text-center">
                            <img src="<?php echo $img_path; ?>" class="img-fluid rounded-3 shadow-lg" alt="<?php echo $title; ?>" style="max-height: 80vh;">
                            <!-- Caption sa ilalim ng pinalaking larawan -->
                            <div class="bg-dark bg-opacity-75 text-white p-3 rounded-bottom mt-2">
                                <h5 class="fw-bold m-0"><?php echo $title; ?></h5>
                                <?php if (!empty($item['food_name'])): ?>
                                    <p class="text-warning small m-0 mt-1">Naka-link sa Ulam: <?php echo htmlspecialchars($item['food_name']); ?> — ₱<?php echo number_format($item['food_price'], 2); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php else: ?>
        <!-- Empty State kapag walang laman ang gallery table -->
        <div class="col-12 text-center py-5">
            <div class="p-5 bg-white rounded-4 border shadow-sm mx-auto" style="max-width: 500px;">
                <p class="text-muted fs-5 mb-0">Wala pang nakalagay na mga larawan sa galerya sa ngayon.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>