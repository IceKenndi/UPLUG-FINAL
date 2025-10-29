<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

$user_id = $_POST['user_id'] ?? null;
$department = $_POST['department_code'] ?? 'default';
$user_role_dir = $_POST['pfp_folder'] ?? 'default';
$role = $_POST['role'] ?? null;
$currentPfp = null;

if (!$user_id || !$role || !isset($_FILES['profile_picture'])) {
    die("Missing user ID, role, or File");
}

$uploadDirectory = dirname(__DIR__) . "/images/{$user_role_dir}/{$department}/";
if (!is_dir($uploadDirectory)){
    mkdir($uploadDirectory, 0755, true);
}

$ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
$newFileName = $user_id . "." . strtolower($ext);
$targetPath = $uploadDirectory . $newFileName;

if (file_exists($targetPath)) {
    unlink($targetPath);
}

if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)){
    $relativePath = "../assets/images/{$user_role_dir}/{$department}/{$newFileName}";

    $dbPath = str_replace('../', '', $relativePath);

    if ($role === "Student") {
        $stmt = $conn->prepare("SELECT profile_picture FROM student_users WHERE student_id = ?");
    } else {
        $stmt = $conn->prepare("SELECT profile_picture FROM faculty_users WHERE faculty_id = ?");
    }

    $stmt->execute([$user_id]);
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $currentPfp = $row['profile_picture'];
    }

        if ($role === "Student") {
        $updateStmt = $conn->prepare("UPDATE student_users SET profile_picture = ? WHERE student_id = ?");
    } else {
        $updateStmt = $conn->prepare("UPDATE faculty_users SET profile_picture = ? WHERE faculty_id = ?");
    }

    if ($updateStmt->execute([$dbPath, $user_id])) {
        if (empty($currentPfp) || $currentPfp === 'assets/images/default.png') {
            echo "🆕 Profile picture uploaded!";
            header("Location: /profile.php");
        } else {
            echo "🔄 Profile picture updated!";
            header("Location: /profile.php");
        }

        if ($updateStmt->affected_rows > 0) {
            echo "✅ Profile picture updated in database!";
            header("Location: /profile.php");
        }
    } else {
        echo "❌ Failed to update profile picture.";
        header("Location: /profile.php");
    }

} else {
    echo "Upload failed";
}


?>