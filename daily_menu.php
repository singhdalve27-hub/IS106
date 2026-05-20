<?php
// Ginamit ang __DIR__ para sa siguradong pathing ng system files
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';
$today = date('Y-m-d');

// 1. LOGIC PARA SA PAGDADAGDAG NG ULAM SA MENU NGAYON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_day'])) {
    $menu_item_id = intval($_POST['menu_item_id']);

    if ($menu_item_id > 0) {
        try {
            // Gagamit ng INSERT IGNORE o ON DUPLICATE KEY para iwas duplication error sa db
            $stmt = $pdo->prepare("
                INSERT INTO daily_menu (menu_item_id, available_date, is_available) 
                VALUES (:menu_item_id, :today, 1)
                ON DUPLICATE KEY UPDATE is_available = 1
            ");
            $stmt->execute([
                'menu_item_id' => $menu_item_id,
                'today' => $today
            ]);
            $success = "Matagumpay na idinagdag ang ulam sa listahan ngayong araw!";
        } catch (\PDOException $e) {
            $error = "Error sa pag-save: " . $e->getMessage();
        }
    } else {
        $error = "Mangyaring pumili ng valid na ulam mula sa listahan.";
    }
}

// 2. LOGIC PARA SA PAG-TOGGLE NG AVAILABILITY (AVAILABLE VS SOLD OUT)
if (isset($_GET['toggle_status']) && isset($_GET['current'])) {
    $id = intval($_GET['toggle_status']);
    $current_status = intval($_GET['current']);
    $new_status = ($current_status === 1) ? 0 : 1;

    try {
        $stmt = $pdo->prepare("UPDATE daily_menu SET is_available = :new_status WHERE id = :id");
        $stmt->execute(['new_status' => $new_status, 'id' => $id]);
        $success = "Matagumpay na nabago ang status ng ulam!";
    } catch (\PDOException $e) {
        $error = "Hindi ma-update ang status: " . $e->getMessage();
    }
}

// 3. LOGIC PARA SA PAG-ALIS NG ULAM SA MENU NGAYON (REMOVE VIA POST MODAL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_remove'])) {
    $remove_id = intval($_POST['remove_id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM daily_menu WHERE id = :id");
        $stmt->execute(['id' => $remove_id]);
        $success = "Tinanggal ang ulam sa menu ng araw na ito.";
    } catch (\PDOException $e) {
        $error = "Hindi maalis ang ulam: " . $e->getMessage();
    }
}

// KUNIN ANG MGA KILALANG ULAM PARA SA DROPDOWN SELECTOR (Master List)
// Sinasala natin para hindi na lumabas sa dropdown ang mga naidagdag na para sa araw na ito
$dropdown_items = $pdo->prepare("
    SELECT * FROM menu_items 
    WHERE id NOT IN (SELECT menu_item_id FROM daily_menu WHERE available_date = :today)
    ORDER BY category ASC, name ASC
");
$dropdown_items->execute(['today' => $today]);
$available_choices = $dropdown_items->fetchAll();

// KUNIN ANG LAHAT NG KASALUKUYANG ULAM NGAYONG ARAW
$stmt_current = $pdo->prepare("
    SELECT dm.id AS daily_id, dm.is_available, mi.name, mi.category, mi.price, mi.image_path
    FROM daily_menu dm
    JOIN menu_items mi ON dm.menu_item_id = mi.id
    WHERE dm.available_date = :today
    ORDER BY mi.category ASC, mi.name ASC
");
$stmt_current->execute(['today' => $today]);
$todays_menu = $stmt_current->fetchAll();
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Menu Planner - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Admin Navigation Top Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php">🛠️ Carenderia Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Overview & Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="menu_items.php">Master Menu</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold" href="daily_menu.php">Ulam Ngayong Araw</a></li>
                <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery Manager</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="btn btn-danger btn-sm px-3 fw-bold shadow-sm" href="logout.php">Log Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row mb-4">
        <div class="col-12 text-center text-md-start">
            <h2 class="fw-bold text-dark m-0">📅 Ulam Ngayong Araw Planner</h2>
            <p class="text-muted small">Pumili mula sa Master List kung anong mga putahe ang nakasalang o luto na para sa araw na ito: <strong><?php echo date('F d, Y'); ?></strong>.</p>
        </div>
    </div>

    <!-- Feedback Message System Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            ✨ <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            ❌ <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT COLUMN: DROPDOWN SELECTOR FORM -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0 fw-bold">Isalang sa Menu Ngayon</h5>
                </div>
                <div class="card-body p-4">
                    <form action="daily_menu.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Pumili ng Putahe</label>
                            <select name="menu_item_id" class="form-select" required>
                                <option value="">-- Piliin sa Master Menu --</option>
                                <?php if (count($available_choices) > 0): ?>
                                    <?php $current_cat = ''; ?>
                                    <?php foreach ($available_choices as $choice): ?>
                                        <?php if ($current_cat !== $choice['category']): ?>
                                            <?php $current_cat = $choice['category']; ?>
                                            <optgroup label="====== <?php echo $current_cat; ?> ======"></optgroup>
                                        <?php endif; ?>
                                        <option value="<?php echo $choice['id']; ?>">
                                            <?php echo htmlspecialchars($choice['name']); ?> (₱<?php echo number_format($choice['price'], 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Lahat ng ulam ay nakasalang na o walang laman ang Master List.</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_to_day" class="btn btn-danger w-100 fw-bold py-2 shadow-sm" <?php echo (count($available_choices) === 0) ? 'disabled' : ''; ?>>
                            ➕ Idagdag sa Listahan Ngayon
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: CURRENT ACTIVE DISHES LIST TABLE -->
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">📋 Mga Aktibong Ulam sa Website Ngayon</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Litrato</th>
                                    <th class="text-start">Pangalan</th>
                                    <th>Kategorya</th>
                                    <th>Status</th>
                                    <th>Aksyon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($todays_menu) > 0): ?>
                                    <?php foreach ($todays_menu as $row): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                    $img = !empty($row['image_path']) ? '../' . htmlspecialchars($row['image_path']) : '../assets/uploads/default-food.jpg';
                                                ?>
                                                <img src="<?php echo $img; ?>" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                                            </td>
                                            <td class="fw-bold text-dark text-start"><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><span class="badge bg-secondary px-2 py-1"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                            <td>
                                                <!-- Dynamic Availability Toggle badge button link -->
                                                <?php if ($row['is_available'] == 1): ?>
                                                    <a href="daily_menu.php?toggle_status=<?php echo $row['daily_id']; ?>&current=1" class="btn btn-success btn-sm fw-bold rounded-pill px-3 py-0 fs-6">
                                                        Available
                                                    </a>
                                                <?php else: ?>
                                                    <a href="daily_menu.php?toggle_status=<?php echo $row['daily_id']; ?>&current=0" class="btn btn-danger btn-sm fw-bold rounded-pill px-3 py-0 fs-6">
                                                        Sold Out
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Inayos na Button: Gagawa ng Trigger para sa Bootstrap 5 Modal -->
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm px-2 py-1 fw-semibold"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#removeModal"
                                                        data-id="<?php echo $row['daily_id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    Alisin
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-muted py-5">Walang nakasala na ulam para sa araw na ito. Pumili sa kaliwang bahagi para magkaroon ng laman ang website homepage mo.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SINGLE REUSABLE BOOTSTRAP 5 CONFIRMATION MODAL PARA SA ALISIN -->
<div class="modal fade" id="removeModal" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-bold" id="removeModalLabel">📋 Tanggalin sa Menu Ngayon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="daily_menu.php" method="POST">
                <div class="modal-body p-4">
                    <p class="mb-1 text-dark">Sigurado ka bang nais mong alisin ang ulam na ito sa listahan ngayong araw?</p>
                    <p class="fw-bold text-danger small bg-light p-2 rounded border" id="modalTargetName"></p>
                    <small class="text-muted d-block mt-2">Paunawa: Hindi ito mabubura sa Master Menu, matatanggal lamang ito sa display ng homepage para sa araw na ito.</small>
                    
                    <!-- Hidden input para bitbitin ang id sa POST processor -->
                    <input type="hidden" name="remove_id" id="modalTargetId" value="">
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Kanselahin</button>
                    <button type="submit" name="confirm_remove" class="btn btn-dark fw-bold shadow-sm">Oo, Alisin Ito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT LOGIC PARA SA DYNAMIC DATA INJECTION SA LOOB NG MODAL -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const removeModal = document.getElementById('removeModal');
    if (removeModal) {
        removeModal.addEventListener('show.bs.modal', function (event) {
            // Button na nag-trigger sa modal panel
            const button = event.relatedTarget;
            
            // Kunin ang data-attributes ng binuong hilera
            const targetId = button.getAttribute('data-id');
            const targetName = button.getAttribute('data-name');
            
            // Hanapin ang mga field sa loob ng target modal frame
            const inputId = removeModal.querySelector('#modalTargetId');
            const textName = removeModal.querySelector('#modalTargetName');
            
            // I-inject ang mga tunay na halaga
            inputId.value = targetId;
            textName.textContent = "Ulam: " + targetName;
        });
    }
});
</script>
</body>
</html>