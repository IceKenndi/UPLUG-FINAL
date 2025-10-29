<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . "/../config/dbconfig.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if ($_POST['login_role'] === 'student') {
        $sql = sprintf("SELECT * FROM student_users WHERE email = '%s'", $conn->real_escape_string($_POST['login_email']));
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($_POST['login_password'], $user['password_hash'])) {
                if ($user['verified'] != 1) {
                    session_start();
                    $otp = rand(100000, 999999);
                    $_SESSION['otp_code'] = $otp;
                    $_SESSION['otp_expiry'] = time() + 600;
                    $_SESSION['pending_email'] = $user['email'];
                    $_SESSION['pending_role'] = 'student';
                    $_SESSION['pending_table'] = 'student_users';
                    $_SESSION['pending_seq_id'] = $user['seq_id'] ?? null;

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'uplug.noreply@gmail.com';
                        $mail->Password = 'iphk qwnj feso feqd';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug OTP');
                        $mail->addAddress($user['email']);
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
                        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
                        exit();
                    }

                    echo json_encode(['success' => false, 'message' => 'Please verify your account via OTP']);
                    exit();
                }

                session_start();
                session_regenerate_id();
                $_SESSION['user_id'] = $user['student_id'];
                $session_id = $_SESSION['user_id'];
                $parts = explode('-', $session_id);
                $departmentCode = strtoupper(end($parts));
                $_SESSION['department_code'] = $departmentCode;
                $_SESSION['show_welcome'] = true;

                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit();
        }
    }

    else if ($_POST['login_role'] === 'faculty') {
        $sql = sprintf("SELECT * FROM faculty_users WHERE email = '%s'", $conn->real_escape_string($_POST['login_email']));
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($_POST['login_password'], $user['password_hash'])) {
                if ($user['verified'] != 1) {
                    session_start();
                    $otp = rand(100000, 999999);
                    $_SESSION['otp_code'] = $otp;
                    $_SESSION['otp_expiry'] = time() + 600;
                    $_SESSION['pending_email'] = $user['email'];
                    $_SESSION['pending_role'] = 'faculty';
                    $_SESSION['pending_table'] = 'faculty_users';
                    $_SESSION['pending_seq_id'] = $user['seq_id'] ?? null;

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'uplug.noreply@gmail.com';
                        $mail->Password = 'iphk qwnj feso feqd';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug OTP');
                        $mail->addAddress($user['email']);
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
                        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
                        exit();
                    }

                    echo json_encode(['success' => false, 'message' => 'Please verify your account via OTP']);
                    exit();
                }

                session_start();
                session_regenerate_id();
                $_SESSION['user_id'] = $user['faculty_id'];
                $session_id = $_SESSION['user_id'];
                $parts = explode('-', $session_id);
                $departmentCode = strtoupper(end($parts));
                $_SESSION['department_code'] = $departmentCode;
                $_SESSION['show_welcome'] = true;

                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit();
        }
    }

    else if ($_POST['login_role'] === 'admin') {
        $sql = sprintf("SELECT * FROM admin_users WHERE email = '%s'", $conn->real_escape_string($_POST['login_email']));
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($_POST['login_password'], $user['password_hash'])) {
                if ($user['verified'] != 1) {
                    session_start();
                    $otp = rand(100000, 999999);
                    $_SESSION['otp_code'] = $otp;
                    $_SESSION['otp_expiry'] = time() + 600;
                    $_SESSION['pending_email'] = $user['email'];
                    $_SESSION['pending_role'] = 'admin';
                    $_SESSION['pending_table'] = 'admin_users';
                    $_SESSION['pending_seq_id'] = $user['seq_id'] ?? null;

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'uplug.noreply@gmail.com';
                        $mail->Password = 'iphk qwnj feso feqd';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port = 587;

                        $mail->setFrom('uplug.noreply@gmail.com', 'U-Plug OTP');
                        $mail->addAddress($user['email']);
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
                        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
                        exit();
                    }

                    echo json_encode(['success' => false, 'message' => 'Please verify your account via OTP']);
                    exit();
                }

                session_start();
                session_regenerate_id();
                $_SESSION['user_id'] = $user['admin_id'];
                $session_id = $_SESSION['user_id'];
                $_SESSION['role'] = 'admin';
                $_SESSION['show_welcome'] = true;

                $redirectUrl = '/admin/admin.php';

                echo json_encode(['success' => true,
                                  'redirect' => $redirectUrl
                                ]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Account not found']);
            exit();
        }
    }
}
?>