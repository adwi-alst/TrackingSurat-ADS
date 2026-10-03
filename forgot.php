<?php
        session_start();
        if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - PT. ADS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="gambar/icon.png" type="image/png">
  <style>
    :root {
      --primary-color: #4361ee;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      padding: 15px 20px;
      background-color: rgba(255, 255, 255, 0.9);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .logo {
      height: 50px;
      width: auto;
    }

    .logo img {
      height: 100%;
      width: auto;
      object-fit: contain;
    }

    .card-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .card {
      background-color: rgba(255, 255, 255, 0.95);
      border: none;
      border-radius: 12px;
      width: 100%;
      max-width: 450px;
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h4 {
      color: var(--primary-color);
      text-align: center;
      margin-bottom: 25px;
      font-weight: 600;
    }

    /* Error Message Styles */
    .alert-message {
      max-width: 450px;
      margin: 20px auto;
      border-radius: 10px;
      padding: 15px 20px;
      text-align: center;
      animation: fadeInDown 0.5s ease-out;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .alert-limit {
      background-color: #fff3cd;
      color: #856404;
      border-left: 5px solid #ffc107;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border-left: 5px solid #dc3545;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border-left: 5px solid #28a745;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .btn-primary {
      background-color: var(--primary-color);
      border: none;
      padding: 12px;
      font-weight: 500;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #3a56d4;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }

    .form-control {
      padding: 12px 15px;
      border-radius: 8px;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <div class="logo">
          <img src="gambar/Logo PT. ADS.png" alt="Logo PT. ADS" />
        </div>
      </a>
    </div>
  </nav>

  <?php
  // Handle error messages
  if (isset($_GET['error'])) {
    $errorMessages = [
      'session_expired' => [
        'type' => 'error',
        'title' => 'Sesi Berakhir',
        'message' => 'Sesi verifikasi telah berakhir. Silakan mulai kembali proses.'
      ],
      'too_many_attempts' => [
        'type' => 'error',
        'title' => 'Terlalu Banyak Percobaan',
        'message' => 'Anda telah melebihi batas percobaan verifikasi. Silakan coba lagi nanti.'
      ],
      'invalid_email' => [
        'type' => 'error',
        'title' => 'Email Tidak Valid',
        'message' => 'Format email yang Anda masukkan tidak valid.'
      ],
      'email_not_registered' => [
        'type' => 'error',
        'title' => 'Email Tidak Dikenali',
        'message' => 'Email yang Anda masukkan tidak terdaftar pada akun.'
      ],
      'otp_failed' => [
        'type' => 'error',
        'title' => 'Gagal Mengirim OTP',
        'message' => 'Terjadi kesalahan saat mengirim kode OTP. Silakan coba lagi.'
      ],
      'email_limit_exceeded' => [
        'type' => 'limit',
        'title' => 'Batas Harian Terlampaui',
        'message' => 'Anda sudah meminta OTP 5 kali hari ini. Silakan coba lagi besok.'
      ],
      'ip_limit_exceeded' => [
        'type' => 'limit',
        'title' => 'Batas IP Terlampaui',
        'message' => 'Terlalu banyak permintaan dari alamat IP ini. Silakan hubungi admin jika ini kesalahan.'
      ],
      'request_too_soon' => [
        'type' => 'limit',
        'title' => 'Terlalu Cepat',
        'message' => 'Harap tunggu 30 detik sebelum meminta OTP kembali.'
      ]
    ];

    if (array_key_exists($_GET['error'], $errorMessages)) {
      $error = $errorMessages[$_GET['error']];
      $alertClass = $error['type'] === 'limit' ? 'alert-limit' : 'alert-error';
      echo '
      <div class="alert-message '.$alertClass.'">
        <strong>'.$error['title'].'</strong>
        <p class="mb-0">'.$error['message'].'</p>
      </div>';
    }
  }
  ?>

  <div class="card-container">
    <div class="card p-4">
      <h4 class="text-center mb-4">Masukkan Email</h4>
      <form action="send_otp_login" method="POST" autocomplete="off">
        <div class="mb-3">
          <label for="email" class="form-label">Alamat Email</label>
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input 
            type="email" 
            class="form-control" 
            id="email" 
            name="email" 
            required 
            placeholder="contoh@email.com"
            <?php if (isset($_GET['email'])) echo 'value="'.htmlspecialchars($_GET['email']).'"'; ?>
          >
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">
            Kirim Kode OTP
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Auto focus ke field email jika ada error
    <?php if (isset($_GET['error'])): ?>
      document.getElementById('email').focus();
    <?php endif; ?>
  </script>
</body>
</html>