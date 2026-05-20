<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🍳 Carenderia Password Verification Test</h2>";
echo "<hr>";

// 1. ANG TESTING DATA
$plain_password_input = "adminpassword"; // Ang password na itina-type mo sa login form

// Ang eksaktong bcrypt hash string na galing sa iyong phpMyAdmin SQL dump table structure
$database_stored_hash = '$2y$10$7R9Gv9v9gKk8g8f7f7f7fe3m8H8m8J8k8L8o8M8N8O8P8Q8R8S8Tu'; 

echo "<p><strong>Plain-text Input:</strong> <code>" . htmlspecialchars($plain_password_input) . "</code></p>";
echo "<p><strong>Stored Hash in DB:</strong> <code>" . htmlspecialchars($database_stored_hash) . "</code></p>";
echo "<hr>";

// 2. PAG-TEST GAMIT ANG PASSWORD_VERIFY()
echo "<h3>🔍 Pagpapatakbo ng password_verify()...</h3>";

if (password_verify($plain_password_input, $database_stored_hash)) {
    echo "<div style='padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; font-weight: bold;'>";
    echo "✅ SUCCESS: Tugma ang password! Ligtas at verified ang login logic mo.";
    echo "</div>";
} else {
    echo "<div style='padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 5px; font-weight: bold;'>";
    echo "❌ FAILED: Hindi tugma ang password sa hash string! Magkaiba sila.";
    echo "</div>";
    
    echo "<br><h4>💡 Paano ito aayusin kung nag-failed?</h4>";
    echo "<p>Maaaring nagkaroon ng typo o naputol ang hash string habang kinokopya sa SQL file. Narito ang bagong sariwang hash para sa string na <strong>'adminpassword'</strong>:</p>";
    
    // Gumawa ng bagong panibagong secure hash string
    $new_fresh_hash = password_hash($plain_password_input, PASSWORD_BCRYPT);
    
    echo "<pre style='background: #eee; padding: 10px; border: 1px solid #ccc; overflow-x: auto;'>" . $new_fresh_hash . "</pre>";
    echo "<p>I-execute mo lamang ang query na ito sa SQL tab ng iyong phpMyAdmin para i-reset ang iyong admin account password:</p>";
    echo "<pre style='background: #fff3cd; padding: 10px; border: 1px solid #ffeeba; color: #856404;'>UPDATE admin_users SET password_hash = '" . $new_fresh_hash . "' WHERE id = 1;</pre>";
}
?>