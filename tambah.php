<?php
session_start();
require 'koneksi.php';

// Redirect jika belum login
// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit;
// }

// Hitung batas tanggal
$today = date('Y-m-d');
$last_month = date('Y-m-d', strtotime('-1 month'));

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $jabatan = $_POST['jabatan'];
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    $nominal = $_POST['nominal'];
    $statuss = 'menunggu';

    // Upload bukti
    $bukti = $_FILES['bukti']['name'];
    $tmp_bukti = $_FILES['bukti']['tmp_name'];
    $path_bukti = 'uploads/' . $bukti;
    move_uploaded_file($tmp_bukti, $path_bukti);

    // Upload CSV (opsional)
    $csv = $_FILES['csv']['name'] ?? '';
    $tmp_csv = $_FILES['csv']['tmp_name'] ?? '';
    $path_csv = $csv ? 'uploads/' . $csv : '';
    if ($csv) move_uploaded_file($tmp_csv, $path_csv);

    // Simpan ke database (tanpa departemen)
    $query = "INSERT INTO reimburse (nama, nip, jabatan, jenis, tanggal, nominal, statuss, bukti, csv_file, notifikasi_admin)
          VALUES ('$nama', '$nip', '$jabatan', '$jenis', '$tanggal', '$nominal', 'menunggu', '$bukti', '$csv_file', 1)";


    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Query error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Tambah Reimburse</title>
    <link rel="icon" href="img/ll.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6fa;
        }

        /* Sidebar Modern */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 80px;
            background-color: #185ABC;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            border-top-right-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
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
            color: #185ABC;
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
            margin-left: 150px;
            padding: 40px;
        }

        form {
            width: 800px;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        label {
            margin-top: 10px;
            color: rgb(59, 0, 148);
        }

        .form-control,
        .form-select {
            margin-bottom: 16px;
        }

        .csv-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #185ABC;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #185ABC;
            border: none;
            color: white;
            font-weight: 500;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(0, 37, 92);
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <img src="img/ll.png" alt="Logo" width="30">
        </div>
        <a href="index.php" class="nav-icon <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i>
        </a>
        <a href="tambah.php" class="nav-icon <?= basename($_SERVER['PHP_SELF']) === 'tambah.php' ? 'active' : '' ?>">
            <i class="bi bi-plus-circle"></i>
        </a>
        <a href="history.php" class="nav-icon <?= basename($_SERVER['PHP_SELF']) === 'history.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i>
        </a>
        <div class="bottom-section">
            <a href="logout.php" class="nav-icon <?= basename($_SERVER['PHP_SELF']) === 'logout.php' ? 'active' : '' ?>">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
    <div class="main-content">

        <h2 style="color:rgb(0, 34, 86);">Tambah <span style="color: #185ABC;">Reimburse</span></h2>
        <a class="csv-link" href="template.csv">Download Template CSV</a>

        <form method="POST" enctype="multipart/form-data">
            <strong style="color: #185ABC;">Nama:</strong>
            <input type="text" name="nama" class="form-control" autocomplete="off" required>

            <strong style="color: #185ABC;">NIP :</strong>
            <input type="text" name="nip" class="form-control" autocomplete="off" required>

            <strong style="color: #185ABC;">Jabatan:</strong>
            <select class="form-select" name="jenis" required>
                <option value="">Pilih jabatan...</option>
                <option value="Security Engineer">Security Engineer</option>
                <option value="Senior Security Engineer"> Senior Security Engineer</option>
                <option value="Director Security Engineer">Director Security Engineer</option>
            </select>

            <strong style="color: #185ABC;">Jenis:</strong>
            <select class="form-select" name="jenis" required>
                <option value="">Pilih jenis...</option>
                <option value="transportasi">Transportasi</option>
                <option value="makan">Makan</option>
                <option value="medis">Medis</option>
            </select>

            <strong style="color: #185ABC;">Tanggal:</strong>
            <input type="date" name="tanggal" class="form-control" required min="<?= $last_month ?>" max="<?= $today ?>">

            <strong style="color: #185ABC;">Nominal:</strong>
            <input type="number" name="nominal" class="form-control" placeholder="Masukkan nominal" required>

            <strong style="color: #185ABC;">Upload Bukti (PDF/JPG/PNG):</strong>
            <input type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" class="form-control" required>

            <strong style="color: #185ABC;">Upload CSV (opsional):</strong>
            <input type="file" name="csv" accept=".csv" class="form-control">

            <button type="submit">Simpan</button>
        </form>
    </div>
</body>

</html>