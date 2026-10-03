<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tracking Surat - PT. ADS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="gambar/icon.png" type="image/png">
  <style>
:root {
  --primary-color: #4361ee;
  --primary-dark: rgb(32, 59, 180);
  --border-color: #e9ecef;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
  font-family: 'Montserrat', sans-serif;
}

body {
  background: url('gambar/login.jpeg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  color: #fff;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.header-container {
  position: relative;
  width: 100%;
}

.logo {
  position: relative;
  display: inline-block;
  margin: 20px 0 0 30px;
  z-index: 100;
  transition: all 0.3s ease;
}

.logo img {
  height: 50px;
  width: auto;
  transition: transform 0.3s ease;
}

.logo img:hover {
  transform: scale(1.05);
}

main {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 20px;
  position: relative;
  margin-top: -30px;
}

.hero-text {
  text-align: center;
  margin-bottom: 15px; /* Reduced margin */
  width: 100%;
  position: relative;
  z-index: 1;
}

.hero-main-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  margin-bottom: 5px; /* Added for better spacing */
}

.hero-main {
  background-color: rgb(4, 160, 160);
  color: white;
  font-weight: bold;
  font-size: 2rem;
  padding: 12px 30px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
  z-index: 2;
  line-height: 1.2; /* Better text alignment */
}

.hero-sub {
  background-color: rgb(4, 160, 160);
  color: white;
  font-weight: bold;
  font-size: 1rem;
  padding: 10px 35px;
  border-radius: 6px;
  margin-top: -1px; /* Adjust this value to make it closer */
  z-index: 1;
  position: relative;
  box-shadow: 0 3px 6px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
  width: fit-content;
}

/* Hover Effects Only */
.hero-main-container:hover .hero-main {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.25);
}

.hero-main-container:hover .hero-sub {
  transform: translateY(2px);
}

.login-container {
  width: 100%;
  max-width: 420px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  position: relative;
}

.card {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 15px;
  padding: 40px 30px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  transform: translateY(0);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin-top: 20px;
  color: white;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}

.form-group {
  margin-bottom: 25px;
  position: relative;
}

.form-group input {
  width: 100%;
  padding: 14px 16px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 15px;
  transition: all 0.3s ease;
  background-color: rgb(238, 246, 255);
  color: black;
  border-color: rgba(255, 255, 255, 0.3);
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
}

.form-placeholder {
  position: absolute;
  left: 15px;
  top: 15px;
  color: rgba(0, 0, 0, 0.8);
  font-size: 15px;
  transition: all 0.3s ease;
  pointer-events: none;
  background-color: transparent;
  padding: 0 5px;
}

.form-group input:focus ~ .form-placeholder,
.form-group input:valid ~ .form-placeholder {
  top: -10px;
  left: 10px;
  font-size: 12px;
  font-weight: 600;
  background-color: white;
  color: black;
  border-radius: 5px;
}

.form-group input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-group i {
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 18px;
  color: #4361ee;
  transition: color 0.3s ease;
}

.form-group i:hover {
  color: #4361ee;
}

.form-buttons {
  margin-top: 10px;
}

.form-buttons button {
  width: 100%;
  padding: 14px;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.form-buttons button:hover {
  background-color: var(--primary-dark);
  transform: translateY(-2px);
}

.forgot-link {
  display: block;
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
}

.forgot-link a {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
  opacity: 0.8;
}

.forgot-link a:hover {
  opacity: 1;
  text-decoration: underline;
}

footer {
  padding: 15px;
  text-align: center;
  font-size: 14px;
  color: white;
  background-color: rgba(0, 0, 0, 0.7);
  flex-shrink: 0;
}

/* Responsive adjustments */
@media (max-width: 1000px) {
  .hero-main {
    font-size: 1.8rem;
    padding: 10px 25px;
  }
  
  .hero-sub {
    font-size: 0.9rem;
    padding: 10px 30px;
    margin-top: -1px; /* More compact on tablets */
  }
  
  .card {
    padding: 30px 20px;
  }
  
  .logo {
    margin: 15px 0 0 15px;
  }
  
  .logo img {
    height: 40px;
  }
}

@media (max-width: 480px) {
   .hero-main {
    font-size: 1.5rem;
    padding: 8px 20px;
  }
  
  .hero-sub {
    font-size: 0.8rem;
    padding: 10px 30px;
    margin-top: -1px; /* Tightest on mobile */
  }
  
  .card {
    padding: 25px 15px;
  }
  
  .form-group input {
    padding: 12px 14px;
  }
  
  .form-buttons button {
    padding: 12px;
  }
}

@media (max-height: 600px) and (orientation: landscape) {
  .header-container {
    position: relative;
    height: auto;
  }
  
  .logo {
    position: relative;
    margin: 10px 0 0 10px;
  }
  
  main {
    padding-top: 0;
    padding-bottom: 20px;
    margin-top: 0;
  }

  .login-container {
    margin-top: 20px;
  }
  
  .hero-text {
    margin-bottom: 20px;
  }
  
  .hero-main {
    font-size: 1.8rem;
    padding: 12px 25px;
  }
  
  .hero-sub {
    font-size: 0.9rem;
    padding: 10px 30px;
    margin-top: -1px;
  }
  
  .card {
    padding: 20px 15px;
    margin-top: 10px;
  }
}
  </style>
</head>
<body>
  <div class="header-container">
    <!-- Logo -->
    <div class="logo">
      <img src="gambar/LogoPT.ADS.png" alt="Logo PT. ADS" />
    </div>
  </div>

  <main>
    <?php if (isset($_GET['error'])): ?>
      <script>
        <?php if ($_GET['error'] == 'empty'): ?>
          alert('⚠️ Username dan Password wajib diisi!');
        <?php elseif ($_GET['error'] == 'invalid_credentials'): ?>
          alert('❌ Username atau Password salah!!');
        <?php endif; ?>
      </script>
    <?php endif; ?>

    <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 'true'): ?>
      <script>
        alert('⏰ Sesi Anda telah berakhir. Silakan login kembali.');
      </script>
    <?php endif; ?>

    <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
      <script>
        alert("✅ Update profile berhasil! Silakan login dengan username dan password baru Anda.");
      </script>
    <?php endif; ?>

    <div class="hero-text">
  <div class="hero-main-container">
    <div class="hero-main">Tracking Surat ADS</div>
    <div class="hero-sub">Disini</div>
  </div>
</div>

    <div class="login-container">
      <div class="card">
        <form action="login" method="POST">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div class="form-group">
             <input type="text" id="username" name="username" required />
             <span class="form-placeholder">Username</span>
             <i class='bx bxs-user'></i>
          </div>

          <div class="form-group">
            <input type="password" id="password" name="password" required />
            <span class="form-placeholder">Password</span>
            <i class='bx bx-show' id="togglePassword"></i>
          </div>

          <div class="form-buttons">
            <button type="submit">LOGIN</button>
          </div>
          
          <div class="forgot-link">
            <a href="forgot">Lupa username atau password?</a>
          </div>
        </form>
      </div>
    </div>
  </main>

  <footer>
    <p>PKL SISKOM UNUGIRI PT. ADS &#169; 2025</p>
  </footer>

  <script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      this.classList.toggle('bx-show');
      this.classList.toggle('bx-hide');
    });
 
  // Handle floating labels
  const inputs = document.querySelectorAll('.form-group input');
  
  inputs.forEach(input => {
    // Check on page load
    if(input.value) {
      input.nextElementSibling.classList.add('floating');
    }
    
    // Check on input
    input.addEventListener('input', function() {
      if(this.value) {
        this.nextElementSibling.classList.add('floating');
      } else {
        this.nextElementSibling.classList.remove('floating');
      }
    });
  });
</script>
</body>
</html>