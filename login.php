<?php
session_start();

// ✅ HANYA validasi CSRF dan login jika method POST.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ CSRF Token check
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    // ✅ Hapus token setelah digunakan untuk mencegah reuse
    unset($_SESSION['csrf_token']);

    // ✅ Amankan session (regen ID untuk mencegah session fixation)
    session_regenerate_id(true);

    require_once 'config/database.php';
    $db = connect_db();

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header('Location: verify?error=empty');
        exit();
    }

    // ✅ Ambil user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        header('Location: verify?error=invalid_credentials');
        exit();
    }

    // ✅ Login sukses, set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['last_activity'] = time();
    $_SESSION['view_folder'] = ($user['id'] == 1) ? 'views' : 'views2';

    header("Location: overviews");
    exit();
}
?>
