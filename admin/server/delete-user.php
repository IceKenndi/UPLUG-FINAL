<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . "/../../assets/config/dbconfig.php";

if (!isset($_POST['id']) || !isset($_POST['role'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$id = intval($_POST['id']);
$role = $_POST['role']; // "student" or "faculty"

$table = ($role === "student") ? "student_users" : (($role === "faculty") ? "faculty_users" : null);

if (!$table) {
    echo json_encode(['success' => false, 'message' => 'Invalid user role']);
    exit;
}
error_log("Deleting user id: $id from table: $table");

$stmt = $conn->prepare("DELETE FROM {$table} WHERE seq_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => ucfirst($role) . " deleted successfully Deleting user id: $id from table: $table"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed']);
}

$stmt->close();
$conn->close();
