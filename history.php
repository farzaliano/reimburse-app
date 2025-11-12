<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];
$role = $_SESSION['active_role'] ?? 'user';
$roles = $_SESSION['roles'] ?? [];
$nama = $_SESSION['nama'] ?? $username;

$keyword = $_GET['keyword'] ?? '';
$status_filter = $_GET['statuss'] ?? '';

$safe_username = mysqli_real_escape_string($conn, $username);
$safe_nama = mysqli_real_escape_string($conn, $nama);

$query_base = "SELECT * FROM reimburse WHERE statuss IN ('disetujui', 'ditolak')";

// Jika bukan admin, tampilkan hanya data milik user tsb
if ($role !== 'admin') {
    $query_base .= " AND username = '$safe_username'";
}

if (!empty($keyword)) {
    $keyword_safe = mysqli_real_escape_string($conn, $keyword);
    $query_base .= " AND (
        jenis LIKE '%$keyword_safe%' 
        OR tanggal LIKE '%$keyword_safe%' 
        OR statuss LIKE '%$keyword_safe%' 
        OR nama LIKE '%$keyword_safe%' 
        OR nip LIKE '%$keyword_safe%' 
        OR jabatan LIKE '%$keyword_safe%'
    )";
}

if (!empty($status_filter)) {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $query_base .= " AND statuss = '$safe_status'";
}

$jumlahDataPerHalaman = 10;
$jumlahData = count(query($query_base));
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
$halamanAktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;

$query_final = $query_base . " ORDER BY tanggal DESC LIMIT $awalData, $jumlahDataPerHalaman";
$reimburse = query($query_final);
?>





<!DOCTYPE html>
<html lang="en">

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

            background-color: #f9f9fb;
            /* lebih soft dari rgb(239, 239, 239) */


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
            transform: translateX(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .sidebar:hover .nav-item .label {
            opacity: 1;
            transform: translateX(0);
        }

        .nav-item:hover {
            background-color: rgb(241, 241, 241);
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

        .custom-table {
            background: white;
            border-radius: 12px;
            padding: 20px;
            overflow-x: auto;

            box-shadow: 0 4px 12px rgba(224, 136, 136, 0.05);
        }

        .custom-table-item:hover {
            background-color: #f2f6ff;
            border-radius: 8px;
        }

        .custom-table-item,
        .table-header {
            min-width: 1000px;
        }



        .custom-table-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            color: #185ABC;
            border-bottom: 2px solid #ccc;
        }

        .status-disetujui {
            color: #0f5132;
            background-color: #d1e7dd;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            display: inline-block;
        }

        .status-ditolak {
            color: #842029;
            background-color: #f8d7da;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            display: inline-block;
        }

        h2 {
            font-size: 40px;
            font-weight: bold;
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
    <!-- Modal Untuk Melihat Bukti -->
    <div id="buktiModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">&times;</button>
            <div id="buktiContainer"></div>
        </div>
    </div>

    <div class="sidebar">
        <div class="logo">
            <img src="img/pi.jpeg" alt="Logo" width="30">
        </div><a href="<?= ($_SESSION['active_role'] ?? 'user') === 'admin' ? 'admin.php' : 'index.php'; ?>" class="nav-item position-relative">

            <i class="bi bi-house-door-fill"></i>
            <span class="label">Beranda</span>
        </a>

        <?php
        $dashboard_link = (in_array('admin', $_SESSION['roles'] ?? [])) ? 'admin.php' : 'index.php';
        $dashboard_active = basename($_SERVER['PHP_SELF']) === basename($dashboard_link) ? 'active' : '';
        ?></a>

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
                <span style="color: #185ABC;">Catatan </span><span style="color: #F7941D;"> Reimburse</span>
            </h1>
            <form class="search-form" method="get" action="history.php">
                <input class="form-control " type="text" name="keyword" placeholder="Search"
                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" autocomplete="off">
                <button class="btn btn-outline-primary" type="submit" name="cari">Search</button>
            </form>

        </div>
        <div class="custom-table">

            <div class="table-header" style="display: flex; justify-content: space-between;">
                <div style="flex:2;">Nama</div>
                <div style="flex:1; text-align:center;">NIP</div>
                <div style="flex:1; text-align:center;">Jabatan</div>
                <div style="flex:1; text-align:center;">Jenis</div>
                <div style="flex:1; text-align:center;">Tanggal</div>
                <div style="flex:1; text-align:center;">Nominal</div>
                <div style="flex:1; text-align:center;">Status</div>
                <div style="flex:1; text-align:center;">Bukti</div>
                <div style="flex:1; text-align:center;">CSV</div>
            </div>

            <?php foreach ($reimburse as $row) : ?>
                <div class="custom-table-item">
                    <div style="flex:2;"><?= htmlspecialchars($row['nama']) ?></div>
                    <div style="flex:1; text-align:center;"><?= htmlspecialchars($row['nip']) ?></div>
                    <div style="flex:1; text-align:center;"><?= htmlspecialchars($row['jabatan']) ?></div>
                    <div style="flex:1; text-align:center;"><?= htmlspecialchars($row['jenis']) ?></div>
                    <div style="flex:1; text-align:center;"><?= htmlspecialchars($row['tanggal']) ?></div>
                    <div style="flex:1; text-align:center;">Rp<?= number_format($row['nominal'], 0, ',', '.') ?></div>
                    <div style="flex:1; text-align:center;">
                        <div class="<?= strtolower($row['statuss']) === 'disetujui' ? 'status-disetujui' : 'status-ditolak' ?>">
                            <?= htmlspecialchars($row['statuss']) ?>
                        </div>
                    </div>
                    <div style="flex:1; text-align:center;">
                        <?php if ($row['bukti']) : ?>
                            <a href="javascript:void(0);" onclick="openModal('uploads/<?= htmlspecialchars($row['bukti']) ?>')" style="color: #F7941D;">Lihat Bukti</a>
                        <?php else : ?>
                            Tidak ada
                        <?php endif; ?>
                    </div>
                    <div style="flex:1; text-align:center;">
                        <?php if ($row['csv_file']) : ?>
                            <a href="uploads/<?= htmlspecialchars($row['csv_file']) ?>" target="_blank" style="color:#185ABC;">Lihat</a>
                        <?php else : ?>
                            Tidak ada
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    // Encode Parameter Pencarian Agar Aman di URL
    $encodedKeyword = urlencode($keyword);
    $encodedStatus = urlencode($status_filter);
    ?>

    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">

            <!-- Tombol Previous -->
            <?php if ($halamanAktif > 1) : ?>
                <li class="page-item">
                    <a href="?halaman=<?= $halamanAktif - 1; ?>&keyword=<?= $encodedKeyword ?>&status=<?= $encodedStatus ?>"
                        class="page-link" style="background-color: #d9d9d9;">Previous</a>
                </li>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $jumlahHalaman; $i++) : ?>
                <li class="page-item <?= ($i == $halamanAktif) ? 'active' : '' ?>">
                    <a class="page-link"
                        href="?halaman=<?= $i; ?>&keyword=<?= $encodedKeyword ?>&status=<?= $encodedStatus ?>">
                        <?= $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <?php if ($halamanAktif < $jumlahHalaman) : ?>
                <li class="page-item">
                    <a href="?halaman=<?= $halamanAktif + 1; ?>&keyword=<?= $encodedKeyword ?>&status=<?= $encodedStatus ?>"
                        class="page-link" style="background-color: #d9d9d9;">Next</a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>


    <script>
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
</body>

</html>