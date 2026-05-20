<?php
// Ginamit ang __DIR__ para sa siguradong pathing ng system files
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

// 1. LOGIC PARA SA PAG-UPLOAD NG LARAWAN SA GALLERY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_gallery'])) {
    $title = trim($_POST['title']);
    $menu_item_id = !empty($_POST['menu_item_id']) ? intval($_POST['menu_item_id']) : null;
    $image_path = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            // Gumawa ng natatanging pangalan gamit ang timestamp (Hal: 1716000000_gallery.png)
            $new_file_name = time() . '_gallery.' . $file_ext;
            
            // Target storage folder path
            $target_dir = __DIR__ . '/../assets/uploads/';
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
                $image_path = 'assets/uploads/' . $new_file_name;
            } else {
                $error = 'Nabigong i-save ang larawan sa iyong server directory.';
            }
        } else {
            $error = 'Maling uri ng file. JPG, JPEG, PNG, o WEBP lamang ang tinatanggap.';
        }
    } else {
        $error = 'Mangyaring pumili ng larawan na ia-upload.';
    }

    // Kung walang error sa file system, isulat na sa database
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, image_path, menu_item_id) VALUES (:title, :image_path, :menu_item_id)");
            $stmt->execute([
                'title' => !empty($title) ? $title : null,
                'image_path' => $image_path,
                'menu_item_id' => $menu_item_id
            ]);
            $success = 'Matagumpay na naidagdag ang larawan sa iyong pampublikong Galerya!';
        } catch (\PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}

// 2. LOGIC PARA SA PAGBUBURA NG LARAWAN SA GALLERY (Mismong file + database record)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $delete_id = intval($_POST['delete_id']);

    try {
        // Kunin muna ang image path mula sa database para mabura ang pisikal na file sa folder
        $stmt_img = $pdo->prepare("SELECT image_path FROM gallery WHERE id = :id");
        $stmt_img->execute(['id' => $delete_id]);
        $gallery_item = $stmt_img->fetch();

        if ($gallery_item) {
            $full_file_path = __DIR__ . '/../' . $gallery_item['image_path'];
            if (!empty($gallery_item['image_path']) && file_exists($full_file_path)) {
                unlink($full_file_path); // Binubura ang totoong file sa computer mo
            }

            // Burahin ang record sa database
            $stmt_del = $pdo->prepare("DELETE FROM gallery WHERE id = :id");
            $stmt_del->execute(['id' => $delete_id]);
            $success = 'Matagumpay na naalis ang larawan sa Galerya.';
        }
    } catch (\PDOException $e) {
        $error = 'Hindi mabura ang larawan: ' . $e->getMessage();
    }
}

// KUNIN ANG MASTER MENU ITEMS PARA SA LINKING DROPDOWN SELECTION
$menu_items = $pdo->query("SELECT id, name FROM menu_items ORDER BY name ASC")->fetchAll();

// KUNIN LAHAT NG GALLERY RECORDS PARA IPAKITA SA CODES GRID VIEW
$query_gallery = "
    SELECT g.*, mi.name AS linked_food 
    FROM gallery g
    LEFT JOIN menu_items mi ON g.menu_item_id = mi.id
    ORDER BY g.uploaded_at DESC
";
$gallery_list = $pdo->query($query_gallery)->fetchAll();
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Manager - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navigation Bar Component -->
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
                <li class="nav-item"><a class="nav-link" href="daily_menu.php">Ulam Ngayong Araw</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold" href="gallery.php">Gallery Manager</a></li>
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
            <h2 class="fw-bold text-dark m-0">📸 Photo Gallery Management</h2>
            <p class="text-muted small">Pamahalaan ang mga larawang makikita ng publiko sa iyong website gallery page module.</p>
        </div>
    </div>

    <!-- Alert Notifications Feedback System -->
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
        <!-- LEFT COLUMN: IMAGE UPLOAD FORM -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0 fw-bold">Mag-upload ng Larawan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="gallery.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">Caption / Pamagat ng Larawan</label>
                            <input type="text" name="title" class="form-control" placeholder="Hal: Aming Malinis na Kusina">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-secondary">I-link sa partikular na Ulam (Opsyonal)</label>
                            <select name="menu_item_id" class="form-select">
                                <option value="">-- Huwag i-link sa kahit ano --</option>
                                <?php foreach ($menu_items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-secondary">Pumili ng File</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <div class="form-text small">JPG, PNG, o WEBP lamang ang pinapayagan.</div>
                        </div>
                        <button type="submit" name="upload_gallery" class="btn btn-danger w-100 fw-bold py-2 shadow-sm">
                            📤 I-upload sa Gallery
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: GALLERY PREVIEW TABLE DATA -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">📋 Mga Larawan sa Website Gallery</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Larawan</th>
                                    <th class="text-start">Caption / Pamagat</th>
                                    <th>Naka-link na Ulam</th>
                                    <th style="width: 100px;">Aksyon</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($gallery_list) > 0): ?>
                                    <?php foreach ($gallery_list as $row): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                    $img = !empty($row['image_path']) ? '../' . htmlspecialchars($row['image_path']) : '../assets/uploads/default-food.jpg';
                                                ?>
                                                <img src="<?php echo $img; ?>" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td class="text-start fw-semibold text-dark">
                                                <?php echo !empty($row['title']) ? htmlspecialchars($row['title']) : '<span class="text-muted small italic">Walang Caption</span>'; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['linked_food'])): ?>
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 border border-danger-subtle">
                                                        🍱 <?php echo htmlspecialchars($row['linked_food']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small">— Pangkalahatan —</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Dynamic trigger link button para mag-bura gamit ang Modal interface -->
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm fw-semibold" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-title="<?php echo !empty($row['title']) ? htmlspecialchars($row['title']) : 'Larawang ito'; ?>">
                                                    Burahin
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-muted py-5">Walang nakitang mga larawan sa Galerya. Mag-upload gamit ang form sa kaliwang bahagi.</td>
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

<!-- SINGLE REUSABLE BOOTSTRAP 5 CONFIRMATION MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">⚠️ Kumpirmahin ang Pagbura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="gallery.php" method="POST">
                <div class="modal-body p-4">
                    <p class="mb-1 text-dark">Sigurado ka bang nais mong permanenteng burahin ang larawang ito?</p>
                    <p class="fw-bold text-danger small bg-light p-2 rounded border" id="modalTargetName"></p>
                    <small class="text-muted d-block mt-2">Paunawa: Ang aksyong ito ay hindi na maaaring bawiin at ang pisikal na file ay permanenteng aalisin sa server storage.</small>
                    
                    <!-- Hidden Input Input Element para bitbitin ang ID papunta sa POST processing -->
                    <input type="hidden" name="delete_id" id="modalTargetId" value="">
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Kanselahin</button>
                    <button type="submit" name="confirm_delete" class="btn btn-danger fw-bold shadow-sm">Oo, Burahin Ito</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT LOGIC PARA PASUKIN ANG DYNAMIC DATA SA LOOB NG MODAL -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            // Button na nag-trigger sa modal
            const button = event.relatedTarget;
            
            // Kunin ang mga ipinasang data attributes
            const targetId = button.getAttribute('data-id');
            const targetTitle = button.getAttribute('data-title');
            
            // Hanapin ang mga HTML elements sa loob ng modal window
            const inputId = deleteModal.querySelector('#modalTargetId');
            const textTitle = deleteModal.querySelector('#modalTargetName');
            
            // I-inject ang totoong halaga o teksto
            inputId.value = targetId;
            textTitle.textContent = "Target: " + targetTitle;
        });
    }
});
</script>
</body>
</html>