<?php
require __DIR__ . "/../config/dbconfig.php";
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;

if ($user_id && $post_id) {
  $stmt = $conn->prepare("INSERT INTO toast_acknowledgments (user_id, post_id) VALUES (?, ?)");
  $stmt->bind_param("ss", $user_id, $post_id);
  $stmt->execute();
}
