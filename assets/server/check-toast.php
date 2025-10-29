<?php
require __DIR__ . "/../config/dbconfig.php";
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$user_department = $_SESSION['department_code'] ?? null;

if (!$user_id) {
  echo json_encode([]);
  exit();
}

if ($user_department) {
  // ✅ Main query: exclude author, match department for department posts, allow all official posts
  $query = "SELECT p.post_id, p.toast_message, p.create_date, p.post_type, p.edited_at 
            FROM posts p
            LEFT JOIN toast_acknowledgments ta ON p.post_id = ta.post_id AND ta.user_id = ?
            WHERE p.toast_status = 1 
              AND p.author_id != ? 
              AND ta.post_id IS NULL
              AND (
                p.post_type = 'official' OR 
                (p.post_type = 'department' AND p.author_department = ?)
              )
            ORDER BY p.create_date DESC";
  
  $stmt = $conn->prepare($query);
$stmt->bind_param("sss", $user_id, $user_id, $user_department);
} else {
  // ✅ Fallback: user has no department, show only official posts not authored by them
  $query = "SELECT post_id, toast_message, create_date, post_type, edited_at 
            FROM posts 
            WHERE toast_status = 1 
              AND post_type = 'official' 
              AND author_id != ?
            ORDER BY create_date DESC";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$toasts = [];
while ($row = $result->fetch_assoc()) {
  $toasts[] = [
    'post_id' => $row['post_id'],
    'message' => $row['toast_message'],
    'timestamp' => $row['edited_at'] ?? $row['create_date'],
    'was_edited' => !empty($row['edited_at']),
    'post_type' => $row['post_type']
  ];
}

echo json_encode($toasts);
