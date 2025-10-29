<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
  require __DIR__ . "/../config/dbconfig.php";
  session_start();

  $title = $_POST['title'] ?? '';
  $content = $_POST['content'] ?? '';
  $post_type = $_POST['post_type'] ?? '';
  $authorID = $_SESSION['user_id'] ?? null;

  if (!$authorID || !$title || !$content || !$post_type) {
    die("Missing required fields.");
  }

  // Get user info
  if (strpos($authorID, 'FAC-') === 0) {
    $role = 'Faculty';
    $stmt = $conn->prepare("SELECT full_name, department FROM faculty_users WHERE faculty_id = ?");
  } else if (strpos($authorID, 'STU-') === 0) {
    $role = 'Student';
    $stmt = $conn->prepare("SELECT full_name, department FROM student_users WHERE student_id = ?");
  } else {
    die("Invalid user.");
  }

  $stmt->bind_param("s", $authorID);
  $stmt->execute();
  $stmt->bind_result($fullName, $department);
  $stmt->fetch();
  $stmt->close();

  // Create toast message
  $toastMessage = "$department $role - $fullName posted a new $post_type post!";

  // Determine toast visibility
  $toastStatus = ($post_type === 'official' || $post_type === 'department') ? 1 : 0;

  // Insert post
  $stmt = $conn->prepare("INSERT INTO posts (title, content, author_id, post_type, author_department, toast_status, toast_message, create_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
  $stmt->bind_param("sssssis", $title, $content, $authorID, $post_type, $department, $toastStatus, $toastMessage);
  $stmt->execute();
  $stmt->close();

  $_SESSION['toastPosts'][] = [
  'post_id' => uniqid('upload_', true),
  'toast_message' => 'Post uploaded successfully!',
  'create_date' => date('Y-m-d H:i:s'),
  'edited_at' => null
];

  header("Location: " . ($post_type === 'personal' ? "/../profile.php" : "/../news.php"));
  exit();
}
?>
