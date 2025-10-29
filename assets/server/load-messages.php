<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

$active_user = $_SESSION['user_id'];
$chatWith = $_GET['chat_with'];
$markSeen = $conn->prepare("
  UPDATE messages SET seen = 1
  WHERE sender_id = ? AND receiver_id = ? AND seen = 0
");
$markSeen->bind_param("ss", $chatWith, $active_user);
$markSeen->execute();
$markSeen->close();

function getUserDetails($conn, $userId){
  if (strpos($userId, 'STU-') === 0){
    $stmt = $conn->prepare('SELECT full_name FROM student_users WHERE student_id = ?');
    $role = 'Student';
  } elseif (strpos($userId, 'FAC-') === 0){
    $stmt = $conn->prepare('SELECT full_name FROM faculty_users WHERE faculty_id = ?');
    $role = 'Faculty';
  } else {
    return ['name' => 'Unknown', 'role' => 'Unknown'];
  }

  $stmt->bind_param("s", $userId);
  $stmt->execute();
  $stmt->bind_result($name);
  $stmt->fetch();
  $stmt->close();

  return ['name' => $name ?? 'Unknown', 'role' => $role];
}

$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC");
$stmt->bind_param("ssss", $active_user, $chatWith, $chatWith, $active_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  // ✅ No messages yet — show empty chat block
  echo "<div class='empty-chat'>
          <div class='empty-chat-icon'>💬</div>
          <p>No messages yet. Start the conversation!</p>
        </div>";
} else {
  while ($msg = $result->fetch_assoc()) {
    $isOwnMessage = trim($msg['sender_id']) === trim($active_user);
    $messageClass = 'message ' . ($isOwnMessage ? 'message-right' : 'message-left');
    $wrapperClass = $isOwnMessage ? 'align-right' : 'align-left';
    
    if ($isOwnMessage) {
      $senderDisplay = 'You';
    } else {
      $senderDetails = getUserDetails($conn, $msg['sender_id']);
      $senderDisplay = $senderDetails['name'];
    }
  
    echo "<div class='message-wrapper {$wrapperClass}'>";
    echo "<div class='{$messageClass}'>";
    echo "<strong>" . htmlspecialchars($senderDisplay) . ":</strong>";
    echo "<p>" . htmlspecialchars($msg['content']) . "</p>";
    echo "<small>" . date("F j, Y - h:i A", strtotime($msg['sent_at'])) . "</small>";
    echo "</div></div>";
  }
}