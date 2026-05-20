<?php
// Simulan ang session handle kung hindi pa ito naisisimula
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Linisin ang lahat ng laman ng $_SESSION array variables
$_SESSION = array();

// 2. Kung gumagamit ang server ng session cookies, burahin din ito sa browser ng user
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Permanenteng sirain at tapusin ang session instance sa server
session_destroy();

// 4. Awtomatikong itapon ang user pabalik sa login form ng admin portal
header("Location: login.php");
exit;
?>