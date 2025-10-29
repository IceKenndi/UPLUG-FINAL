<?php
session_start();
require __DIR__ . "/../config/dbconfig.php";

$active_user = $_SESSION['user_id'];
$chatWith = $_GET['chat_with'] ?? $_POST['chat_with'] ?? null;

if (!$chatWith) {
  echo "<h2>No chat selected</h2>";
  exit();
}
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

$chatWithDetails = getUserDetails($conn, $chatWith);

$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?)
                                                  OR (sender_id = ? AND receiver_id = ?)
                                                  ORDER BY sent_at ASC");

$stmt->bind_param("ssss", $active_user, $chatWith, $chatWith, $active_user);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2><?= htmlspecialchars($chatWithDetails['name']) . " - " . htmlspecialchars($chatWithDetails['role'])?></h2>

<div id="chatBox">
<?php while ($msg = $result->fetch_assoc()): ?>
    <?php
      $isOwnMessage = $msg['sender_id'] === $active_user;
      $messageClass = 'message ' . ($isOwnMessage ? 'message-right' : 'message-left');
    ?>
    <div class="message-wrapper <?= $isOwnMessage ? 'align-right' : 'align-left' ?>">
      <div class="<?= $messageClass ?>">
        <strong><?= htmlspecialchars($senderDisplay) ?>:</strong>
        <p><?= htmlspecialchars($msg['content']) ?></p>
        <small><?= date("F j, Y - h:i A", strtotime($msg['sent_at'])) ?></small>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<form id="messageForm">
  <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($chatWith) ?>">
  <textarea name="content" required></textarea>
  <button type="submit">Send</button>
</form>

<script>
let currentChatWith = <?= json_encode($chatWith) ?>;
  
function loadMessages() {
  if (!currentChatWith) return;

  fetch(`/assets/server/load-messages.php?chat_with=${currentChatWith}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('chatBox').innerHTML = html;
    });
}

document.getElementById('messageForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData();
  formData.append('receiver_id', currentChatWith);
  formData.append('content', document.querySelector('#messageForm textarea').value);

  fetch('/assets/server/send-message.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    console.log('Message sent:', response);
    loadMessages(); // ✅ Only call once
    document.querySelector('#messageForm textarea').value = '';
  });
});
</script>