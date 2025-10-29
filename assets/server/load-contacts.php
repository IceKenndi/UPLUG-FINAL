<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

$active_user = $_SESSION['user_id'] ?? null;
$preselectedChat = $_SESSION['active_chat'] ?? null;

echo '<div class="contact-list-header">Contacts</div>';

$contactSql = "
  SELECT u.id, u.full_name, u.role, u.profile_picture, u.department,
         m.content, m.sent_at, m.sender_id
  FROM (
    SELECT student_id AS id, full_name, 'Student' AS role, profile_picture, department FROM student_users
    UNION
    SELECT faculty_id AS id, full_name, 'Faculty' AS role, profile_picture, department FROM faculty_users
  ) AS u
  LEFT JOIN (
    SELECT sender_id, receiver_id, content, sent_at
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
    ORDER BY sent_at DESC
  ) AS m ON (
    (m.sender_id = u.id AND m.receiver_id = ?) OR
    (m.receiver_id = u.id AND m.sender_id = ?)
  )
  WHERE u.id != ?
  ORDER BY m.sent_at DESC
";

$stmt = $conn->prepare($contactSql);
$stmt->bind_param("sssss", $active_user, $active_user, $active_user, $active_user, $active_user);
$stmt->execute();
$result = $stmt->get_result();

$seen = [];

while ($row = $result->fetch_assoc()) {
  $contactId = $row['id'];
  if (in_array($contactId, $seen)) continue;
  $seen[] = $contactId;

  // ✅ Count unread messages from this contact
  $unreadStmt = $conn->prepare("
    SELECT COUNT(*) FROM messages
    WHERE sender_id = ? AND receiver_id = ? AND seen = 0
  ");
  $unreadStmt->bind_param("ss", $contactId, $active_user);
  $unreadStmt->execute();
  $unreadStmt->bind_result($unreadCount);
  $unreadStmt->fetch();
  $unreadStmt->close();

  // Determine preview message
  if (!empty($row['content'])) {
    $preview = ($row['sender_id'] === $active_user)
      ? 'You: ' . $row['content']
      : explode(" ", $row['full_name'])[0] . ': ' . $row['content'];
  } else {
    $preview = 'No message yet';
  }

  $previewText = htmlspecialchars($preview);
  $previewClass = ($unreadCount > 0) ? 'last-message unread' : 'last-message';


  // Profile picture fallback
  $pfp = !empty($row['profile_picture']) ? $row['profile_picture'] : 'assets/images/default.png';

  // Highlight active contact
  $isActive = isset($preselectedChat) && trim($contactId) === trim($preselectedChat);
  $activeClass = $isActive ? ' active' : '';

    echo '<div class="contact-button' . $activeClass . '" data-id="' . htmlspecialchars($contactId) . '">
            <button type="button">
              <div class="avatar-image">
                <img src="/' . htmlspecialchars($pfp) . '" alt="Profile Picture" class="profile-pic">
              </div>
              <div class="contact-info">
                <div class="contact-name">' . htmlspecialchars($row['full_name']) . ' - ' . htmlspecialchars($row['department']) . ' ' . htmlspecialchars($row['role']) . '</div>
                <div class="' . $previewClass . '">' . $previewText . '</div>
              </div>';

  // ✅ Show unread badge if needed
  if ($unreadCount > 0) {
    echo '<div class="unread-badge">' . $unreadCount . '</div>';
  }

  echo '</button></div>';
}

$stmt->close();
?>
