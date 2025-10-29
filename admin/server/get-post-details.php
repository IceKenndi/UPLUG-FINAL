<?php
require_once __DIR__ . "/../../assets/config/dbconfig.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
  http_response_code(400);
  echo json_encode(["error" => "Missing post ID"]);
  exit;
}

$post_id = $_GET['id'];
$stmt = $conn->prepare("SELECT create_date, edited_at FROM posts WHERE post_id = ?");
$stmt->bind_param("s", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode($row);
} else {
  http_response_code(404);
  echo json_encode(["error" => "Post not found"]);
}

$stmt->close();
$conn->close();
?>
