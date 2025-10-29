<?php
require __DIR__ . '/../config/dbconfig.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$postId = $_POST['post_id'] ?? null;
if ($postId) {
  $stmt = $conn->prepare("UPDATE posts SET toast_status = 0 WHERE post_id = ?");
  $stmt->bind_param("s", $postId);
  $stmt->execute();
}
