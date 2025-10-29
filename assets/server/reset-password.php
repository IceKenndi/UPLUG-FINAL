<?php
session_start();
require __DIR__ . '/../config/dbconfig.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "Invalid or missing token.";
    exit();
}

$sql = "SELECT student_id AS id, 'student' AS role, reset_expiry FROM student_users WHERE reset_token = ?
        UNION
        SELECT faculty_id AS id, 'faculty' AS role, reset_expiry FROM faculty_users WHERE reset_token = ?
        UNION
        SELECT admin_id AS id, 'admin' AS role, reset_expiry FROM admin_users WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $token, $token, $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || time() > $user['reset_expiry']) {
    echo "This reset link is invalid or has expired.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | U-Plug</title>
  <link rel="stylesheet" href="/assets/css/reset-password.css">
</head>
<body>

<div class="auth-container">
  <div class="glass-card">
    <h2>Reset Your Password</h2>
    <form method="POST" action="/assets/server/reset-password-process.php" class="auth-form">
      <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
      <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <div class="password-wrapper">
        <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
        <span class="toggle-password" onclick="toggleVisibility('new_password', this)">
          <img src="/assets/images/client/hidden_password.png" alt="Show" class="eye-icon">
        </span>
      </div>

      <div class="password-wrapper">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <span class="toggle-password" onclick="toggleVisibility('confirm_password', this)">
          <img src="/assets/images/client/hidden_password.png" alt="Show" class="eye-icon">
        </span>
      </div>

      <button type="submit" class="login-btn">Update Password</button>
    </form>
  </div>
</div>

<script>
  function toggleVisibility(inputId, toggleIcon) {
    const input = document.getElementById(inputId);
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    toggleIcon.querySelector('img').src = isPassword
      ? "/assets/images/client/show_password.png"
      : "/assets/images/client/hidden_password.png";
  }
</script>

</body>
</html>
