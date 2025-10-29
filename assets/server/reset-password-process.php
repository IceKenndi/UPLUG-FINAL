<?php
session_start();
require __DIR__ . '/../config/dbconfig.php';

$user_id = $_POST['user_id'] ?? '';
$role = $_POST['role'] ?? '';
$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$table = $role . "_users";
$id_column = $role . "_id";

// ✅ Validate passwords
if (strlen($new_password) < 8) {
    echo "<div class='error-box'>Password must be at least 8 characters.</div>";
    exit();
}

if ($new_password !== $confirm_password) {
    echo "<div class='error-box'>Passwords do not match.</div>";
    exit();
}

// ✅ Verify token and expiry
$sql = "SELECT reset_expiry FROM $table WHERE $id_column = ? AND reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_id, $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || time() > $user['reset_expiry']) {
    echo "<div class='error-box'>Invalid or expired token.</div>";
    exit();
}

// ✅ Hash and update password
$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$clear_sql = "UPDATE $table SET password_hash = ?, reset_token = NULL, reset_expiry = NULL WHERE $id_column = ?";
$update = $conn->prepare($clear_sql);
$update->bind_param("ss", $hashed, $user_id);
$update->execute();

// ✅ Destroy session and show success message
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Password Reset Successful</title>
  <link rel="stylesheet" href="/assets/css/reset-password.css">
  <style>
    .reset-success {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      text-align: center;
      color: #e7eaf0;
      font-family: 'Segoe UI', sans-serif;
    }

    .reset-success h2 {
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }

    .reset-success p {
      font-size: 1rem;
      margin-bottom: 2rem;
    }

    .login-redirect {
      background: #233511;
      color: #e7eaf0;
      padding: 0.7rem 1.2rem;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .login-redirect:hover {
      background: #e7eaf0;
      color: #233511;
    }
  </style>
</head>
<body>
  <div class="reset-success">
    <h2>Password Updated Successfully</h2>
    <p>Your password has been changed. Please log in to continue.</p>
    <a href="/index.php" class="login-redirect">Go to Login</a>
  </div>
</body>
</html>
