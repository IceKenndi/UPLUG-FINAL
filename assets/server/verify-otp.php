<?php
session_start();
if (!isset($_SESSION['pending_email'])) {
  header("Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Verify OTP</title>
  <link rel="stylesheet" href="/assets/css/index.css">
</head>
<style>
  #note{
    color:white;
  }
</style>
<body>
  <div class="glass-card">
    <h2>Verify Your Email</h2>
    <form action="verify-otp-process.php" method="POST">
      <input type="text" name="otp_input" placeholder="Enter OTP" required>
      <button type="submit">Verify</button>
      <p id="note"> We've sent an OTP to your email </p>
    </form>
  </div>
</body>
</html>
