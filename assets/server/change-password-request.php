<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config/dbconfig.php';

$user_id = $_POST['user_id'];
$role = $_POST['reset_role'];
$email = $_POST['email'];

$token = bin2hex(random_bytes(32));
$expiry = time() + 600; // 10 minutes

$table = $role . "_users";
$id = $role . "_id";

$sql = "UPDATE $table SET reset_token = ?, reset_expiry = ? WHERE $id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $token, $expiry, $user_id);
$stmt->execute();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'uplug.noreply@gmail.com';
    $mail->Password = 'iphk qwnj feso feqd';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug Security');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'U-Plug Change Password Request';

    $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <p>Dear User,</p>
            <p>You requested to change your password. Please click the link below to proceed:</p>
            <p><a href='http://uplug.progress/assets/server/reset-password.php?token=$token' style='color: #007BFF;'>Change Your Password</a></p>
            <p>This link will expire in 10 minutes and can only be used once.</p>
            <br><hr>
            <p style='font-size: 12px; color: #777;'>© " . date('Y') . " U-Plug. All rights reserved.</p>
        </div>
    ";

    $mail->send();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send reset email.']);
    exit();
}


$_SESSION['toastPosts'][] = [
  'post_id' => uniqid('reset_', true),
  'toast_message' => 'A reset link has been sent to your email.',
  'type' => 'success',
  'create_date' => date('Y-m-d H:i:s'),
];

// Redirect to profile with toast
session_write_close();
header("Location: /profile.php");
exit();
