<?php
session_start();

// Validasi akses admin
if (!in_array('admin', $_SESSION['roles'] ?? [])) {
    header("Location: login.php");
    exit;
}

// Lanjutkan proses ubah status
require 'koneksi.php';

$id = $_POST['id'];
$status = $_POST['statuss'];

$query = "UPDATE reimburse SET statuss = '$status' WHERE id = $id";
mysqli_query($conn, $query);

// Set flash message (opsional)
$_SESSION['flash'] = "Status berhasil diperbarui.";

// Kembali ke halaman admin
header("Location: admin.php");
exit;
?>
