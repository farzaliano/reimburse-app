<?php

if (!isset($_GET['file'])) {
    header("Location: index.php");
    exit;
}

$file = basename($_GET['file']);
$path = "uploads/$file";

if (!file_exists($path)) {
    echo "File tidak ditemukan.";
    exit;
}
$ext = pathinfo($path, PATHINFO_EXTENSION);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Lihat Bukti</title>
  <link rel="icon" href="img/ll.png" />
  <style>
    body {
      margin: 0;
      background: #f9f9f9;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      position: relative;
      max-width: 90vw;
      max-height: 90vh;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      background: white;
      padding: 20px;
      border-radius: 12px;
    }

    .close-btn {
      position: absolute;
      top: -15px;
      right: -15px;
      background: #185ABC;
      color: white;
      border: none;
      font-size: 18px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      transition: 0.3s ease;
    }

    .close-btn:hover {
      background: #143d82;
      transform: rotate(90deg);
    }

    .viewer {
      width: 100%;
      height: 80vh;
    }

    img, embed {
      max-width: 100%;
      max-height: 80vh;
      border-radius: 8px;
      display: block;
      margin: 0 auto;
    }
  </style>
</head>
<body>
  <div class="container">
    <button class="close-btn" onclick="window.location.href='index.php'">&times;</button>

    <div class="viewer">
      <?php if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
        <img src="<?= $path ?>" alt="Bukti Reimburse">
      <?php elseif ($ext === 'pdf'): ?>
        <embed src="<?= $path ?>" type="application/pdf" width="100%" height="100%" />
      <?php else: ?>
        <p>File format tidak didukung.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
