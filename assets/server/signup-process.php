<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json');

require __DIR__ . "/../config/dbconfig.php";

if (empty($_POST['signup_role'])){
    echo json_encode(['success' => false, 'message' => 'Role is required (e.g. Student, Faculty)']);
    exit;
}

if (empty($_POST['department'])){
    echo json_encode(['success' => false, 'message' => 'Department is required']);
    exit;
}

if (empty($_POST['first_name'])){
    echo json_encode(['success' => false, 'message' => 'First name is required']);
    exit;
}

if (empty($_POST['last_name'])){
    echo json_encode(['success' => false, 'message' => 'Last name is required']);
    exit;
}

if (!filter_var($_POST['signup_email'], FILTER_VALIDATE_EMAIL)){
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit;
}

if (!preg_match('/@phinmaed\.com$/i', $_POST['signup_email'])) {
    echo json_encode(['success' => false, 'message' => 'Only @phinmaed.com emails are allowed']);
    exit;
}


if (strlen($_POST['signup_password']) < 8){
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit;
}

if (!preg_match("/[a-z]/i", $_POST['signup_password'])){
    echo json_encode(['success' => false, 'message' => 'Password must at least contain one letter']);
    exit;
}

if (!preg_match("/[0-9]/", $_POST['signup_password'])){
    echo json_encode(['success' => false, 'message' => 'Password must at least containt one number']);
    exit;
}

if ($_POST['signup_password'] !== $_POST['password_confirmation']){
    echo json_encode(['success' => false, 'message' => 'Passwords must match']);
    exit;
}

// Prepare user data
$role = $_POST['signup_role'];
$password_hash = password_hash($_POST['signup_password'], PASSWORD_DEFAULT);
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['signup_email'];
$department = $_POST['department'];

if ($role === 'student') {
    $table = 'student_users';
    $id_prefix = 'STU';
} elseif ($role === 'faculty') {
    $table = 'faculty_users';
    $id_prefix = 'FAC';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

// Insert user
$sql = "INSERT INTO $table (first_name, last_name, email, password_hash, department) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssss", $first_name, $last_name, $email, $password_hash, $department);

try {
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo json_encode(['success' => false, 'message' => 'Email is already taken']);
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $e->getMessage()]);
    }
    exit;
}

// Generate ID and update
$seq_id = $conn->insert_id;
$generated_id = $id_prefix . '-' . $seq_id . '-' . $department;
$full_name = $first_name . ' ' . $last_name;

$column_id = ($role === 'student') ? 'student_id' : 'faculty_id';
$update_sql = "UPDATE $table SET $column_id = ?, full_name = ? WHERE seq_id = ?";
$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    exit;
}

$update_stmt->bind_param("ssi", $generated_id, $full_name, $seq_id);
$update_stmt->execute();

// Step 1: Generate OTP and store in session
$otp = rand(100000, 999999);
$_SESSION['otp_code'] = $otp;
$_SESSION['otp_expiry'] = time() + 600; // 10 minutes
$_SESSION['pending_email'] = $email;
$_SESSION['pending_role'] = $role;
$_SESSION['pending_table'] = $table;
$_SESSION['pending_seq_id'] = $seq_id;

// Send OTP via email
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

    $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug OTP');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your U-Plug OTP Code';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <p>Dear User,</p>
            <p>Thank you for signing in to <strong>U-Plug</strong>. To complete your login, please verify your account using the One-Time Password (OTP) provided below.</p>
            <p>This OTP is valid for <strong>10 minutes</strong> and should not be shared with anyone.</p>
            <p>If you did not attempt to log in, please disregard this message.</p>
            <br>
            <p style='font-size: 18px;'>Your OTP code is:</p>
            <p style='font-size: 24px; font-weight: bold; color: #007BFF;'>$otp</p>
            <br><hr>
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

// Redirect to OTP verification page
echo json_encode([
    'success' => true,
    'redirect' => '/assets/server/verify-otp.php'
]);
exit;


?>