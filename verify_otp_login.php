<?php
session_start();

// Buat CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Tangani POST hanya jika permintaan memang valid dan memiliki CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }
    // Jangan langsung unset CSRF token di sini.
}

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'config.env');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("config/database.php");

// Validasi session OTP
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot?error=session_expired");
    exit;
}

// Rate limiting untuk verifikasi (5x percobaan)
if (!isset($_SESSION['verify_attempts'])) {
    $_SESSION['verify_attempts'] = 0;
}

if ($_SESSION['verify_attempts'] >= 5) {
    header("Location: forgot?error=too_many_attempts");
    exit;
}

// Handle Resend OTP Request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend_otp'])) {
    $email = $_SESSION['otp_email'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $current_time = time();

    try {
        $db->begin_transaction();

        // 1. Batasi pengiriman ulang (maksimal 3x dalam 1 jam per email)
        $stmt = $db->prepare("SELECT COUNT(*) as resend_count FROM otp_requests 
                             WHERE email = ? AND request_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resend_count = $stmt->get_result()->fetch_assoc()['resend_count'];

        if ($resend_count >= 3) {
            throw new Exception("too_many_resend");
        }

        // 2. Hapus OTP lama
        $stmt = $db->prepare("DELETE FROM otp_codes WHERE email = ?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete old OTP");
        }

        // 3. Generate OTP baru (6 digit alfanumerik)
        $otp = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $expires_at = date('Y-m-d H:i:s', $current_time + 600); // 10 menit
        $otp_hash = password_hash($otp, PASSWORD_DEFAULT);

        // 4. Simpan OTP baru ke database
        $stmt = $db->prepare("INSERT INTO otp_codes (email, otp_hash, expires_at, ip_address, is_used) 
                             VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $email, $otp_hash, $expires_at, $ip);
        if (!$stmt->execute()) {
            throw new Exception("Failed to save OTP");
        }

        // 5. Catat permintaan OTP
        $stmt = $db->prepare("INSERT INTO otp_requests (email, ip_address, request_time) 
                             VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $email, $ip);
        if (!$stmt->execute()) {
            throw new Exception("Failed to log OTP request");
        }

        // 6. Kirim Email OTP (Menggunakan PHPMailer)
        $mail = new PHPMailer(true);
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
        $mail->Subject = 'Kode OTP Baru untuk Pengaturan Ulang Kata Sandi';
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
            throw new Exception("email_failed");
        }

        $db->commit();

        // Update session untuk reset percobaan verifikasi
        $_SESSION['verify_attempts'] = 0;
        $_SESSION['otp_last_sent'] = $current_time;

        header("Location: verify_otp_login?resent=1");
        exit;

    } catch (Exception $e) {
        $db->rollback();
        error_log("OTP Resend Error: " . $e->getMessage());
        
        // Redirect dengan pesan error spesifik
        $error = $e->getMessage();
        if ($error === "too_many_resend") {
            header("Location: verify_otp_login?error=too_many_resend");
        } else {
            header("Location: verify_otp_login?error=email_failed");
        }
        exit;
    }
}

// Handle OTP Verification (Jika user submit OTP)
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['resend_otp'])) {
    $otp = '';
    for ($i = 1; $i <= 6; $i++) {
        $otp .= strtoupper($_POST['otp'.$i] ?? '');
    }

    // Validasi format OTP
    if (strlen($otp) !== 6 || !preg_match('/^[A-Z0-9]{6}$/', $otp)) {
        $_SESSION['verify_attempts']++;
        header("Location: verify_otp_login?error=invalid_otp_format");
        exit;
    }

    $email = $_SESSION['otp_email'];
    $ip = $_SERVER['REMOTE_ADDR'];

    // Ambil OTP yang aktif dari database
    $stmt = $db->prepare("SELECT * FROM otp_codes WHERE email = ? AND is_used = 0 AND expires_at >= NOW()");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $otp_valid = false;
    $row_valid = null;
    while ($row = $result->fetch_assoc()) {
        if (password_verify($otp, $row['otp_hash'])) {
            $otp_valid = true;
            $row_valid = $row;
            break;
        }
    }

    if ($otp_valid) {
        // Tandai OTP sebagai digunakan
        $stmt = $db->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
        $stmt->bind_param("i", $row_valid['id']);
        $stmt->execute();
        
        // Set session untuk menandai OTP sudah diverifikasi
        $_SESSION['otp_verified'] = true;
        
        // Redirect ke halaman setelah verifikasi berhasil
        header("Location: process_otp_login");
        exit;
    } else {
        $_SESSION['verify_attempts']++;
        header("Location: verify_otp_login?error=invalid_otp");
        exit;
    }
}

// Set waktu terakhir OTP dikirim jika belum ada
if (!isset($_SESSION['otp_last_sent'])) {
    $_SESSION['otp_last_sent'] = time();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --error-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 10px;
            --box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .otp-container {
            background: white;
            max-width: 480px;
            width: 100%;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            margin-bottom: 20px;
        }
        
        .logo img {
            height: 50px;
            width: auto;
        }
        
        h2 {
            color: var(--dark-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        
        .otp-input-container {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }
        
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .error-message {
            color: var(--error-color);
            background: rgba(247, 37, 133, 0.1);
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: shake 0.5s;
        }
        
        .success-message {
            color: var(--success-color);
            background: rgba(76, 201, 240, 0.1);
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .email-display {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
            word-break: break-all;
        }
        
        button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .resend-link {
            margin-top: 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .resend-link button {
            background: none;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0;
            width: auto;
            display: inline;
        }
        
        .resend-link button:hover {
            text-decoration: underline;
            transform: none;
            box-shadow: none;
        }
        
        .resend-link button:disabled {
            color: #adb5bd;
            cursor: not-allowed;
            text-decoration: none;
        }
        
        .timer {
            color: var(--error-color);
            font-weight: 500;
            margin-top: 5px;
        }
        
        @media (max-width: 480px) {
            .otp-container {
                padding: 30px 20px;
            }
            
            .otp-input {
                width: 40px;
                height: 50px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="logo">
            <img src="gambar/Logo PT. ADS.png" alt="Logo PT. ADS" />
        </div>
        <h2>Verifikasi OTP</h2>
        <p class="subtitle">Masukkan 6 digit kode verifikasi yang telah dikirim ke email Anda</p>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php
                switch ($_GET['error']) {
                    case 'invalid_otp': echo "Kode OTP salah atau sudah kadaluarsa"; break;
                    case 'invalid_otp_format': echo "Format OTP tidak valid (harus 6 karakter)"; break;
                    case 'too_many_attempts': echo "Terlalu banyak percobaan. Silakan minta OTP baru."; break;
                    case 'session_expired': echo "Sesi telah berakhir. Silakan mulai kembali."; break;
                    case 'too_many_resend': echo "Anda sudah meminta OTP terlalu banyak. Silakan tunggu 1 jam."; break;
                    case 'email_failed': echo "Gagal mengirim email OTP. Silakan coba lagi."; break;
                    default: echo "Terjadi kesalahan";
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['resent'])): ?>
            <div class="success-message">
                Kode OTP baru telah dikirim ke email Anda!
            </div>
        <?php endif; ?>

        <?php
            function hideEmail($email) {
                     list($user, $domain) = explode('@', $email);
                     $length = strlen($user);

                    if ($length <= 2) {
                        $user_hidden = substr($user, 0, 1) . str_repeat('*', $length - 1);
                    } else {
                        $user_hidden = substr($user, 0, 1)
                                     . str_repeat('*', $length - 2)
                                     . substr($user, -1);
                    }

                    return $user_hidden . '@' . $domain;
                }
            ?>

        <div class="email-display">
            <?php echo htmlspecialchars(hideEmail($_SESSION['otp_email'])); ?>
        </div>

        <form method="post" autocomplete="off">
            <div class="otp-input-container">
                <input type="text" name="otp1" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required autofocus>
                <input type="text" name="otp2" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required>
                <input type="text" name="otp3" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required>
                <input type="text" name="otp4" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required>
                <input type="text" name="otp5" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required>
                <input type="text" name="otp6" class="otp-input" maxlength="1" pattern="[0-9A-Z]" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit">Verifikasi Sekarang</button>
        </form>
        
        <div class="resend-link">
            Tidak menerima OTP?
            <form method="post" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="resend_otp" value="1">
                <button type="submit" id="resend-btn" <?php echo (time() - $_SESSION['otp_last_sent'] < 60) ? 'disabled' : ''; ?>>Kirim ulang</button>
            </form>
            <?php if (time() - $_SESSION['otp_last_sent'] < 60): ?>
                <div class="timer">(Tersedia dalam <span id="countdown"><?php echo 60 - (time() - $_SESSION['otp_last_sent']); ?></span> detik)</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-focus dan auto-advance untuk OTP input
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.toUpperCase();
                
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').toUpperCase();
                const pasteChars = pasteData.split('').filter(c => /[0-9A-Z]/.test(c));
                
                for (let i = 0; i < Math.min(pasteChars.length, otpInputs.length); i++) {
                    otpInputs[i].value = pasteChars[i];
                }
                
                otpInputs[Math.min(pasteChars.length - 1, otpInputs.length - 1)].focus();
            });
        });

        // Timer untuk resend OTP
        const resendBtn = document.getElementById('resend-btn');
        const timerElement = document.getElementById('countdown');
        
        if (resendBtn && resendBtn.disabled && timerElement) {
            let timeLeft = <?php echo isset($_SESSION['otp_last_sent']) ? 60 - (time() - $_SESSION['otp_last_sent']) : 0; ?>;
            const countdown = setInterval(() => {
                timeLeft--;
                timerElement.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    resendBtn.disabled = false;
                    timerElement.parentElement.style.display = 'none';
                }
            }, 1000);
        }
    </script>
</body>
</html>