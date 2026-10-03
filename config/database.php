<?php
date_default_timezone_set('Asia/Jakarta');
$host = "localhost";
$username = "root";
$password = "";
$database = "surat_db";

$db = new mysqli($host, $username, $password, $database);

if ($db->connect_error) {
    die("Koneksi database gagal: " . $db->connect_error);
}

// Set timezone database
$db->query("SET time_zone = '+07:00'"); // Atau 'Asia/Jakarta' jika didukung

function connect_db() {
    global $host, $username, $password, $database;
    $db = new mysqli($host, $username, $password, $database);
    if ($db->connect_error) {
        die("Koneksi database gagal: " . $db->connect_error);
    }

    // Set timezone database untuk setiap koneksi baru
    $db->query("SET time_zone = '+07:00'"); // Atau 'Asia/Jakarta' jika didukung
    return $db;
}

function cleanup_otp_requests() {
    global $db;
    // Hapus record yang lebih lama dari 7 hari
    $db->query("DELETE FROM otp_requests WHERE request_time < DATE_SUB(NOW(), INTERVAL 7 DAY)");
    // Hapus OTP yang sudah expired lebih dari 1 hari
    $db->query("DELETE FROM otp_codes WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
}

// Panggil fungsi cleanup secara periodik (10% chance)
if (rand(1, 10) === 1) {
    cleanup_otp_requests();
}
?>