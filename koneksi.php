<?php
// koneksi.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_reimburse";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

function tambah($data)
{
    global $conn;

    $jenis = $data["jenis"];
    $tanggal = $data["tanggal"];
    $nominal = $data["nominal"];
    $statuss = 'menunggu';
    $username = $data["username"];
    $nama = $data["nama"];
    $nip = $data["nip"];
    $jabatan = $data["jabatan"];

    // upload bukti
    $bukti_name = $_FILES['bukti']['name'];
    $bukti_tmp = $_FILES['bukti']['tmp_name'];
    move_uploaded_file($bukti_tmp, "uploads/" . $bukti_name);

    // upload csv
    $csv_name = '';
    if ($_FILES['csv']['error'] === 0) {
        $csv_name = $_FILES['csv']['name'];
        $csv_tmp = $_FILES['csv']['tmp_name'];
        move_uploaded_file($csv_tmp, "uploads/" . $csv_name);
    }

    $stmt = $conn->prepare("INSERT INTO reimburse 
        (username, nama, nip, jabatan, jenis, tanggal, nominal, bukti, csv_file, statuss) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssssdsss", $username, $nama, $nip, $jabatan, $jenis, $tanggal, $nominal, $bukti_name, $csv_name, $statuss);
    $stmt->execute();

    return mysqli_affected_rows($conn);
}

function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}




function cari($keyword, $username = null, ...$statuses)
{
    global $conn;

    $keyword = mysqli_real_escape_string($conn, $keyword);

    $sql = "SELECT * FROM reimburse WHERE (";
    $sql .= "nama LIKE '%$keyword%' OR ";
    $sql .= "nip LIKE '%$keyword%' OR ";
    $sql .= "jenis LIKE '%$keyword%' OR ";
    $sql .= "statuss LIKE '%$keyword%' OR ";
    $sql .= "tanggal LIKE '%$keyword%' )";

    if ($username !== null) {
        $sql .= " AND username = '$username'";
    }


    if (!empty($statuses)) {
        $statusList = implode("','", $statuses);
        $sql .= " AND statuss IN ('$statusList')";
    }

    $sql .= " ORDER BY tanggal DESC";

    return query($sql);
}
