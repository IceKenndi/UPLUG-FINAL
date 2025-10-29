<?php
session_start();
require __DIR__ . '/../config/dbconfig.php';

$role = $_POST['forgot_role'] ?? '';
$email = strtolower(trim($_POST['forgot_email'] ?? ''));

$table = $role . "_users";
$id_column = $role . "_id";

// ✅ Check if user exists
$sql = "SELECT $id_column FROM $table WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  error_log("Reset request failed for $email in $table");
  echo "<div class='error-box'>No account found with that email.</div>";
  exit();
}

// ✅ Generate token and expiry
$token = bin2hex(random_bytes(16));
$expiry = time() + (60 * 30); // 30 minutes

// ✅ Store token
$update = $conn->prepare("UPDATE $table SET reset_token = ?, reset_expiry = ? WHERE email = ?");
$update->bind_param("sis", $token, $expiry, $email);
$update->execute();

// ✅ Send email

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Adjust path if needed

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'uplug.noreply@gmail.com';         // 🔐 Your Gmail
    $mail->Password = 'iphk qwnj feso feqd';           // 🔐 App password from Google
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug Password Reset');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'U-Plug Reset Password Request';
    $mail->Body = "
      <div style='font-family: Arial, sans-serif; color: #333;'>
        <p>Dear User,</p>
        <p>We received a request to reset your password for your <strong>U-Plug</strong> account.</p>
        <p>To proceed, please click the button below. This link is valid for <strong>30 minutes</strong> and should not be shared with anyone.</p>
        <p style='margin: 20px 0;'>
          <a href='http://uplug.progress/assets/server/reset-password.php?token=$token' style='color: #007BFF;'>Reset Your Password</a>
        </p>
        <p>If you did not request this password reset, you can safely ignore this email. Your account will remain secure.</p>
        <br>
        <hr style='border: none; border-top: 1px solid #ccc; margin: 30px 0;'>
        <p style='font-size: 12px; color: #777;'>© " . date('Y') . " U-Plug. All rights reserved.</p>
      </div>
    ";
    $mail->send();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP. Please try again later.'
    ]);
    exit;
}

// ✅ Show branded confirmation
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Link Sent</title>
  <link rel="stylesheet" href="/assets/css/index.css">
  <style>
    .reset-success {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 30vh;
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
  <div class="card">
    <h2>Reset Link Sent</h2>
    <p>We've emailed you a link to reset your password. It expires in 30 minutes.</p>
    <a href="/index.php" class="login-redirect">Return to Login</a>
  </div>
</div>

</body>
</html>
