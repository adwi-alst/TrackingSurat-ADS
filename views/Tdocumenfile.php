<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Upload Surat</title>
   <link rel="stylesheet" href="viewCss/tambah.css">
   <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="icon" href="gambar/icon.png" type="image/png">
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <img src="gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" class="sidebar-logo">
  </div>
  <hr class="sidebar-divider" />
  <ul class="sidebar-menu">
    <li class="sidebar-menu-tambah"><i class="fas fa-plus-circle"></i>TAMBAH SURAT</li>
    <li class="sidebar-menu-back">
      <a href="overviews"><i class="fas fa-arrow-left"></i>BACK</a>
    </li>
  </ul>
</aside>

<div class="container">
        <h2>Form Upload Surat</h2>
    <form class="form-grid" action="tambah_surat" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="tambah_surat">
            <div class="form-pair">
            <div class="form-group">
            <label for="jenis_surat">Jenis Surat</label>
            <select name="jenis_surat" id="jenis_surat" required>
                <option value="" disabled selected>Pilih jenis surat</option>
                <option value="masuk">Masuk</option>
                <option value="keluar">Keluar</option>
                <option value="undangan">Undangan</option>
                <option value="pengumuman">Pengumuman</option>
            </select>
            </div>
    <div class="form-pair">
    <div class="form-group">      
      <label for="no_surat">NO Surat</label>
      <input type="text" id="no_surat" name="no_surat" placeholder="Masukkan Nomor Surat" required>
      </div>
    </div>
    <div class="form-pair">
    <div class="form-group">
            <label for="devisi">Divisi</label>
            <select name="devisi" id="devisi" required>
                <option value="" disabled selected>Pilih Divisi</option>
                <option value="Keuangan">Keuangan</option>
                <option value="General Affairs (GA)">General Affairs (GA)</option>
                <option value="Personalia">Personalia</option>
                <option value="Corporate Social Responsibility (CSR)">Corporate Social Responsibility (CSR)</option>
                <option value="Satuan Pengawas Internal">Satuan Pengawas Internal</option>
                <option value="Teknik dan Business Development">Teknik dan Business Development</option>
                <option value="-">-Lainnya-</option>
            </select>
            </div>
    <div class="form-group"> 
      <label for="subjek">Judul</label>
      <input type="text" id="subjek" name="subjek" placeholder="Masukkan Judul Surat" required>
    </div>
    </div>
    <div class="form-pair">
    <div class="form-group">
      <label for="pengirim">Pengirim</label>
      <input type="text" id="pengirim" name="pengirim" placeholder="Masukkan Nama Pengirim" required>
    </div>
    <div class="form-group">
      <label for="tujuan">Tujuan</label>
      <input type="text" id="tujuan" name="tujuan" placeholder="Masukkan Tujuan Surat" required>
    </div>
    </div>
    <div class="form-pair">
    <div class="form-group">
      <label for="tanggal">Pilih Tanggal</label>
      <input type="date" id="tanggal" name="tanggal" required>
    </div>
    <div class="form-group">
      <label for="file">File Surat (PDF/DOCX)</label>
      <input type="file" id="file" name="file" accept=".pdf,.doc,.docx" required>
    </div>
    </div>
    <div class="form-group">
      <input type="submit" value="Upload Surat">
    </div>
    </form>
  </div>

</body>
</html>
