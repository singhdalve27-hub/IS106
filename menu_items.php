<?php
// Ginamit ang __DIR__ para sa siguradong pathing ng system files
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';

$success = '';
$error = '';

// 1. LOGIC PARA SA PAGDADAGDAG NG ULAM (ADD ITEM)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $image_path = null;

    // Secure File Upload Handling para sa mga Litrato ng Pagkain
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_exts)) {
            // Gumagawa ng unique filename gamit ang timestamp (Hal: 1716000000_pork_adobo.png)
            $clean_name = preg_replace("/[^a-zA-Z0-9-]/", "_", strtolower($name));
            $new_file_name = time() . '_' . $clean_name . '.' . $file_ext;
            
            // Absolute path patungo sa public uploads folder ng iyong project
            $target_dir = __DIR__ . '/../assets/uploads/';
            
            // Siguraduhing gawa ang folder bago mag-upload
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            if (move_uploaded_file($file_tmp, $target_dir . $new_file_name)) {
                // Ang path na ise-save sa DB ay relative sa pampublikong frontend natin
                $image_path = 'assets/uploads/' . $new_file_name;
            } else {
                $error = 'Nabigong i-save ang larawan sa server storage folder.';
            }
        } else {
            $error = 'Maling uri ng file. Ang pinahihintulutan lamang ay JPG, JPEG, PNG, o WEBP.';
        }
    }

    // Isulat sa database kung walang nakitang error sa file validation
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO menu_items (name, description, category, price, image_path) VALUES (:name, :description, :category, :price, :image_path)");
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'price' => $price,
                'image_path' => $image_path
            ]);
            $success = 'Matagumpay na naidagdag ang bagong pagkain sa iyong Master List!';
        } catch (\PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
        }
    }
}

// 2. LOGIC PARA SA PAGBUBURA NG ULAM (DELETE ITEM VIA POST MODAL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $delete_id = intval($_POST['delete_id']);
    
    try {
        // Alamin muna kung may lumang file para burahin din sa computer storage (anti-garbage collection)
        $stmt_img = $pdo->prepare("SELECT image_path FROM menu_items WHERE id = :id");
        $stmt_img->execute(['id' => $delete_id]);
        $item = $stmt_img->fetch();

        if ($item) {
            $full_image_file = __DIR__ . '/../' . $item['image_path'];
            if (!empty($item['image_path']) && file_exists($full_image_file)) {
                unlink($full_image_file); // Binubura ang totoong file sa assets/uploads/
            }
            
            // Buburahin ang hilera sa database
            $stmt_del = $pdo->prepare("DELETE FROM menu_items WHERE id = :id");
            $stmt_del->execute(['id' => $delete_id]);
            $success = 'Matagumpay na naalis ang ulam mula sa iyong system.';
        }
    } catch (\PDOException $e) {
        $error = 'Hindi magawang burahin ang ulam: ' . $e->getMessage();
    }
}

// Kunin ang pinakabagong talaan ng mga ulam para sa data table array module
$all_items = $pdo->query("SELECT * FROM menu_items ORDER BY category ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Menu Manager - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navigation Panel Link Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php">🛠️ Carenderia Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Overview & Settings</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold" href="menu_items.php">Master Menu</a></li>
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
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold m-0">📁 Master List Management</h2>
            <p class="text-muted small m-0">Dito mo idinedeklara ang kabuuang listahan ng putaheng alam lutuin ng iyong carenderia.</p>
        </div>
        <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addItemForm">
            ➕ Magdagdag ng Bagong Ulam
        </button>
    </div>

    <!-- Alert Notifications Feedback panel -->
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

    <!-- Collapsible Form panel para sa pagpapasok ng ulam -->
    <div class="collapse mb-4" id="addItemForm">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold">Form ng Bagong Putahe</h5>
            </div>
            <div class="card-body p-4">
                <form action="menu_items.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold small text-secondary">Pangalan ng Pagkain / Inumin</label>
                            <input type="text" name="name" class="form-control" placeholder="Hal: Pork Menudo" required autocomplete="off">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold small text-secondary">Kategorya</label>
                            <select name="category" class="form-select" required>
                                <option value="Main Dish">Main Dish</option>
                                <option value="Side Dish">Side Dish</option>
                                <option value="Soup">Soup</option>
                                <option value="Dessert">Dessert</option>
                                <option value="Drinks">Drinks</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold small text-secondary">Presyo kada Order (PHP)</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Maikling Deskripsyon</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Isulat ang mga detalye o pangunahing sangkap..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-secondary">Litrato ng Ulam</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text small">Opsyonal. Tanging mga files na may format na JPG, PNG, o WEBP ang tatanggapin.</div>
                    </div>
                    <div class="text-end">
                        <button type="submit" name="add_item" class="btn btn-success fw-bold px-4 shadow-sm">I-save sa System Database</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Master Items Data View Grid Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 80px;">Litrato</th>
                            <th class="text-start">Pangalan ng Luto</th>
                            <th>Kategorya</th>
                            <th>Presyo</th>
                            <th class="text-start">Deskripsyon</th>
                            <th style="width: 100px;">Aksyon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($all_items) > 0): ?>
                            <?php foreach ($all_items as $row): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            // Fallback checker para sa absolute rendering ng path image file
                                            $img_src = !empty($row['image_path']) ? '../' . htmlspecialchars($row['image_path']) : '../assets/uploads/default-food.jpg';
                                        ?>
                                        <img src="<?php echo $img_src; ?>" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td class="fw-bold text-dark text-start"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><span class="badge bg-secondary px-2 py-1"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                    <td class="text-success fw-bold">₱<?php echo number_format($row['price'], 2); ?></td>
                                    <td class="text-muted small text-start text-truncate" style="max-width: 260px;">
                                        <?php echo htmlspecialchars($row['description'] ?? 'Walang deskripsyon.'); ?>
                                    </td>
                                    <td>
                                        <!-- Modipikadong Trigger Button para sa Bootstrap 5 Delete Confirmation Modal -->
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm fw-bold px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                            Burahin
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-muted py-4">Walang laman ang iyong Master List. Magpasok ng bago gamit ang pulang button sa itaas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- SINGLE REUSABLE BOOTSTRAP 5 CONFIRMATION MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">⚠️ Permanenteng Pagbura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="menu_items.php" method="POST">
                <div class="modal-body p-4">
                    <p class="mb-1 text-dark">Sigurado ka bang nais mong permanenteng burahin ang putaheng ito mula sa Master Menu?</p>
                    <p class="fw-bold text-danger small bg-light p-2 rounded border" id="modalTargetName"></p>
                    <small class="text-muted d-block mt-2">Paunawa: Kakalat ang pagbura na ito (`CASCADE`). Awtomatikong matatanggal din ang ulam sa iskedyul ng **Ulam Ngayong Araw** at mabubura ang file ng larawan nito sa server storage.</small>
                    
                    <!-- Hidden input element na may dalang dynamic Primary Key ID -->
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

<!-- JAVASCRIPT LOGIC PARA SA DYNAMIC MODAL INJECTION -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            // Kunin ang element button na nag-trigger sa event frame
            const button = event.relatedTarget;
            
            // Hilahin ang mga nakatagong data-attributes
            const targetId = button.getAttribute('data-id');
            const targetName = button.getAttribute('data-name');
            
            // Map sa tamang lalagyan sa loob ng dynamic form fields
            const inputId = deleteModal.querySelector('#modalTargetId');
            const textName = deleteModal.querySelector('#modalTargetName');
            
            // Isulat ang mga impormasyon
            inputId.value = targetId;
            textName.textContent = "Ulam: " + targetName;
        });
    }
});
</script>
</body>
</html>