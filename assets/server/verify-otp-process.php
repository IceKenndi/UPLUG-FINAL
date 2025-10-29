<?php
session_start();
require __DIR__ . '/../config/dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputOtp = $_POST['otp_input'] ?? '';
    $sessionOtp = $_SESSION['otp_code'] ?? null;
    $expiry = $_SESSION['otp_expiry'] ?? 0;

    if (!$sessionOtp || time() > $expiry) {
        echo "<div class='error-box'>OTP expired. Please request a new one.</div>";
        exit();
    }

    if ($inputOtp != $sessionOtp) {
        echo "<div class='error-box'>Incorrect OTP.</div>";
        exit();
    }

    // Mark user as verified
    $email = $_SESSION['pending_email'];
    $table = $_SESSION['pending_table'];
    $sql = "UPDATE $table SET verified = 1 WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Clear session and force re-login
    session_unset();                    // remove all session variables
    session_destroy();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup Successful</title>
  <link rel="stylesheet" href="/assets/css/signup-success.css">
  <style>
    .signup-success {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      text-align: center;
      color: #e7eaf0;
      font-family: 'Segoe UI', sans-serif;
    }

    .signup-success h2 {
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }

    .signup-success p {
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
  <div class="signup-success">
    <h2>Verification Successful!</h2>
    <p>Login now to start using UPlug and connect with UPang!</p>
    <a href="/index.php" class="login-redirect" id="redirect-link">
      Go to Login <span id="countdown">5</span>s
    </a>
  </div>

  <script>
  let seconds = 5;
  const countdownSpan = document.getElementById("countdown");
  const redirectLink = document.getElementById("redirect-link");

  const countdown = setInterval(() => {
    seconds--;
    countdownSpan.textContent = seconds;

    if (seconds <= 0) {
      clearInterval(countdown);
      window.location.href = redirectLink.href;
    }
  }, 1000);
</script>

</body>
</html>

<!-- Add this inside your <body> just before closing -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
  // 🎉 Launch confetti burst
  confetti({
    particleCount: 150,
    spread: 70,
    origin: { y: 0.6 }
  });

  // ⏳ Redirect after 5 seconds
  setTimeout(() => {
    window.location.href = "/index.php";
  }, 5000);
</script>


