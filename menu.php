<?php 
require_once 'includes/header.php'; 

// Kunin ang kasalukuyang petsa para sa pagsusuri ng availability ngayon
$today = date('Y-m-d');

// 1. Kunin ang lahat ng ulam mula sa Master List (menu_items)
// Gagamit tayo ng LEFT JOIN para malaman kung ang ulam ay kasama sa daily_menu ng araw na ito
$query = "
    SELECT mi.*, 
           IF(dm.id IS NOT NULL AND dm.is_available = 1, 1, 0) AS available_today
    FROM menu_items mi
    LEFT JOIN daily_menu dm ON mi.id = dm.menu_item_id AND dm.available_date = :today
    ORDER BY mi.category ASC, mi.name ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['today' => $today]);
$all_menu_items = $stmt->fetchAll();

// Kunin ang active filter mula sa URL parameter (?category=Main+Dish) para sa interactive filtering
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : 'All';

// Tukuyin ang mga valid categories para sa validation
$categories = ['All', 'Main Dish', 'Soup', 'Side Dish', 'Dessert', 'Drinks'];
// INAYOS NA LINET: Ginamit ang tamang in_array() function ng PHP
if (!in_array($selected_category, $categories)) {
    $selected_category = 'All';
}
?>

<!-- Menu Page Title -->
<div class="text-center my-4">
    <h1 class="fw-bold text-dark">Ang Aming Kumpletong Menu</h1>
    <p class="text-muted">Silipin ang aming mga masasarap na luto. Pwede mong i-filter ang mga pagkain base sa kategorya nito.</p>
</div>

<!-- Responsive Category Filter Buttons -->
<div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
    <?php foreach ($categories as $cat): ?>
        <?php 
            // Palitan ang label kapag 'All' para sa lokal na konteksto
            $label = ($cat === 'All') ? 'Lahat ng Ulam' : $cat;
            // Baguhin ang kulay ng active button
            $btn_class = ($selected_category === $cat) ? 'btn-danger shadow-sm' : 'btn-outline-secondary';
            $url = ($cat === 'All') ? 'menu.php' : 'menu.php?category=' . urlencode($cat);
        ?>
        <a href="<?php echo $url; ?>" class="btn <?php echo $btn_class; ?> px-4 py-2 rounded-pill fw-semibold btn-sm">
            <?php echo htmlspecialchars($label); ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Food Grid Display -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php 
    $displayed_count = 0;
    
    foreach ($all_menu_items as $item): 
        // Logic para sa pag-filter sa PHP side
        if ($selected_category !== 'All' && $item['category'] !== $selected_category) {
            continue;
        }
        $displayed_count++;
        
        $img = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'assets/uploads/default-food.jpg';
        $is_available = (bool)$item['available_today'];
    ?>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden custom-card-hover position-relative">
                
                <!-- Availability Badge Overlay sa ibabaw ng litrato -->
                <div class="position-absolute top-0 end-0 m-2 z-1">
                    <?php if ($is_available): ?>
                        <span class="badge bg-success px-2 py-1 shadow-sm">Available Ngayon</span>
                    <?php else: ?>
                        <span class="badge bg-danger px-2 py-1 shadow-sm opacity-75">Not Available</span>
                    <?php endif; ?>
                </div>

                <!-- Image Frame -->
                <div style="height: 180px; overflow: hidden;" class="bg-light">
                    <img src="<?php echo $img; ?>" class="w-100 h-100 <?php echo !$is_available ? 'grayscale-img' : ''; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="object-fit: cover;">
                </div>

                <!-- Card Body content -->
                <div class="card-body d-flex flex-column p-3">
                    <div class="mb-1">
                        <small class="text-uppercase tracking-wider text-muted fw-bold font-monospace" style="font-size: 0.75rem;">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </small>
                    </div>
                    <h5 class="card-title fw-bold text-dark <?php echo !$is_available ? 'text-decoration-line-through text-muted' : ''; ?> mb-2">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </h5>
                    <p class="card-text text-muted small flex-grow-1">
                        <?php echo htmlspecialchars($item['description'] ?? 'Malinamnam na lutong bahay na inihanda para sa iyo.'); ?>
                    </p>
                    
                    <!-- Pricing Footer inside Card -->
                    <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-semibold">Presyo:</span>
                        <span class="fs-5 fw-bold <?php echo $is_available ? 'text-success' : 'text-muted'; ?>">
                            ₱<?php echo number_format($item['price'], 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Empty State Control kapag walang ulam sa napiling kategorya -->
    <?php if ($displayed_count === 0): ?>
        <div class="col-12 text-center py-5">
            <div class="p-5 bg-white rounded-4 border shadow-sm max-width-md mx-auto">
                <p class="text-muted fs-5 mb-0">Walang nakitang ulam sa kategoryang <strong>"<?php echo htmlspecialchars($selected_category); ?>"</strong> sa ngayon.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>