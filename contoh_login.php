<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | ClaimEase</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0; padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body, html {
      height: 100%;
    }

    .container {
      display: flex;
      height: 100vh;
    }

    .left {
      flex: 1;
      background: linear-gradient(to bottom right, #007BFF, #F7941D);
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      text-align: center;
      padding: 2rem;
    }

    .left img {
      width: 200px;
      margin-bottom: 20px;
    }

    .right {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f4f6f8;
    }

    .login-box {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgb(251, 2, 2);
      width: 100%;
      max-width: 400px;
    }

    .login-box h2 {
      margin-bottom: 20px;
      color: #007BFF;
    }

    .login-box input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .login-box button {
      width: 100%;
      padding: 12px;
      border: none;
      background-color: #F7941D;
      color: white;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
    }

    .login-box button:hover {
      background-color: #e07d15;
    }
    .search-popup {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.popup-content {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    position: relative;
    text-align: center;
}

.close-btn {
    position: absolute;
    top: 10px; right: 15px;
    font-size: 24px;
    cursor: pointer;
}

  </style>
</head>
<body>
  <!-- <div class="container">
    <div class="left">
      
      <h1>ClaimEase</h1>
      <p>Smart & Easy Reimbursement</p>
    </div>
    <div class="right">
      <form class="login-box" action="login.php" method="POST">
        <h2>Login</h2>
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
      </form>
    </div>
  </div> -->
  <!-- Search Icon -->
<div style="position: absolute; top: 20px; right: 80px;">
    <i class="bi bi-search" id="searchBtn" style="font-size: 24px; cursor: pointer;"></i>
</div>

<!-- Search Popup -->
<div id="searchPopup" class="search-popup">
    <div class="popup-content">
        <span class="close-btn" id="closeSearch">&times;</span>
        <h5 style="margin-bottom: 20px;">Cari Data Reimburse</h5>
        <form action="index.php" method="post">
            <input type="text" name="keyword" class="form-control" placeholder="Masukkan kata kunci..." required>
            <button type="submit" name="cari" class="btn btn-primary mt-3" style="background-color:#185ABC;">Cari</button>
        </form>
    </div>
</div>



<script>

</script>

</body>
</html>
