<?php
session_start();

$role = $_GET['role'] ?? '';

if (!isset($_SESSION['roles']) || !in_array($role, $_SESSION['roles'])) {
  header("Location: login.php");
  exit;
}

$_SESSION['active_role'] = $role;

// Redirect sesuai role
if ($role === 'admin') {
  header("Location: admin.php");
} else {
  header("Location: index.php");
}
exit;
