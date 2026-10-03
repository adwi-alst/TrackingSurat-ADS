<?php
// Query untuk menampilkan data dengan ID = 1
$query_1 = "SELECT * FROM users WHERE id = 1";
$hasil_1 = $db->query($query_1);

// Query untuk menampilkan data selain ID = 1
$query_2 = "SELECT * FROM users WHERE id != 1";
$hasil_2 = $db->query($query_2);

$status = $_GET['status'] ?? '';

?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Form Register</title>
     <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="RegisterCss/formRegister.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <img src="gambar/Logo PT. ADS.png" alt="Logo PT. ADS" class="sidebar-logo">
  </div>
  <hr class="sidebar-divider" />
  <ul class="sidebar-menu">
    <li class="sidebar-menu-register"><i class="fa fa-user-plus"></i>REGISTER & EDIT</li>
    <li class="sidebar-menu-back">
      <a href="overviews"><i class="fas fa-arrow-left"></i>BACK</a>
    </li>
  </ul>
</aside>

<div class="container">
<div class="intro-section">
    <h2>Username & Password Admin</h2>
      <table class="features-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($hasil_1->num_rows > 0) {
            while($row = $hasil_1->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['username']) . "</td>";
              echo "<td>" . htmlspecialchars($row['password']) . "</td>";
              echo "<td>
                <div class='action-buttons'>
                 <button type='button' class='edit-button' onclick=\"openEditModal('" . htmlspecialchars($row['id']) . "', '" . htmlspecialchars($row['username']) . "', '" . htmlspecialchars($row['password']) . "')\">Edit</button>
               </div>
              </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='3'>Tidak ada data</td></tr>";
          }
          ?>
        </tbody>
      </table>
      <br><br>
      
      <!-- Tabel untuk data selain ID = 1 -->
      <h2>Username & Password User</h2>
      <table class="features-table2">
        <thead>
          <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($hasil_2->num_rows > 0) {
            while($row = $hasil_2->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['username']) . "</td>";
              echo "<td>" . htmlspecialchars($row['password']) . "</td>";
               echo "<td>

               <div class='action-buttons'>
                  <button type='button' class='edit-button' onclick=\"openEditModal('" . htmlspecialchars($row['id']) . "', '" . htmlspecialchars($row['username']) . "', '" . htmlspecialchars($row['password']) . "')\">Edit</button>
                <form action='index.php' method='POST' onsubmit='return confirmDeletion();'>
                  <input type='hidden' name='action' value='delete'>
                  <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                  <button type='submit' class='delete-button'>Hapus</button>
                </form>
              </div>
                </td>";
              }
          } else {
            echo "<tr><td colspan='3'>Tidak ada data</td></tr>";
          }
          ?>
          
        </tbody>
      </table>
      </div>

      <div class="register-container">
  <h1 class="register-title">REGISTER</h1>
  <form method="POST" action="register"> 
    <div class="form-group">
      <input type="text" id="username" name="username" placeholder="Username" required />
      <i class='bx bxs-user input-icon'></i>
    </div>
    <div class="form-group">
      <input type="password" id="password" name="password" placeholder="Password" required />
      <i class='bx bx-show input-icon' id="togglePassword"></i>
    </div>
    <button type="submit" class="register-button">DAFTAR</button>
  </form>       
</div>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      this.classList.toggle('bx-show');
      this.classList.toggle('bx-hide');
    });
  </script>
</div>
<script>
  function confirmDeletion() {
    return confirm('Yakin ingin menghapus data ini?');
  }

  function openEditModal(id, oldUsername, oldPassword) {
    document.getElementById('editId').value = id;
    document.getElementById('editUsername').value = '';
    document.getElementById('editPassword').value = '';
    document.getElementById('editUsername').placeholder = 'Masukkan username baru';
    document.getElementById('editPassword').placeholder = 'Masukkan password baru';
    document.getElementById('oldUsernameInfo').innerText = 'Edit Untuk Akun: ' + oldUsername;
    document.getElementById('editModalOverlay').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';

    if (id == 1) {
        document.getElementById('emailFormGroup').style.display = 'block';
        document.getElementById('editEmail').required = true;
    } else {
        document.getElementById('emailFormGroup').style.display = 'none';
        document.getElementById('editEmail').required = false;
        document.getElementById('editEmail').value = '';  // bersihkan input email kalau disembunyikan
    }

    document.getElementById('editModalOverlay').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';
  }
  function closeEditModal() {
    document.getElementById('editModalOverlay').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
  }

  function confirmDeletion() {
    return confirm('Yakin ingin menghapus akun ini?');
  }

window.onload = function() {
  var notif = document.getElementById('notifPopup');
  if (notif) {
    setTimeout(function() {
      notif.style.opacity = '0';
      setTimeout(function() {
        notif.parentNode.removeChild(notif);
      }, 500);
    }, 2500); // 2.5 detik
  }
};
</script>
<!-- Modal Overlay -->
<div id="editModalOverlay" class="modal-overlay" style="display:none;"></div>

<!-- Edit Modal Card -->
<div id="editModal" class="modal" style="display:none;">
  <div class="modal-content" style="position:relative;">
          <span class="close" onclick="closeEditModal()">&times;</span>
          <h2>Edit Akun</h2>
      <div id="oldUsernameInfo" style="margin-bottom: 16px; color: #555;"></div>
    <form id="editForm" method="POST" action="index.php">
  <input type="hidden" name="action" value="update">
  <input type="hidden" name="id" id="editId">
  <div class="form-group">
    <label>Username</label>
    <input type="text" name="username" id="editUsername" placeholder="Masukkan username baru" required>
  </div>
  <div class="form-group">
    <label>Password</label>
    <input type="password" name="password" id="editPassword" placeholder="Masukkan password baru" required>
    <div style="margin-top: 5px;">
        <input type="checkbox" id="showPasswordCheckbox" style="width: 14px; height: 14px;">
        <label for="showPasswordCheckbox" style="font-size: 0.95em; cursor:pointer;">Lihat Password</label>
    </div>
  </div>

   <div class="form-group" id="emailFormGroup">
    <label>Email</label>
    <input type="email" name="email" id="editEmail" placeholder="Masukkan Alamat Email" required>
  </div>

  <button type="submit" class="register-button">Update</button>
</form>
<script>
document.getElementById('showPasswordCheckbox').addEventListener('change', function() {
    const passwordInput = document.getElementById('editPassword');
    if (this.checked) {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
});
</script>
  </div>
</div>
<?php if ($status == 'success'): ?>
<script>
    Swal.fire('Sukses', 'Update berhasil!', 'success');
</script>
<?php elseif ($status == 'error'): ?>
<script>
    Swal.fire('Gagal', 'Update gagal!', 'error');
</script>
<?php endif; ?>

<?php if ($status == 'add_success'): ?>
<script>
    Swal.fire('Sukses', 'Berhasil menambahkan Akun!', 'success');
</script>
<?php elseif ($status == 'add_failed'): ?>
<script>
    Swal.fire('Gagal', 'Gagal untuk menambahkan Akun!', 'error');
</script>
<?php endif; ?>


<?php if ($status == 'delete_success'): ?>
<script>
    Swal.fire('Sukses', 'Akun Berhasil di hapus!', 'success');
</script>
<?php elseif ($status == 'add_failed'): ?>
<script>
    Swal.fire('Gagal', 'Gagal untuk menghapus Akun!', 'error');
</script>
<?php endif; ?>

</body>
</html>
