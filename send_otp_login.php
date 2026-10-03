<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'config.env');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include("config/database.php");

// Pastikan tabel otp_requests ada
function ensureTablesExist($db) {
    $db->query("CREATE TABLE IF NOT EXISTS otp_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email_time (email, request_time),
        INDEX idx_ip_time (ip_address, request_time)
    )");
    
    $db->query("CREATE TABLE IF NOT EXISTS otp_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        is_used TINYINT(1) DEFAULT 0,
        INDEX (email),
        INDEX (created_at),
        INDEX (ip_address)
    )");
}

// Validasi request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    header("Location: forgot?error=method_not_allowed");
    exit;
}

// Validasi input email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header("Location: forgot?error=invalid_email");
    exit;
}

// Pastikan email ada di database user
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    header("Location: forgot?error=email_not_registered");
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'];
$now = time();

// Pastikan tabel ada
ensureTablesExist($db);

try {
    $db->begin_transaction();

    // 1. LIMIT BERDASARKAN EMAIL (5x/hari)
    $stmt = $db->prepare("SELECT COUNT(*) as email_count FROM otp_requests 
                         WHERE email = ? AND request_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['email_count'] >= 5) {
        throw new Exception("Email request limit exceeded");
    }

    // 2. LIMIT BERDASARKAN IP (10x/hari)
    $stmt = $db->prepare("SELECT COUNT(*) as ip_count FROM otp_requests 
                         WHERE ip_address = ? AND request_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['ip_count'] >= 10) {
        throw new Exception("IP request limit exceeded");
    }

    // 3. ANTISPAM - MINIMAL 30 DETIK ANTAR REQUEST
    $stmt = $db->prepare("SELECT MAX(request_time) as last_request FROM otp_requests 
                         WHERE email = ? OR ip_address = ?");
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['last_request'] && (strtotime($row['last_request']) > ($now - 30))) {
        throw new Exception("Request too soon");
    }

    // Hapus OTP lama untuk email ini
    $stmt = $db->prepare("DELETE FROM otp_codes WHERE email = ?");
    if (!$stmt->bind_param("s", $email) || !$stmt->execute()) {
        throw new Exception("Failed to delete old OTP codes");
    }

    // Generate OTP baru
    $otp = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    $expires_at = date('Y-m-d H:i:s', $now + 600); // 10 menit
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);

    // Simpan OTP baru
    $stmt = $db->prepare("INSERT INTO otp_codes (email, otp_hash, expires_at, ip_address, is_used) 
                         VALUES (?, ?, ?, ?, 0)");
    if (!$stmt->bind_param("ssss", $email, $otp_hash, $expires_at, $ip) || !$stmt->execute()) {
        throw new Exception("Failed to save new OTP code");
    }

    // Log permintaan OTP
    $stmt = $db->prepare("INSERT INTO otp_requests (email, ip_address, request_time) 
                         VALUES (?, ?, NOW())");
    if (!$stmt->bind_param("ss", $email, $ip) || !$stmt->execute()) {
        throw new Exception("Failed to log OTP request");
    }

    // Kirim email OTP
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Permintaan Pengaturan Ulang Kata Sandi';
       $mail->Body = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;">
  <div style="max-width: 600px; margin: auto; background-color: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 8px rgba(0,0,0,0.05);">
    <h2 style="color: #333;">Pengaturan Ulang Kata Sandi</h2>
    <p>Halo,</p>
    <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.</p>
    <p style="margin: 30px 0; text-align: center;">
      <span style="display: inline-block; font-size: 32px; font-weight: bold; color: #2E86C1; letter-spacing: 6px;">' . $otp . '</span>
    </p>
    <p>Silakan masukkan kode tersebut untuk melanjutkan proses. Kode ini berlaku selama <strong>10 menit</strong> dan hanya dapat digunakan satu kali.</p>
    <p style="color: #d9534f;"><strong>Penting:</strong> Jangan bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku sebagai administrator.</p>
    <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini. Tidak ada tindakan lebih lanjut yang diperlukan.</p>
    <br>
    <p>Hormat kami,<br><strong>Tim ' . $_ENV['SMTP_FROM_NAME'] . '</strong></p>
  </div>
</body>
</html>';

        if (!$mail->send()) {
            throw new Exception("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
        }
    } catch (Exception $e) {
        throw new Exception("Mailer Error: " . $e->getMessage());
    }

    $db->commit();

    // Set session
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_last_sent'] = $now;
    $_SESSION['verify_attempts'] = 0;
    
    header("Location: verify_otp_login");
    exit;

} catch (Exception $e) {
    $db->rollback();
    error_log("OTP System Error: " . $e->getMessage());
    
    // Tentukan jenis error untuk redirect
    $error = 'otp_failed';
    if ($e->getMessage() === "Email request limit exceeded") $error = 'email_limit_exceeded';
    if ($e->getMessage() === "IP request limit exceeded") $error = 'ip_limit_exceeded';
    if ($e->getMessage() === "Request too soon") $error = 'request_too_soon';
    
    header("Location: forgot?error=" . $error);
    exit;
}
?>