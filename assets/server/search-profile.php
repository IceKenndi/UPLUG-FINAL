<?php
require __DIR__ . "/../config/dbconfig.php";

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
  echo "<p>Start typing to search users...</p>";
  exit;
}

$stmt = $conn->prepare("
  SELECT student_id AS id, full_name, profile_picture, 'Student' AS role, department FROM student_users WHERE full_name LIKE CONCAT('%', ?, '%')
  UNION
  SELECT faculty_id AS id, full_name, profile_picture, 'Faculty' AS role, department FROM faculty_users WHERE full_name LIKE CONCAT('%', ?, '%')
");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<p>No users found.</p>";
  exit;
}

while ($row = $result->fetch_assoc()) {
  $pfp = !empty($row['profile_picture']) ? $row['profile_picture'] : 'assets/images/default.png';
  echo '<div class="profile-card" onclick="viewProfile(\'' . $row['id'] . '\')">
            <img src="/' . htmlspecialchars($pfp) . '" alt="Profile Picture">
            <div class="profile-name">' . htmlspecialchars($row['full_name']) . '</div>
            <div class="profile-role">' . htmlspecialchars($row['role']) . '</div>
            <div class="profile-role">' . htmlspecialchars($row['department']) . '</div>
          </div>
        </div>';
}
$stmt->close();
?>
