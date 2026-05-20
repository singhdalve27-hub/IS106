<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Inayos ang pathing gamit ang __DIR__ para iwas error sa include streams
require_once __DIR__ . '/../includes/db.php';

// Kung naka-log in na, dumeretso na agad sa dashboard nang hindi na naglo-login ulit
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Ibe-verify ang password base sa bcrypt hash sa database
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Maling username o password!';
        }
    } else {
        $error = 'Mangyaring punan ang lahat ng field.';
    }
}
?>
<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Carenderia System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom aesthetics para sa mas modernong login panel */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .login-card {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.125) !important;
        }
        .toggle-password {
            cursor: pointer;
            z-index: 10;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            
            <!-- Elegant Modern White Card Container -->
            <div class="card login-card shadow border-0 rounded-4 overflow-hidden">
                <!-- Decorative Red Accent Accent Line at the Top -->
                <div style="height: 5px;" class="bg-danger"></div>
                
                <div class="card-body p-4 p-md-5">
                    <!-- Brand / System Header Branding View -->
                    <div class="text-center mb-4">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <span class="fs-2">🔒</span>
                        </div>
                        <h3 class="fw-black text-dark m-0 tracking-tight">Admin Portal</h3>
                        <p class="text-muted small mb-0">Kusina de Carenderia Management</p>
                    </div>
                    
                    <!-- Form PHP Validation Feedback Messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center border-0 py-2 small rounded-3 mb-4" role="alert">
                            🛑 <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        
                        <!-- Username Floating Label Form Group -->
                        <div class="form-floating mb-3">
                            <input type="text" name="username" id="username" class="form-control border-secondary border-opacity-25 rounded-3" placeholder="Username" required autocomplete="off">
                            <label for="username" class="text-secondary fw-semibold">Username</label>
                        </div>
                        
                        <!-- Password Input Structure Group with Eye Toggle Icon -->
                        <div class="form-floating mb-4 position-relative">
                            <input type="password" name="password" id="password" class="form-control border-secondary border-opacity-25 rounded-3 pe-5" placeholder="Password" required>
                            <label for="password" class="text-secondary fw-semibold">Password</label>
                            
                            <!-- Dynamic Eye Toggle Icon Anchor -->
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted toggle-password" id="togglePasswordBtn">
                                👁️
                            </span>
                        </div>
                        
                        <!-- Submit Execution Action Button Control -->
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-3 rounded-3 shadow-sm text-uppercase tracking-wider">
                            Mag-log In →
                        </button>
                    </form>
                    
                    <!-- Footer Link Redirection Back home -->
                    <div class="text-center mt-4 pt-2 border-top border-light">
                        <a href="../index.php" class="text-secondary text-decoration-none small hover-danger transition">
                            ← Bumalik sa Public Website
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle Integration -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JAVASCRIPT: LOGIC PARA SA SHOW/HIDE PASSWORDS FUNCTIONALITY -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            // Suriin kung kasalukuyang nakatago o nakadispley ang karakter
            const isPassword = passwordInput.getAttribute('type') === 'password';
            
            // Magpalit ng input behavior base sa type
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Baguhin ang emoji o hitsura ng toggle indicator
            this.textContent = isPassword ? '🙈' : '👁️';
        });
    }
});
</script>
</body>
</html>