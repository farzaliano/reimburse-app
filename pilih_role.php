<?php
session_start();

if (!isset($_SESSION['roles'])) {
  header("Location: login.php");
  exit;
}

$roles = $_SESSION['roles'];
$nama  = $_SESSION['nama'] ?? $_SESSION['user'] ?? 'Pengguna';

// Redirect otomatis jika hanya 1 role
if (count($roles) === 1) {
  $_SESSION['active_role'] = $roles[0];
  header("Location: " . ($roles[0] === 'admin' ? 'admin.php' : 'index.php'));
  exit;
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Pilih Peran</title>
  <link rel="icon" href="img/ppi.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    *{
      font-family: 'Poppins', sans-serif;
    }
    body {
      font-family: Poppins, sans-serif;
      background: linear-gradient(to right, #007BFF, #F7941D);
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .box {
      background: white;
      color: black;
      padding: 30px 40px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .role-btn {
      background: none;
      border: none;
      margin: 15px;
      font-size: 48px;
      color: #F7941D;
      transition: transform 0.2s;
    }

    .role-btn:hover {
      transform: scale(1.2);
      color: #d67c00;
    }

    .bi-shield-lock-fill {
      color: #007bff;
    }

    .bi-person-circle {
      color: #F7941D;
    }
  </style>
</head>

<body>
  <div class="box">
    <h3>Selamat datang, <?= htmlspecialchars($nama) ?>!</h3>
    <p>Pilih peran Anda:</p>

    <div class="d-flex justify-content-center gap-4 mt-3">
      <?php if (in_array('user', $roles)) : ?>
        <a href="set_role.php?role=user" class="role-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Masuk sebagai User">
          <i class="bi bi-person-circle"></i>
        </a>
      <?php endif; ?>

      <?php if (in_array('admin', $roles)) : ?>
        <a href="set_role.php?role=admin" class="role-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Masuk sebagai Admin">
          <i class="bi bi-shield-lock-fill"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Aktifkan tooltip
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new bootstrap.Tooltip(el);
    });
  </script>
</body>

</html>
