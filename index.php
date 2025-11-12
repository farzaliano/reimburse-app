<?php
session_start();
if (!isset($_SESSION['active_role']) || $_SESSION['active_role'] !== 'user') {
  header("Location: login.php");
  exit;
}

require 'koneksi.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ambil data dropdown dari tabel
$list_jabatan = query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
$list_jenis   = query("SELECT * FROM jenis ORDER BY nama_jenis ASC");

// Variabel Session
$username = $_SESSION['user'];
$nama     = $_SESSION['nama'] ?? $username;

// Jika Form Tambah Dikirim
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['cari'])) {
  $nip     = $_POST['nip'];
  $jabatan = $_POST['jabatan'];
  $jenis   = $_POST['jenis'];
  $tanggal = $_POST['tanggal'];
  $nominal = $_POST['nominal'];
  $statuss = 'menunggu';

// Simpan file bukti
$bukti = $_FILES['bukti']['name'];
$tmp_bukti = $_FILES['bukti']['tmp_name'];
$filename_bukti = time() . '_' . basename($bukti);
$path_bukti = __DIR__ . '/uploads/' . $filename_bukti;
if (!move_uploaded_file($tmp_bukti, $path_bukti)) {
  error_log("❌ Upload bukti gagal");
}

// Simpan file CSV (jika ada)
$csv = $_FILES['csv']['name'] ?? '';
$tmp_csv = $_FILES['csv']['tmp_name'] ?? '';
$filename_csv = '';
if ($csv) {
  $filename_csv = time() . '_' . basename($csv);
  $path_csv = 'uploads/' . $filename_csv;
  if (!move_uploaded_file($tmp_csv, $path_csv)) {
    error_log("❌ Upload CSV gagal");
  }
}

// Simpan ke database — hanya nama file
$stmt = $conn->prepare("INSERT INTO reimburse 
  (username, nama, nip, jabatan, jenis, tanggal, nominal, statuss, bukti, csv_file, notifikasi_admin) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
$stmt->bind_param("ssssssdsss", $username, $nama, $nip, $jabatan, $jenis, $tanggal, $nominal, $statuss, $filename_bukti, $filename_csv);


  if ($stmt->execute()) {
    try {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'liano.farza@gmail.com';
      $mail->Password = 'nbxs omss qmmk wubt';
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;
      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true,
          'cafile' => 'C:/xampp/cacert.pem'
        ]
      ];
      $mail->setFrom('liano.farza@gmail.com', 'ClaimEase Notification');
      $mail->addAddress('liano.farza@gmail.com', 'Admin');
      $mail->isHTML(true);
      $mail->Subject = 'Pengajuan Reimburse Baru';
      $mail->Body = "
        <h4>Notifikasi Pengajuan Reimburse</h4>
        <p><strong>Nama:</strong> $nama</p>
        <p><strong>Jabatan:</strong> $jabatan</p>
        <p><strong>Jenis:</strong> $jenis</p>
        <p><strong>Tanggal:</strong> $tanggal</p>
        <p><strong>Nominal:</strong> Rp " . number_format($nominal, 0, ',', '.') . "</p>
        <p>Status saat ini: <strong>Menunggu</strong></p>
        <p><a href='http://localhost/reimburse/admin.php'>Lihat Detail</a></p>
      ";
      $mail->send();
    } catch (Exception $e) {
      error_log("Gagal kirim email: {$mail->ErrorInfo}");
    }
    header("Location: index.php");
    exit;
  } else {
    echo "Gagal menyimpan data: " . $conn->error;
  }
}

// Pencarian
if (isset($_POST['cari'])) {
  $keyword = $_POST['keyword'];
  $reimburse = cari($keyword, $nama, 'menunggu');
} else {
  $reimburse = query("SELECT * FROM reimburse WHERE nama = '$nama' AND statuss = 'menunggu' ORDER BY tanggal DESC");
}

// Tanggal batas
$min_date = date('Y-m-d', strtotime('-1 month'));
$max_date = date('Y-m-d', strtotime('+1 month'));
?>







<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>ClaimEase</title>
  <link rel="icon" href="img/ppi.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background-color: rgb(239, 239, 239);
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 70px;
      height: 100vh;
      background-color: #185ABC;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: width 0.3s ease;
      overflow-x: hidden;
      z-index: 999;
    }

    .sidebar:hover {
      width: 200px;
      align-items: flex-start;
    }

    .logo {
      width: 50px;
      height: 50px;
      margin: 10px auto 30px;
      background-color: white;
      border-radius: 15px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .nav-item {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      transition: background 0.3s;
      white-space: nowrap;
      font-size: 16px;
    }

    .nav-item i {
      font-size: 20px;
    }

    .nav-item .label {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .sidebar:hover .nav-item .label {
      opacity: 1;
    }

    .nav-item:hover {
      background-color: rgb(239, 239, 239);
      color: #185ABC;

    }

    .bottom-section {
      margin-top: auto;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .sidebar .logo {
      width: 50px;
      height: 50px;
      margin-bottom: 40px;
      border-radius: 15px;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .sidebar .nav-icon {
      width: 50px;
      height: 50px;
      margin-bottom: 20px;
      border-radius: 15px;
      background-color: transparent;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 22px;
      transition: background 0.3s;
    }

    .sidebar .nav-icon:hover,
    .sidebar .nav-icon.active {
      background-color: white;
      color: #6C63FF;
    }

    .sidebar .bottom-section {
      margin-top: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sidebar .bottom-section img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .main-content {
      margin-left: 60px;
      padding: 40px;
      transition: margin-left 0.3s ease;
    }

    .sidebar:hover~.main-content {
      margin-left: 200px;
    }

    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .custom-table {
      background-color: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .custom-table-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }

    .custom-table-item:last-child {
      border-bottom: none;
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      font-weight: bold;
      padding: 10px 0;
      border-bottom: 2px solid #ccc;
      color: #185ABC;
    }

    .table-header div {
      flex: 1;
      text-align: center;
    }

    .table-header .jenis {
      text-align: left;
      flex: 2;
    }

    .status-disetujui {
      color: #0f5132;
      background-color: rgb(183, 255, 223);
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 14px;
    }

    .status-menunggu {
      color: #664d03;
      background-color: rgb(255, 230, 150);
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 14px;
    }

    .status-ditolak {
      color: #842029;
      background-color: rgb(252, 183, 189);
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 14px;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease-in;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      max-width: 90%;
      max-height: 90%;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
      position: relative;
      animation: slideUp 0.4s ease;
    }

    .modal-content img,
    .modal-content embed {
      max-width: 100%;
      max-height: 80vh;
      border-radius: 10px;
      display: block;
      margin: auto;
    }

    .close-btn {
      position: absolute;
      top: -10px;
      right: -10px;
      background: #6C63FF;
      color: white;
      border: none;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      font-size: 18px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .close-btn:hover {
      background: #534dc2;
      transform: rotate(90deg);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes slideUp {
      from {
        transform: translateY(40px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    #page-loader {
      position: fixed;
      top: 0;
      left: 0;
      background: #ffffff;
      width: 100%;
      height: 100%;
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      opacity: 1;
      transition: opacity 0.5s ease;
    }

    #page-loader.hide {
      opacity: 0;
      pointer-events: none;
    }

    .ripple-loader {
      position: relative;
      width: 80px;
      height: 80px;
    }

    .ripple-loader div {
      position: absolute;
      border: 4px solid #6C63FF;
      opacity: 1;
      border-radius: 50%;
      animation: ripple 1.8s cubic-bezier(0, 0.2, 0.8, 1) infinite;
    }

    .ripple-loader div:nth-child(2) {
      animation-delay: -0.9s;
    }

    @keyframes ripple {
      0% {
        top: 36px;
        left: 36px;
        width: 0;
        height: 0;
        opacity: 1;
      }

      100% {
        top: 0px;
        left: 0px;
        width: 72px;
        height: 72px;
        opacity: 0;
      }
    }

    h2 {
      font-size: 40px;
      font-weight: bold;
      color: #185ABC;
    }

    .welcome-box {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(to right, #185ABC, #4D8BFF);
      color: white;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .welcome-text h4 {
      font-size: 24px;
      margin: 0;
      font-weight: 600;
    }

    .welcome-text p {
      margin: 5px 0 10px;
      font-size: 16px;
      color: #e0e7ff;
    }

    .welcome-text a {
      color: #ffd966;
      text-decoration: none;
      font-weight: bold;
      font-size: 14px;
    }

    .welcome-text a:hover {
      text-decoration: underline;
    }

    .welcome-image img {
      max-height: 100px;
    }

    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .logo-text {
      margin: 0;
      font-size: 28px;
      font-weight: bold;
      display: flex;
      align-items: center;
    }

    .search-form {
      display: flex;
      flex: 1;
      max-width: 500px;
      gap: 8px;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <div class="logo">
      <img src="img/pi.jpeg" alt="Logo" width="30">
    </div>

    <a href="index.php" class="nav-item">
      <i class="bi bi-house-door-fill"></i>
      <span class="label">Beranda</span>
    </a>
    <br>
    <a href="history.php" class="nav-item">
      <i class="bi bi-clock-history"></i>
      <span class="label">Riwayat</span>
    </a>

    <div class="bottom-section">
      <a href="logout.php" class="nav-item">
        <i class="bi bi-box-arrow-right"></i>
        <span class="label">Logout</span>
      </a>
    </div>
  </div>



  <div class="main-content">
    <div class="header-bar">
      <h1 class="logo-text">
        <span style="color: #185ABC;">Claim</span><span style="color: #F7941D;">Ease</span>
      </h1>
      <form class="search-form" method="post">
        <input class="form-control me-2" type="text" name="keyword" placeholder="Search" autocomplete="off">
        <button class="btn btn-outline-primary" type="submit" name="cari">Search</button>
      </form>
    </div>

    <?php if (isset($_SESSION['user'])): ?>
      <div class="welcome-box">
        <div class="welcome-text">
          <h4>Hallo <?= htmlspecialchars($_SESSION['user']) ?> ! 👋</h4>
          <p>Kamu memiliki permintaan reimburse baru. Ayo mulai hari ini!</p>
        </div>
      </div>
    <?php endif; ?>




    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" enctype="multipart/form-data" action="">
            <div class="modal-header">
              <h5 class="modal-title" id="modalTambahLabel" style="color: #185ABC;">Tambah <span style="color: #F7941D;">Reimburse</span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
              <input type="hidden" name="nama" value="<?= isset($_SESSION['nama']) ? $_SESSION['nama'] : '' ?>">
              <div class="mb-3">
                <label for="nip" class="form-label">NIP</label>
                <input type="text" name="nip" id="nip" class="form-control" autocomplete="off" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Jabatan</label>
               <select name="jabatan" class="form-select" required>
                <option value="">Pilih jabatan...</option>
                <?php foreach ($list_jabatan as $j) : ?>
                  <option value="<?= htmlspecialchars($j['nama_jabatan']) ?>"><?= htmlspecialchars($j['nama_jabatan']) ?></option>
                <?php endforeach; ?>
              </select>

              </div>
              <input type="hidden" name="username" value="<?= $_SESSION['user'] ?>">

              <div class="mb-3">
                <label class="form-label">Jenis</label>
               <select name="jenis" class="form-select" required>
                <option value="">Pilih jenis...</option>
                <?php foreach ($list_jenis as $j) : ?>
                  <option value="<?= htmlspecialchars($j['nama_jenis']) ?>"><?= htmlspecialchars($j['nama_jenis']) ?></option>
                <?php endforeach; ?>
              </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required min="<?= $min_date ?>" max="<?= $max_date ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Nominal</label>
                <input type="number" name="nominal" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Upload Bukti</label>
                <input type="file" name="bukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Upload CSV (Opsional)</label>
                <input type="file" name="csv" class="form-control" accept=".csv">
                <br>
                <a class="csv-link" href="template.csv">Download Template CSV</a>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">Simpan</button>
              <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>



    <div class="custom-table">
      <div class="table-header">
        <div class="jenis">Nama</div>
        <div>NIP</div>
        <div>Jabatan</div>
        <div>Jenis</div>
        <div>Tanggal</div>
        <div>Nominal</div>
        <div>Status</div>
        <div>Bukti</div>
        <div>CSV</div>
      </div>

      <?php foreach ($reimburse as $row) :
        $status = strtolower($row['statuss']);
        $statusClass = match ($status) {
          'disetujui' => 'status-disetujui',
          'menunggu' => 'status-menunggu',
          'ditolak'   => 'status-ditolak',
          default     => '',
        };
      ?>
        <div class="custom-table-item">
          <div class="item-left" style="flex: 2;">
            <strong><?= htmlspecialchars($row['nama']) ?></strong>
          </div>
          <div style="flex: 1; text-align: center;">
            <?= htmlspecialchars($row['nip']) ?>
          </div>
          <div style="flex: 1; text-align: center;">
            <?= htmlspecialchars($row['jabatan']) ?>
          </div>
          <div style="flex: 1; text-align: center;">
            <?= htmlspecialchars($row['jenis']) ?>
          </div>
          <div style="flex: 1; text-align: center;">
            <?= htmlspecialchars($row['tanggal']) ?>
          </div>
          <div style="flex: 1; text-align: center;">
            Rp<?= number_format($row['nominal'], 0, ',', '.') ?>
          </div>
          <div style="flex: 1; text-align: center;">
            <div class="<?= $statusClass ?>">
              <?= htmlspecialchars($row['statuss']) ?>
            </div>
          </div>
          <div style="flex: 1; text-align: center;">
         <?php if ($row['bukti']) : ?>
  <a href="javascript:void(0);" onclick="openModal('uploads/<?= htmlspecialchars($row['bukti']) ?>')" style="color: #F7941D;">Lihat Bukti</a>
<?php else : ?>
  Tidak ada
<?php endif; ?>
          </div>
          <div style="flex: 1; text-align: center;">
            <?php if (!empty($row['csv_file'])) : ?>
              <a href="uploads/<?= htmlspecialchars($row['csv_file']) ?>" target="_blank" style="color: #F7941D;">Lihat CSV</a>
            <?php else : ?>
              Tidak ada
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tombol Tambah-->
  <div class="text-end mb-3" style="margin-right: 40px;">
    <button class="btn" style="background-color: #F7941D; color: white;" data-bs-toggle="modal" data-bs-target="#modalTambah">
      <i class="bi bi-plus-circle"></i> Tambah Reimburse
    </button>
  </div>

  <!-- Modal Untuk Lihat Bukti -->
  <div id="buktiModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal()">&times;</button>
      <div id="buktiContainer"></div>
    </div>
  </div>

  <!-- Loading -->
  <div id="page-loader">
    <div class="ripple-loader">
      <div></div>
      <div></div>
    </div>
  </div>

  <script>
    window.addEventListener("load", () => {
      document.getElementById("page-loader").classList.add("hide");
    });

    document.querySelectorAll('a[href]').forEach(link => {
      if (link.getAttribute('href').startsWith('#') || link.getAttribute('href').startsWith('javascript')) return;
      link.addEventListener('click', e => {
        e.preventDefault();
        const target = link.href;
        document.getElementById("page-loader").classList.remove("hide");
        setTimeout(() => window.location.href = target, 1000);
      });
    });

    function openModal(fileUrl) {
      const container = document.getElementById('buktiContainer');
      const ext = fileUrl.split('.').pop().toLowerCase();
      if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        container.innerHTML = `<img src="${fileUrl}" alt="Bukti">`;
      } else if (ext === 'pdf') {
        container.innerHTML = `<embed src="${fileUrl}" type="application/pdf" width="100%" height="500px">`;
      } else {
        container.innerHTML = `<p style="color:red; text-align:center;">Format tidak didukung.</p>`;
      }
      document.getElementById('buktiModal').style.display = 'flex';
    }

    function closeModal() {
      document.getElementById('buktiModal').style.display = 'none';
      document.getElementById('buktiContainer').innerHTML = '';
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>