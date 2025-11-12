<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ClaimEase</title>
  <link rel="icon" href="img/ll.png">
  
  <!-- Google Font Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #185ABC;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
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
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px; /* Jarak antara icon dan teks */
    }

    button:hover {
      background-color: rgb(0, 37, 92);
    }

    .btn-space {
      margin-top: 15px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <center><img src="img/ll.png" width="150"></center>
    <br>
    <button type="button" onclick="location.href='login.php?role=admin'">
      <i class="bi bi-person-fill-lock"></i> Admin
    </button>
    <button type="button" class="btn-space" onclick="location.href='login.php?role=user'">
      <i class="bi bi-person-fill"></i> Users
    </button>
  </div>
</body>
</html>
