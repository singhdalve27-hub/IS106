<?php
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$success_msg = '';

// Mag-update ng Settings kapag nag-submit ang admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $store_name = trim($_POST['store_name']);
    $operating_hours = trim($_POST['operating_hours']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    $stmt_update = $pdo->prepare("
        UPDATE settings 
        SET store_name = :store_name, operating_hours = :operating_hours, contact_number = :contact_number, address = :address
        WHERE id = 1
    ");
    $stmt_update->execute([
        'store_name' => $store_name,
        'operating_hours' => $operating_hours,
        'contact_number' => $contact_number,
        'address' => $address
    ]);
    $success_msg = "Matagumpay na na-update ang impormasyon ng carenderia!";
}

// Kunin ang kasalukuyang datos para sa system overview counters at forms
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();
$total_items = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

$today = date('Y-m-d');
$stmt_today_count = $pdo->prepare("SELECT COUNT(*) FROM daily_menu WHERE available_date = :today AND is_available = 1");
$stmt_today_count->execute(['today' => $today]);
$today_items = $stmt_today_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Carenderia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Responsive Admin Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php">🛠️ Carenderia Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active fw-bold" href="dashboard.php">Overview & Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="menu_items.php">Master Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="daily_menu.php">Ulam Ngayong Araw</a></li>
                <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery Manager</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="btn btn-danger btn-sm px-3 fw-bold shadow-sm" href="logout.php">Log Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12 text-center text-md-start">
            <h2 class="fw-bold text-dark mb-1">Kumusta, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! 👋</h2>
            <p class="text-muted small">Pamahalaan ang mga ulam, operating hours, at profile settings ng iyong online digital menu board.</p>
        </div>
    </div>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            ✨ <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Overview Statistics Badges -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-6">
            <div class="card border-0 bg-primary text-white shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="text-uppercase mb-2 opacity-75 small fw-bold">Kabuuang Ulam sa System (Master List)</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $total_items; ?></h2>
                    <a href="menu_items.php" class="text-white-50 small text-decoration-none d-block mt-3 fw-semibold">I-manage ang Master List →</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-0 bg-success text-white shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="text-uppercase mb-2 opacity-75 small fw-bold">Mga Nakahandang Ulam Ngayong Araw</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $today_items; ?></h2>
                    <a href="daily_menu.php" class="text-white-50 small text-decoration-none d-block mt-3 fw-semibold">Palitan ang Menu Ngayon →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Settings Component Form -->
    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">📝 Profile Information Settings</h5>
                </div>
                <div class="card-body p-4">
                    <form action="dashboard.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Pangalan ng Carenderia</label>
                            <input type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-secondary">Oras ng Pagbubukas/Pagsasara</label>
                                <input type="text" name="operating_hours" class="form-control" value="<?php echo htmlspecialchars($settings['operating_hours'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label fw-semibold small text-secondary">Numero ng Telepono / GCash</label>
                                <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($settings['contact_number'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-secondary">Eksaktong Lokasyon / Address</label>
                            <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_settings" class="btn btn-dark fw-bold px-4 shadow-sm">I-save ang mga Pagbabago</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>