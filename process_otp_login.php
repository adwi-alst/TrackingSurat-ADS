<?php
session_start();
require_once 'config/database.php';

// Validasi session dan OTP
if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: forgot?error=unauthorized");
    exit;
}

$email = $_SESSION['otp_email'];
$db = connect_db();

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    // Set session untuk update profil
    $_SESSION['user_id_temp'] = $user['id'];
    $_SESSION['user_email'] = $email;
    
    // Hapus semua OTP untuk email ini
    $stmt = $db->prepare("DELETE FROM otp_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Redirect ke halaman update profil
    header('Location: update_profil');
    exit;
} else {
    header("Location: forgot?error=user_not_found");
    exit;
}
?>