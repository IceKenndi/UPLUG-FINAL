<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

function getUserType($userId){
    if (strpos($userId, 'FAC-') === 0){
        return 'faculty';
    } else if (strpos($userId, 'STU-') === 0){
        return 'student';
    } else {
        return 'unknown';
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $senderId = $_SESSION['user_id'];
    $receiverId = $_POST['receiver_id'];
    $content = $_POST['content'];

    $senderType = getUserType($senderId);
    $receiverType = getUserType($receiverId);

    // ✅ Insert with seen = 0 (unread)
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_type, receiver_id, receiver_type, content, seen) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssss", $senderId, $senderType, $receiverId, $receiverType, $content);
    $stmt->execute();
    $stmt->close();

    echo "Message sent";
    exit();
}
