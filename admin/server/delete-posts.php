<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once "../../assets/config/dbconfig.php";

// Validate POST
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID']);
    exit;
}

$post_id = intval($_POST['id']);

// Delete post

$stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
}

$stmt->close();
$conn->close();
?>
