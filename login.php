<?php
session_start();
require 'koneksi.php';

$max_attempts = 5;
$block_duration = 60; // dalam detik

// Inisialisasi login attempts dan waktu blokir jika belum ada
if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['blocked_until'])) {
  $_SESSION['blocked_until'] = 0;
}

// Cek apakah user sedang diblokir
$current_time = time();
if ($_SESSION['login_attempts'] >= $max_attempts) {
  if ($current_time < $_SESSION['blocked_until']) {
    $remaining = $_SESSION['blocked_until'] - $current_time;
    $message = "Terlalu banyak percobaan login. Coba lagi dalam <span id='countdown'>$remaining</span> detik.";
    $message_type = "danger";
  } else {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['blocked_until'] = 0;
  }
}


// Proses login jika tidak diblokir
if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_SESSION['login_attempts'] < $max_attempts)) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $captcha_input = trim($_POST['captcha_input'] ?? '');

  if (empty($username) || empty($password) || empty($captcha_input)) {
    $message = "Semua kolom wajib diisi.";
    $message_type = "danger";
    $_SESSION['login_attempts']++;
  } elseif (strtolower($captcha_input) !== strtolower($_SESSION['captcha'])) {
    $message = "Kode captcha salah.";
    $message_type = "danger";
    $_SESSION['login_attempts']++;
  } else {
    // Proses login LDAP
    $ldap_server = "ldap://172.10.10.61";
    $ldap_port   = 389;
    $domain      = "nuc.local";
    $base_dn     = "DC=nuc,DC=local";
    $group_admin_dn = "CN=PAM_ADMIN,OU=Training,OU=Users-Groups,OU=SF,OU=LAB-Training,DC=training,DC=local";

    $ldap_conn = ldap_connect($ldap_server, $ldap_port);

    if (!$ldap_conn) {
      $message = "Gagal terhubung ke LDAP.";
      $message_type = "danger";
    } else {
      ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

      $ldap_user = $username . '@' . $domain;

      if (@ldap_bind($ldap_conn, $ldap_user, $password)) {
        // Sukses login
        $filter = "(sAMAccountName=$username)";
        $result = ldap_search($ldap_conn, $base_dn, $filter, ['cn', 'memberOf', 'displayName']);
        $entries = ldap_get_entries($ldap_conn, $result);

        $namaLengkap = $entries[0]['displayname'][0] ?? $username;
        $groups = $entries[0]['memberof'] ?? [];

        $isAdmin = false;
        foreach ($groups as $group) {
          if (stripos($group, "CN=PAM_ADMIN") !== false) {
            $isAdmin = true;
            break;
          }
        }

        $roles = ['user'];
        if ($isAdmin) {
          $roles[] = 'admin';
        }

        $_SESSION['user'] = $username;
        $_SESSION['nama'] = $namaLengkap;
        $_SESSION['roles'] = $roles;
        $_SESSION['active_role'] = $isAdmin ? 'admin' : 'user';
        $_SESSION['login_attempts'] = 0;
        $_SESSION['blocked_until'] = 0;

        header("Location: pilih_role.php");
        exit;
      } else {
        $message = "Login gagal. Username atau password salah.";
        $message_type = "danger";
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= $max_attempts) {
          $_SESSION['blocked_until'] = time() + $block_duration;
        }
      }

      ldap_unbind($ldap_conn);
    }
  }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login ClaimEase</title>
  <link rel="icon" href="img/ppi.png">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(to bottom right, #007BFF, #F7941D);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .input-group input:focus {
      border: 2px solid orange;
      outline: none;
    }

    .login-container {
      background-color: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    .login-form h2 {
      text-align: center;
      margin-bottom: 25px;
      font-weight: 600;
      color: #333;
    }

    .input-group {
      margin-bottom: 20px;
      position: relative;
    }

    .input-group label {
      display: block;
      margin-bottom: 8px;
      color: #555;
    }

    .input-group input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 14px;
    }

    .toggle-password {
      position: absolute;
      top: 12px;
      right: 15px;
      cursor: pointer;
      font-size: 18px;
      color: #cccccc;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #F7941D;
      border: none;
      color: white;
      font-weight: 500;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: rgb(182, 107, 16);
    }
  </style>
</head>

<body>
  <div class="login-container">
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="login-form">
      <center><img src="img/pi.jpeg" width="150"></center>

   <?php if (!empty($message)): ?>
  <div class="alert alert-<?= $message_type ?>" style="color: red; margin-bottom: 10px;">
    <?= $message ?>
  </div>
<?php endif; ?>

      <div class="input-group">
        <input type="text" id="username" name="username" placeholder="Username" autocomplete="off" required />
      </div>
      <div class="input-group">
        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="off" />
        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
      </div>
      <div class="input-group">
        <div style="display:flex; align-items:center; gap:10px;">
          <img src="captcha.php" alt="Captcha" id="captcha-img" style="cursor: pointer;" onclick="refreshCaptcha();">
          <input type="text" name="captcha_input" required placeholder="Captcha" style="flex:1;">
        </div>
      </div>
      <button type="submit">Login</button>
    </form>
  </div>
  

  <script>
  const countdownEl = document.getElementById('countdown');
  if (countdownEl) {
    let seconds = parseInt(countdownEl.innerText);
    const interval = setInterval(() => {
      seconds--;
      countdownEl.innerText = seconds;
      if (seconds <= 0) {
        clearInterval(interval);
        location.reload(); // reload halaman setelah hitungan habis
      }
    }, 1000);
  }

    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    toggle.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('bi-eye');
      this.classList.toggle('bi-eye-slash');
    });

    function refreshCaptcha() {
      const img = document.getElementById("captcha-img");
      img.src = "captcha.php?rand=" + Math.random(); // mencegah cache
    }
  </script>
</body>
</html>