<?php
session_start();
require __DIR__ . "/../config/dbconfig.php"; // adjust path if needed

$currentUser = $_SESSION['user_id'] ?? null;
if (!$currentUser) {
    exit("Unauthorized");
}

// ✅ Get latest message per sender, prioritize unread, and limit to 5
$sql = "
    SELECT m1.*, 
           CASE 
              WHEN SUBSTRING(m1.sender_id, 1, 4) = 'FAC-' THEN f.full_name
              WHEN SUBSTRING(m1.sender_id, 1, 4) = 'STU-' THEN s.full_name
              ELSE 'Unknown Sender'
           END AS sender_name
    FROM messages m1
    LEFT JOIN faculty_users f ON m1.sender_id = f.faculty_id
    LEFT JOIN student_users s ON m1.sender_id = s.student_id
    INNER JOIN (
        SELECT sender_id, MAX(sent_at) AS latest 
        FROM messages 
        WHERE receiver_id = ?
        GROUP BY sender_id
    ) m2 ON m1.sender_id = m2.sender_id AND m1.sent_at = m2.latest
    WHERE m1.receiver_id = ?
    ORDER BY m1.seen ASC, m1.sent_at DESC
    LIMIT 5
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $currentUser, $currentUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='opacity:0.7;'>No recent messages.</p>";
    exit;
}

while ($msg = $result->fetch_assoc()) {
    $isUnread = $msg['seen'] == 0;
    $unreadClass = $isUnread ? "unread" : "";

    echo "<div class='recent-message {$unreadClass}' 
             onclick=\"window.location='messaging.php?chat_with=" . htmlspecialchars($msg['sender_id']) . "'\">";
    echo "<strong>" . htmlspecialchars($msg['sender_name']) . "</strong>";
    echo "<p>" . htmlspecialchars($msg['content']) . "</p>";
    echo "<small>" . date('M j, Y g:i A', strtotime($msg['sent_at'])) . "</small>";
    echo "</div>";
}
?>