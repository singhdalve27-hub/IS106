<?php 
require_once 'includes/header.php'; 

$today = date('Y-m-d');

// Query para kunin ang mga ulam na itinakda ng admin para sa araw na ito
$stmt = $pdo->prepare("
    SELECT mi.* FROM menu_items mi
    JOIN daily_menu dm ON mi.id = dm.menu_item_id
    WHERE dm.available_date = :today AND dm.is_available = 1
");
$stmt->execute(['today' => $today]);
$todays_menu = $stmt->fetchAll();
?>

<!-- Responsive Hero Section -->
<div class="p-4 p-md-5 mb-5 bg-white rounded-4 shadow-sm border text-center">
    <div class="py-3 py-md-4">
        <h1 class="display-5 display-md-4 fw-bold text-danger mb-3">Mainit at Masarap na Lutong Bahay!</h1>
        <p class="col-lg-8 fs-5 mx-auto text-muted mb-4">Sariwang luto araw-araw para sa iyong pamilya. Silipin ang mga nakahandang masasarap na ulam para sa araw na ito.</p>
        <a href="menu.php" class="btn btn-danger btn-lg px-4 shadow-sm fw-bold">Tingnan ang Lahat ng Menu</a>
    </div>
</div>

<!-- Today's Menu Section Header -->
<div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
    <h2 class="h3 fw-bold text-dark m-0">🔥 Mga Ulam Ngayong Araw</h2>
    <span class="badge bg-danger px-3 py-2 rounded-pill fs-6">
        <?php echo date('M d, Y'); ?>
    </span>
</div>

<!-- Fully Responsive Food Grid -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php if (count($todays_menu) > 0): ?>
        <?php foreach ($todays_menu as $item): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden custom-card-hover">
                    <?php 
                        $img = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'assets/uploads/default-food.jpg';
                    ?>
                    <!-- Image Wrapper na may fixed proportion aspect-ratio gamit ang Bootstrap layout helper -->
                    <div class="position-relative" style="height: 200px; overflow: hidden;">
                        <img src="<?php echo $img; ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($item['name']); ?>" style="object-fit: cover;">
                    </div>
                    
                    <div class="card-body d-flex flex-column p-3">
                        <div class="mb-2">
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded border">
                                <?php echo htmlspecialchars($item['category']); ?>
                            </span>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <?php echo htmlspecialchars($item['description'] ?? 'Walang sapat na deskripsyon para sa pagkaing ito.'); ?>
                        </p>
                        <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Presyo:</span>
                            <span class="fs-5 fw-bold text-success">₱<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Pag walang ulam, ito ang lalabas na alert panel -->
        <div class="col-12 text-center py-5">
            <div class="p-5 bg-white rounded-3 border">
                <p class="text-muted fs-5 mb-0">Paumanhin, wala pang naihahandang menu para sa araw na ito. Magbalik mamaya o kontakin ang admin!</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>