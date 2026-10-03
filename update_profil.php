<?php
session_start();
require_once 'config/database.php';

// Verifikasi OTP dan data user tersedia
if (
    !isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] ||
    !isset($_SESSION['user_id_temp']) || !isset($_SESSION['user_email'])
) {
    header("Location: forgot?error=unauthorized");
    exit;
}

// Koneksi database
$db = connect_db();

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF verification failed.");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = $_SESSION['user_email'];
    $id = $_SESSION['user_id_temp'];

    // Validasi input
    $error = '';
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update user di database
        $stmt = $db->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $hashedPassword, $id);

        if ($stmt->execute()) {
            // Bersihkan session sementara
            unset($_SESSION['otp_verified']);
            unset($_SESSION['user_email']);
            unset($_SESSION['user_id_temp']);

            // Buat session login
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['last_activity'] = time();

            // Hapus token CSRF
            unset($_SESSION['csrf_token']);

            // Redirect ke halaman utama
            header("Location: verify?update=success");
            exit;
        } else {
            $error = "Gagal mengupdate profil.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" href="gambar/icon.png" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Perbarui Profil - Sistem Kami</title>
    <!-- Tambahkan Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tambahkan Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #4cc9f0;
            --error-color: #f72585;
            --success-color: #4ade80;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
            color: var(--dark-color);
        }
        
        .update-container {
            background: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.6s cubic-bezier(0.39, 0.575, 0.565, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo img {
            height: 50px;
            width: auto;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-color);
            font-size: 0.95rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1rem;
            padding-right: 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
            background-color: white;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.1rem;
            transition: var(--transition);
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }
        
        .btn i {
            font-size: 1.1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .alert-error {
            color: var(--error-color);
            background-color: rgba(247, 37, 133, 0.1);
            border-left: 4px solid var(--error-color);
        }
        
        .email-display {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }
        
        .email-display i {
            font-size: 1.1rem;
        }
        
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background: var(--error-color);
            transition: var(--transition);
        }
        
        @media (max-width: 480px) {
            .update-container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="update-container">
        <div class="logo">
            <img src="gambar/Logo PT. ADS.png" alt="Logo Perusahaan">
        </div>
        
        <h2>Perbarui Profil Anda</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
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
            <i class="fas fa-envelope"></i>
            <?php echo htmlspecialchars(hideEmail($_SESSION['user_email'])); ?>
        </div>

        <form method="POST" id="updateForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username Baru</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-control" required 
                           minlength="4" maxlength="20" pattern="[a-zA-Z0-9]+"
                           title="Hanya huruf dan angka, minimal 4 karakter">
                    <i class="fas fa-user-edit" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-color);"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password Baru</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-control" required
                           minlength="6" title="Minimal 6 karakter">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });

        // Password Strength Indicator
        password.addEventListener('input', function() {
            const strengthMeter = document.getElementById('strengthMeter');
            const strength = calculatePasswordStrength(this.value);
            
            strengthMeter.style.width = strength.percentage + '%';
            strengthMeter.style.backgroundColor = strength.color;
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length > 0) strength += 10;
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 20;
            
            // Complexity checks
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Cap at 100
            strength = Math.min(strength, 100);
            
            // Determine color
            let color;
            if (strength < 30) color = '#f72585'; // Weak (red)
            else if (strength < 70) color = '#f8961e'; // Medium (orange)
            else color = '#4ade80'; // Strong (green)
            
            return { percentage: strength, color };
        }

        // Form Validation
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (username.length < 4) {
                e.preventDefault();
                showAlert('Username minimal 4 karakter', 'error');
                return;
            }
            
            if (!/^[a-zA-Z0-9]+$/.test(username)) {
                e.preventDefault();
                showAlert('Username hanya boleh mengandung huruf dan angka', 'error');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password minimal 6 karakter', 'error');
                return;
            }
        });

        function showAlert(message, type) {
            // Remove existing alerts
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            
            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            
            // Insert after h2
            const h2 = document.querySelector('h2');
            h2.parentNode.insertBefore(alertDiv, h2.nextSibling);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>