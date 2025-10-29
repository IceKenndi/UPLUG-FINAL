<?php
require __DIR__ . "/../config/dbconfig.php";
session_start();

$targetUserId = $_GET['user_id'] ?? null;
if (!$targetUserId) {
  echo "No user specified.";
  exit();
}

// Get user info
if (strpos($targetUserId, 'FAC-') === 0) {
  $stmt = $conn->prepare("SELECT full_name, department, profile_picture FROM faculty_users WHERE faculty_id = ?");
} else if (strpos($targetUserId, 'STU-') === 0) {
  $stmt = $conn->prepare("SELECT full_name, department, profile_picture FROM student_users WHERE student_id = ?");
} else {
  echo "Invalid user ID.";
  exit();
}

$stmt->bind_param("s", $targetUserId);
$stmt->execute();
$stmt->bind_result($fullName, $department, $profilePicture);
$stmt->fetch();
$stmt->close();

// Get posts
$stmt = $conn->prepare("SELECT title, content, post_type, create_date, edited_at FROM posts WHERE author_id = ? ORDER BY COALESCE(edited_at, create_date) DESC");
$stmt->bind_param("s", $targetUserId);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Viewing Profile</title>
  <link rel="stylesheet" href="/assets/css/profile.css">
  <link rel="icon" href="/assets/images/client/UplugLogo.png" type="image/png">
</head>
<body>
  <nav class="navbar">
    <div class="nav-left">
      <div class="logo">U-Plug</div>
      <div class="nav-links">
        <a href="/home.php">Home</a>
        <a href="/news.php">News</a>
        <a href="/map.php">Map</a>
        <a href="/messaging.php">Messages</a>
        <a href="/profile.php">Profile</a>
        <a href="/assets/server/logout-process.php">Logout</a>
      </div>
    </div>
    <div class="nav-right">
      <div class="search-wrapper">
        <input type="text" id="searchInput" placeholder="Search by name..." autocomplete="off">
        <div id="searchResults"></div>
      </div>
    </div>
  </nav>


  <main class="dashboard">
    <!-- Sidebar -->
    <aside class="profile-sidebar">
      <div class="profile-pic-container">
        <img src="/<?= htmlspecialchars($profilePicture) . '?v=' . time() ?>" class="profile-pic">
      </div>
      <h2>Viewing Profile</h2>
      <p><strong>Account Number:</strong><br> <?= htmlspecialchars($targetUserId) ?></p>
      <p><strong>Name:</strong><br> <?= htmlspecialchars($fullName) ?></p>
      <?php switch ($department) {
            case "SHS":
              $department_full = "Senior High School - (SHS)";
              break;
            case "CITE":
              $department_full = "College of Information Technology Education - (CITE)";
              break;
            case "CCJE":
              $department_full = "College of Criminal Justice Education - (CCJE)";
              break;
            case "CAHS":
              $department_full = "College of Allied Health Sciences - (CAHS)";
              break;
            case "CAS":
              $department_full = "College of Arts and Sciences - (CAS)";
              break;
            case "CEA":
              $department_full = "College of Engineering and Architecture - (CEA)";
              break;
            case "CELA":
              $department_full = "College of Education and Liberal Arts - (CELA)";
              break;
            case "CMA":
              $department_full = "College of Management and Accountancy - (CMA)";
              break;
            case "COL":
              $department_full = "College of Law - (COL)";
              break;
            default:
              $department_full = "Unknown Department";
              break;
      }?>
      <p><strong>Department:</strong><br> <?= htmlspecialchars($department_full) ?></p>
      <p><strong>Role:</strong><br> <?= strpos($targetUserId, 'FAC-') === 0 ? 'Faculty' : 'Student' ?></p>
      <form method="POST" action="/messaging.php">
        <input type="hidden" name="recipient_id" value="<?= htmlspecialchars($targetUserId) ?>">
        <button type="submit" class="message-btn">💬 Message This User</button>
      </form>
    </aside>

    <!-- Feed Section -->
    <section class="feed-content">
      <h2><?= htmlspecialchars($fullName) ?>’s Posts</h2>
      <?php foreach ($posts as $post): ?>
        <div class="post-card">
          <div class="post">
            <p><strong>TYPE:</strong> <?= ucfirst($post['post_type']) ?></p>
            <p><strong>TITLE:</strong> <?= htmlspecialchars($post['title']) ?></p>
            <p><?= htmlspecialchars($post['content']) ?></p>
            <small>
              <?= empty($post['edited_at'])
                ? date("F j, Y - h:i A", strtotime($post['create_date']))
                : "Edited at: " . date("F j, Y - h:i A", strtotime($post['edited_at'])) ?>
            </small>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  </main>
  <script>
  const searchInput = document.getElementById('searchInput');
  const searchResults = document.getElementById('searchResults');

  searchInput.addEventListener('input', function () {
    const query = this.value.trim();

    if (query.length === 0) {
      searchResults.style.display = 'none';
      searchResults.innerHTML = '';
      return;
    }

    fetch('search-profile.php?q=' + encodeURIComponent(query))
      .then(res => res.text())
      .then(html => {
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
      });
  });
  </script>

  <script>
  function viewProfile(userId) {
    window.location.href = 'view-profile.php?user_id=' + encodeURIComponent(userId);
  }
  </script>


</body>
</html>
