<?php

session_start();

if (isset($_SESSION['user_id'])) {
  // Redirect admin to admin dashboard
  if ($_SESSION['user_id'] == 1) {
    header("Location: /admin/admin.php");
    exit();
  }

  require __DIR__ . "/assets/config/dbconfig.php";
} else {
  header("Location: index.php");
  exit();
}

?>

<!-- Welcome Logo Section -->
<?php
  $departmentCode = $_SESSION['department_code'] ?? 'default';
  if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome']):
?>
    <div id="welcome-overlay">
      <div class="logo-container">
        <img src="assets/images/client/department/<?= htmlspecialchars($departmentCode) ?>.png" alt="<?= htmlspecialchars($departmentCode) ?> Logo">
      </div>
      <div class="color-fade-bg"></div>
    </div>

  <script>
  setTimeout(() => {
    const overlay = document.getElementById('welcome-overlay');
    if (overlay) overlay.remove();
  }, 3500); // after full animation
</script>
<?php
  unset($_SESSION['show_welcome']);
endif;
?>


<!-- Post Fetching Section -->
<?php
$posts = [];
$officialStudentPosts = [];
$officialFacultyPosts = [];

$sql = "SELECT * FROM posts ORDER BY COALESCE(edited_at, create_date) DESC";
$result = $conn->query($sql);

while ($post = $result->fetch_assoc()){
  $authorId = $post['author_id'];

  if (strpos($authorId, 'STU-') === 0){
    $stmt = $conn->prepare("SELECT full_name, department FROM student_users WHERE student_id = ?");
  } else if (strpos($authorId, 'FAC-') === 0){
    $stmt = $conn->prepare("SELECT full_name, department FROM faculty_users WHERE faculty_id = ?");
  } else {
    $post['authorName'] = 'Unknown';
    $post['authorRole'] = 'Unknown';
    $post['authorDept'] = 'Unknown';
    continue;
  }

  $stmt->bind_param("s", $authorId);
  $stmt->execute();
  $stmt->bind_result($authorName, $authorDept);
  $stmt->fetch();
  $stmt->close();

  $post['authorName'] = $authorName;
  $post['authorRole'] = strpos($authorId, 'STU-') === 0 ? 'Student' : 'Faculty';
  $post['authorDept'] = $authorDept;

  if ($post['post_type'] === 'official'){
      if ($post['authorRole'] === 'Faculty'){
        $officialFacultyPosts[] = $post;
      } else if ($post['authorRole'] === 'Student'){
        $officialStudentPosts[] = $post;
      }
  }
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>U-Plug</title>
  <link rel="stylesheet" href="assets/css/home.css">
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="icon" href="assets/images/client/UplugLogo.png" type="image/png">
  <script src="assets/javascript/toast-notif.js" defer></script>
</head>

<body>



  <nav class="navbar">
    <div class="nav-left">
      <div class="logo">U-Plug</div>
      <div class="nav-links">
        <a href="home.php" class="active">Home</a>
        <a href="news.php">News</a>
        <a href="map.php">Map</a>
        <a href="messaging.php">Messages</a>
        <a href="profile.php">Profile</a>
        <a href="#" id="logoutBtn" class="logout-link">Logout</a>
      </div>
    </div>
    <div class="nav-right">
      <div class="search-wrapper">
        <input type="text" id="searchInput" placeholder="Search profiles by name..." autocomplete="off">
        <div id="searchResults"></div>
      </div>
    </div>
  </nav>
  <div class="container">
    <main class="main-content">
      <section class="news-feed">
        <h2>News Feed</h2>
        <div class="tabs">
          <button class="tab active">Faculty News</button>
          <button class="tab">Student News</button>
        </div>
        <div id="officialFaculty" class="news-items">

          <?php foreach ($officialFacultyPosts as $post): ?>
              <div class="news-item" data-post-id="<?= $post['post_id'] ?>">
            <img src="assets/images/client/department/<?= htmlspecialchars($post['authorDept']) ?>.png" alt="<?= htmlspecialchars($post['authorDept']) ?> Logo">
            <div>
              <h3><?= htmlspecialchars($post['authorName']) . " - " . htmlspecialchars($post['authorDept'])?></h3>
              <h3><?= htmlspecialchars($post['title']) ?></h3>
              <p><?= htmlspecialchars($post['content']) ?></p>
              <small title="Originally posted: <?= date("F j, Y - h:i A", strtotime($post['create_date']))?>">
                <?= (empty($post['edited_at']))
                ? date("F j, Y - h:i A", strtotime($post['create_date']))
                : "Edited at: " . date("F j, Y - h:i A", strtotime($post['edited_at']))?>
              </small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div id="officialStudent" class="news-items hidden">

          <?php foreach ($officialStudentPosts as $post): ?>
              <div class="news-item" data-post-id="<?= $post['post_id'] ?>">
            <img src="assets/images/client/department/<?= htmlspecialchars($post['authorDept']) ?>.png" alt="<?= htmlspecialchars($post['authorDept']) ?> Logo">
            <div>
              <h3><?= htmlspecialchars($post['authorName']) . " - " . htmlspecialchars($post['authorDept'])?></h3>
              <h3><?= htmlspecialchars($post['title']) ?></h3>
              <p><?= htmlspecialchars($post['content']) ?></p>
              <small title="Originally posted: <?= date("F j, Y - h:i A", strtotime($post['create_date']))?>">
                <?= (empty($post['edited_at']))
                ? date("F j, Y - h:i A", strtotime($post['create_date']))
                : "Edited at: " . date("F j, Y - h:i A", strtotime($post['edited_at']))?>
              </small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        
      </section>

      <aside class="right-panel">
              <div class="map-section">
                <h2>University Map</h2>
            <div class="map-placeholder">
               <iframe
                 class="campus-map-embed"
                 src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d958.5898478605993!2d120.3415839905081!3d16.04682954919672!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339167fe6bba4d67%3A0xf54b516c2c5d10b6!2sPHINMA-University%20of%20Pangasinan!5e0!3m2!1sen!2sph!4v1761753858071!5m2!1sen!2sph"
                 allowfullscreen=""
                 loading="lazy"
                 referrerpolicy="no-referrer-when-downgrade">
               </iframe>
          
          </div>
        </div>
        <!-- DYNAMIC MESSAGING SYS -->
              <div class="messaging-section">
                <h2>Recent Messages</h2>
                <div class="chat">
                  <?php if (!empty($recentMessages)): ?>
                    <?php foreach ($recentMessages as $msg): ?>
                      <div class="message <?= ($msg['sender_id'] === $currentUser) ? 'from-user' : 'from-other' ?>">
                        <p><strong><?= htmlspecialchars($msg['sender_name'] ?? $msg['sender_id']) ?></strong></p>
                        <p><?= htmlspecialchars($msg['content']) ?></p>
                        <small><?= date('M j, Y g:i A', strtotime($msg['sent_at'])) ?></small>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p style="opacity: 0.7;">No recent messages.</p>
                  <?php endif; ?>
                </div>
              </div>



          <!-- PROFILE SECTION -->
    <div class="profile-section">
    <h2>Profile</h2>


  <?php
  $currentUser = $_SESSION['user_id'] ?? null;
  $profileImage = '/assets/images/default-profile.png'; 
  $displayName = 'Unknown User';
  $accountNumber = '—';
  $departmentLabel = 'N/A';
  $roleLabel = 'N/A';

  if ($currentUser && isset($conn)) {
    if (strpos($currentUser, 'FAC-') === 0) {
      $stmt = $conn->prepare("SELECT full_name, faculty_id AS id, department, profile_picture FROM faculty_users WHERE faculty_id = ?");
      $roleLabel = 'Faculty';
    } elseif (strpos($currentUser, 'STU-') === 0) {
      $stmt = $conn->prepare("SELECT full_name, student_id AS id, department, profile_picture FROM student_users WHERE student_id = ?");
      $roleLabel = 'Student';
    } else {
      $stmt = null;
    }

    if (!empty($stmt)) {
      $stmt->bind_param("s", $currentUser);
      $stmt->execute();
      $stmt->bind_result($f_name, $f_id, $f_dept, $f_photo);
      if ($stmt->fetch()) {
        $displayName = $f_name ?: $displayName;
        $accountNumber = $f_id ?: $accountNumber;
        $departmentLabel = $f_dept ?: $departmentLabel;
        if (!empty($f_photo)) {
          $profileImage = (strpos($f_photo, '/') === 0) ? $f_photo : '/' . ltrim($f_photo, '/');
        }
      }
      $stmt->close();
    }
  }
  ?>

  <div class="profile-card-preview profile-card-dynamic">
    <img
      src="<?= htmlspecialchars($profileImage) ?>"
      alt="Profile"
      onerror="this.onerror=null;this.src='/assets/images/default-profile.png';"
      class="profile-preview-img">
    
    <h3><?= htmlspecialchars($displayName) ?></h3>

    <div class="profile-info-rows">
      <div><strong>Account Number:</strong> <?= htmlspecialchars($accountNumber) ?></div>
      <div><strong>Department:</strong> <?= htmlspecialchars($departmentLabel) ?></div>
      <div><strong>Role:</strong> <?= htmlspecialchars($roleLabel) ?></div>
    </div>
  </div>
</div>

  <script>

    
    document.addEventListener('DOMContentLoaded', () => {
      const tabs = document.querySelectorAll('.tab');
      tabs[0].addEventListener('click', () => showSection('officialFaculty'));
      tabs[1].addEventListener('click', () => showSection('officialStudent'));
    })


    function showSection(section) {
      document.getElementById('officialFaculty').classList.add('hidden');
      document.getElementById('officialStudent').classList.add('hidden');
      document.getElementById(section).classList.remove('hidden');
      document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
      if(section === 'officialFaculty') {
        document.querySelectorAll('.tab')[0].classList.add('active');
      } else {
        document.querySelectorAll('.tab')[1].classList.add('active');
      }
    }

  </script>

  <!-- SEARCH PROFILE -->

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

      fetch('assets/server/search-profile.php?q=' + encodeURIComponent(query))
        .then(res => res.text())
        .then(html => {
          searchResults.innerHTML = html;
          searchResults.style.display = 'block';
        });
    });
</script>

<script>
    function viewProfile(userId) {
      window.location.href = 'assets/server/view-profile.php?user_id=' + encodeURIComponent(userId);
    }
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const chatContainer = document.querySelector(".chat");

  async function loadMessages() {
    try {
      const response = await fetch("assets/server/fetch-messages.php");
      const html = await response.text();
      chatContainer.innerHTML = html;
    } catch (error) {
      console.error("Error loading messages:", error);
    }
  }

  // Initial load
  loadMessages();

  // Refresh every 10 seconds
  setInterval(loadMessages, 10000);
});
</script>

<script>
fetch('assets/server/load-toasts.php')
  .then(res => res.json())
  .then(data => {
    if (Array.isArray(data)) {
      data.forEach(toast => {
        showToast(toast.message, toast.type, 'poll', toast.link);

        // Mark as acknowledged immediately
        fetch('assets/server/ack-toast.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `message=${encodeURIComponent(toast.message)}`
        });
      });
    }
  });
</script>
<div id="toastContainer" class="toast-container"></div>
<?php include 'assets/server/logout-modal.php'; ?>
</body>
</html>

