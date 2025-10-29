<?php
require __DIR__ . "/../config/dbconfig.php";

$userId = $_GET['id'] ?? '';
$userId = trim($userId);

if (strpos($userId, 'STU-') === 0) {
  $stmt = $conn->prepare("SELECT full_name, profile_picture, email FROM student_users WHERE student_id = ?");
} elseif (strpos($userId, 'FAC-') === 0) {
  $stmt = $conn->prepare("SELECT full_name, profile_picture, email FROM faculty_users WHERE faculty_id = ?");
} else {
  echo "<p>Invalid user ID.</p>";
  exit;
}

$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<p>User not found.</p>";
  exit;
}

$user = $result->fetch_assoc();
$pfp = !empty($user['profile_picture']) ? $user['profile_picture'] : 'assets/images/default.png';

echo '<div class="profile-details">
        <img src="/' . htmlspecialchars($pfp) . '" alt="Profile Picture">
        <h2>' . htmlspecialchars($user['full_name']) . '</h2>
        <p>Email: ' . htmlspecialchars($user['email']) . '</p>
      </div>';
$stmt->close();
?>
