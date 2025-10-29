<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

if (!isset($_POST['post_id'], $_POST['title'], $_POST['content'], $_SESSION['user_id'])) {
  header("Location: /news.php");
  exit();
}

$postId = $_POST['post_id'];
$title = $_POST['title'];
$content = $_POST['content'];
$currentUserId = $_SESSION['user_id'];
$editedAt = date("Y-m-d H:i:s");

// Verify ownership
$stmt = $conn->prepare("SELECT author_id, post_type FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->bind_result($authorId, $PostType);
$stmt->fetch();
$stmt->close();

if ($authorId !== $currentUserId) {
  echo "Unauthorized update.";
  exit();
}

// Get user info for toast message
if (strpos($currentUserId, 'FAC-') === 0) {
  $role = 'Faculty';
  $stmt = $conn->prepare("SELECT full_name, department FROM faculty_users WHERE faculty_id = ?");
} else if (strpos($currentUserId, 'STU-') === 0) {
  $role = 'Student';
  $stmt = $conn->prepare("SELECT full_name, department FROM student_users WHERE student_id = ?");
} else {
  die("Invalid user.");
}

$stmt->bind_param("s", $currentUserId);
$stmt->execute();
$stmt->bind_result($fullName, $department);
$stmt->fetch();
$stmt->close();

// Update post with toast reset
$stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, edited_at = ?, toast_status = 1, toast_message = ? WHERE post_id = ?");
$stmt->bind_param("ssssi", $title, $content, $editedAt, $toastMessage, $postId);
$stmt->execute();
$stmt->close();

// Reset toast acknowledgments so all users (except author) will be notified again
$stmt = $conn->prepare("DELETE FROM toast_acknowledgments WHERE post_id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->close();

// Redirect
$origin = $_POST['origin'] ?? $_GET['origin'] ?? 'news';
if ($origin === 'profile') {
  header("Location: /profile.php");
} else {
  $tab = $_POST['tab'] ?? 'official';
  header("Location: /news.php?tab=$tab");
}
exit();