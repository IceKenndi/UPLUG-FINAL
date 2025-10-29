<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once "../../assets/config/dbconfig.php";

if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing student ID']);
    exit;
}

$id = intval($_POST['id']);
$stmt = $conn->prepare("DELETE FROM faculty_users WHERE seq_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete student']);
}

$stmt->close();
$conn->close();
?>
