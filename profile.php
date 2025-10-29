<?php
session_start();

if (isset($_SESSION['user_id'])) {

  if ($_SESSION['user_id'] == 1) {
    header("Location: /admin/admin.php");
    exit();
  }

  require __DIR__ . "/assets/config/dbconfig.php";
} else {
  header("Location: index.php");
  exit();
}

$toastPosts = $toastPosts ?? [];

if (!empty($_SESSION['toastPosts'])) {
  $toastPosts = array_merge($_SESSION['toastPosts'], $toastPosts);
  unset($_SESSION['toastPosts']);
}



$session_id = $_SESSION['user_id'] ?? null;


$stmt = $conn->prepare("SELECT post_id, title, create_date, edited_at, toast_status, toast_message FROM posts WHERE toast_status = 1 AND author_id != ?");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$result = $stmt->get_result();

$pushToasts = [];
while ($row = $result->fetch_assoc()) {
  $pushToasts[] = $row;
}


$toastPosts = array_merge($toastPosts, $pushToasts);




if (strpos($session_id, 'FAC-') === 0) {
  $sql = "SELECT * FROM faculty_users WHERE faculty_id = '$session_id'";
  $result = $conn->query($sql);
  $user = $result->fetch_assoc();
  $department_code = $user['department'];
  $role = 'Faculty';
  $reset_role = 'faculty';
  $pfpDir = 'faculty-profiles';
} else if (strpos($session_id, 'STU-') === 0) {
  $sql = "SELECT * FROM student_users WHERE student_id = '$session_id'";
  $result = $conn->query($sql);
  $user = $result->fetch_assoc();
  $department_code = $user['department'];
  $role = 'Student';
  $reset_role = 'student';
  $pfpDir = 'student-profiles';
}
?>


<?php
$posts = [];
$personalPosts = [];

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

    if ($post['post_type'] === 'personal'){
      $post['post_type'] = 'Personal';
    } else if ($post['post_type'] === 'official'){
      $post['post_type'] = 'Official';
    } else if ($post['post_type'] === 'department'){
      $post['post_type'] = 'Department';
    } else {
      $post['post_type'] = 'Unknown';
    }

  if ($post['author_id'] === $session_id){
      $personalPosts[] = $post;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <link rel="stylesheet" href="assets/css/profile.css">
  <link rel="icon" href="assets/images/client/UplugLogo.png" type="image/png">
  <script src="assets/javascript/toast-notif.js" defer></script>
</head>
<body>

<div id="toastContainer" class="toast-container">
  <?php foreach ($toastPosts as $post): ?>
    <div class="toast <?= $post['type'] ?? 'info' ?>" data-post-id="<?= $post['post_id'] ?>">
      <span><?= htmlspecialchars($post['toast_message']) ?></span><br>

      <?php if (!empty($post['create_date']) || !empty($post['edited_at'])): ?>
        <small class="toast-timestamp" style="opacity: 0.8;">
          <?= !empty($post['edited_at'])
            ? 'Edited at: ' . date("F j, Y - h:i A", strtotime($post['edited_at']))
            : 'Posted: ' . date("F j, Y - h:i A", strtotime($post['create_date'])) ?>
        </small>
      <?php endif; ?>
      
      <button class="dismiss-toast">X</button>
    </div>
  <?php endforeach; ?>
</div>

<div id="newPostModal" class="modal">
  <div class="modal-content">
    <button class="close-btn" type="button" onclick="closeModal()">✕</button>
    <h3>Create New Post</h3>
    <form method="POST" action="assets/server/create-post.php" autocomplete="off">
      <input type="hidden" name="post_type" value="personal">

      <label for="post_title">Title:</label>
      <textarea id="title" name="title" rows="1" required></textarea>

      <label for="post_content">Content:</label>
      <textarea id="content" name="content" rows="4" required></textarea>

      <button type="submit" name="submit_post">Post</button>
    </form>
  </div>
</div>

  <nav class="navbar">
    <div class="nav-left">
      <div class="logo">U-Plug</div>
      <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="news.php">News</a>
        <a href="map.php">Map</a>
        <a href="messaging.php">Messages</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="#" id="logoutBtn" class="logout-link">Logout</a>
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
<aside class="profile-sidebar">
  <?php
  $pfpPath = !empty($user['profile_picture'])
      ? '/' . htmlspecialchars($user['profile_picture'])
      : '/images/default.png';
  ?>
  
 <div class="profile-pic-container">
  <img src="<?= $pfpPath . '?v=' . time() ?>" class="profile-pic" alt="Profile Picture">
    
<form method="POST" enctype="multipart/form-data" action="assets/server/upload-profile.php" class="upload-form" id="upload-form">
  <button type="button" id="openDropzoneBtn" class="open-dropzone-btn">📸 Upload Photo</button>

  <!-- Step 2: Hidden dropzone container -->
  <div class="dropzone-container" id="dropzoneContainer" style="display:none;">
    <div class="dropzone" id="dropzone" role="button" tabindex="0">
      <p>Drag and drop your <strong>PNG</strong> photo<br><em>or click to browse</em></p>
      <p class="file-hint">Only PNG files are accepted (max 40MB)</p>
      <input type="file" id="profile_picture" name="profile_picture" accept="image/png" hidden required>
      <button type="button" class="close-dropzone" id="closeDropzone">✕</button>
    </div>
  </div>

  <span id="file-chosen" class="file-chosen">No file chosen</span>

  <input type="hidden" name="user_id" value="<?= $session_id ?>">
  <input type="hidden" name="department_code" value="<?= $department_code ?>">
  <input type="hidden" name="pfp_folder" value="<?= $pfpDir ?>">
  <input type="hidden" name="role" value="<?= $role ?>">

  <button type="submit" class="upload-btn" id="upload-btn" style="display:none;">Upload</button>
</form>



  <h2>Profile</h2>
  <p><strong>Account Number:</strong><br><?= htmlspecialchars($session_id) ?></p>
  <p><strong>Name:</strong><br><?= htmlspecialchars($user['full_name']) ?></p>

  <?php
  switch ($department_code) {
    case "SHS": $department_full = "Senior High School - (SHS)"; break;
    case "CITE": $department_full = "College of Information Technology Education - (CITE)"; break;
    case "CCJE": $department_full = "College of Criminal Justice Education - (CCJE)"; break;
    case "CAHS": $department_full = "College of Allied Health Sciences - (CAHS)"; break;
    case "CAS": $department_full = "College of Arts and Sciences - (CAS)"; break;
    case "CEA": $department_full = "College of Engineering and Architecture - (CEA)"; break;
    case "CELA": $department_full = "College of Education and Liberal Arts - (CELA)"; break;
    case "CMA": $department_full = "College of Management and Accountancy - (CMA)"; break;
    case "COL": $department_full = "College of Law - (COL)"; break;
    default: $department_full = "Unknown Department"; break;
  }
  ?>

  <p><strong>Department:</strong><br><?= htmlspecialchars($department_full) ?></p>
  <p><strong>Role:</strong><br><?= htmlspecialchars($role) ?></p>

  <form method="POST" action="assets/server/change-password-request.php" class="password-form">
    <input type="hidden" name="user_id" value="<?= $session_id ?>">
    <input type="hidden" name="reset_role" value="<?= $reset_role ?>">
    <input type="hidden" name="email" value="<?= $user['email'] ?>">
    <button type="submit" class="password-btn">🔒 Change Password</button>
  </form>

  <div class="profile-image-preview">
    <img id="preview-img" src="#" alt="Profile Preview" style="display:none;">
  </div>
  </aside>

  <!-- Feed Section -->
  <section class="feed-content">
    <div class="newsfeed">
      <div class="new-post-button">
        <button onclick="openModal()">➕ New Post</button>
      </div>
      
      <h2>Your Posts</h2>

      <?php if (isset($error_message)): ?>
        <p style="color:red;"><?= $error_message ?></p>
      <?php endif; ?>

    <?php foreach ($personalPosts as $post): ?>
        <div class="post-card" data-post-id="<?= $post['post_id'] ?>">
        <div class="post" data-post-id="<?= htmlspecialchars($post['post_id']) ?>">
          <?php if ($post['author_id'] === $_SESSION['user_id']): ?>
            <div class="post-actions">
              <button type="button"
                      class="edit-btn"
                      data-post-id="<?= htmlspecialchars($post['post_id']) ?>"
                      data-title="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
                      data-content="<?= htmlspecialchars($post['content'], ENT_QUOTES) ?>">
                Edit
              </button>

              <!-- open floating delete confirm modal -->
              <button type="button"
                      class="delete-btn danger"
                      data-post-id="<?= htmlspecialchars($post['post_id']) ?>">
                Delete
              </button>
            </div>
          <?php endif; ?>

          <p><strong>POST TYPE:</strong> <?= htmlspecialchars($post['post_type']) ?></p>
          <p><strong>TITLE:</strong> <?= htmlspecialchars($post['title'])?></p>
          <p><?= htmlspecialchars($post['content'])?></p>
          <small title="Originally posted: <?= date("F j, Y - h:i A", strtotime($post['create_date']))?>">
            <?= (empty($post['edited_at']))
            ? date("F j, Y - h:i A", strtotime($post['create_date']))
            : "Edited at: " . date("F j, Y - h:i A", strtotime($post['edited_at']))?>
          </small>
        </div>
      <?php endforeach; ?>

    <footer class="footer-tag">
      <p>Logged in as <?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($_SESSION['user_id']) ?>)</p>
    </footer>
  </section>
</main>


  <script>
    const modal = document.getElementById("newPostModal");
    const titleField = document.getElementById("post_title");
    const contentField = document.getElementById("post_content");

    function openModal() {
      modal.classList.add("show");
      document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
      modal.classList.remove("show");
      document.body.style.overflow = 'auto';
    }

    window.addEventListener('click', function(event) {
      if (event.target === modal) closeModal();
    });

    function autoExpand(field) {
      if (!field) return;
      field.style.height = 'auto';
      field.style.height = field.scrollHeight + 'px';
    }

    window.addEventListener('DOMContentLoaded', () => {
      autoExpand(titleField);
      autoExpand(contentField);
    });
  </script>

  <!-- EDIT MODAL (identical structure to news.php) -->
  <div id="editPostModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <button type="button" class="close-btn" aria-label="Close" onclick="closeEditModal()">✕</button>
      <h3>Edit Post</h3>
      <form id="editPostForm" method="POST" action="assets/server/edit-post.php" autocomplete="off" class="post-form">
        <input type="hidden" name="post_id" id="edit_post_id">
        <input type="hidden" name="origin" value="profile">
        <input type="hidden" name="post_type" value="<?= $post['post_type'] ?>">
        
        <label for="edit_title">Title:</label>
        <textarea id="edit_title" name="title" rows="1" required></textarea>

        <label for="edit_content">Content:</label>
        <textarea id="edit_content" name="content" rows="4" required></textarea>

        <button type="submit" name="save_edit" class="create-post-btn">Save</button>
        <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
      </form>
    </div>
  </div>

  <div id="deleteConfirmModal" class="modal" aria-hidden="true">
  <div class="modal-content">
    <button type="button" class="close-btn" aria-label="Close" onclick="closeDeleteModal()">✕</button>
    <h3>Confirm Delete</h3>
    <p>Are you sure you want to delete this post?</p>
    <form id="deleteForm" method="POST" action="assets/server/delete-post.php">
      <input type="hidden" name="redirect_to" value="profile">
      <input type="hidden" name="post_id" id="delete_post_id">
      <button type="submit" class="confirm-btn">Confirm</button>
      <button type="button" class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
    </form>
  </div>
</div>


<script>
  // EDIT POST MODAL
  const editModal = document.getElementById("editPostModal");
  const editIdField = document.getElementById("edit_post_id");
  const editTitleField = document.getElementById("edit_title");
  const editContentField = document.getElementById("edit_content");

  function openEditModal(postId, title, content) {
    editIdField.value = postId || '';
    editTitleField.value = title || '';
    editContentField.value = content || '';
    try { editTitleField.style.height = 'auto'; editTitleField.style.height = editTitleField.scrollHeight + 'px'; } catch(e){}
    try { editContentField.style.height = 'auto'; editContentField.style.height = editContentField.scrollHeight + 'px'; } catch(e){}
    editModal.classList.add("show");
    document.body.style.overflow = 'hidden';
  }

  function closeEditModal() {
    editModal.classList.remove("show");
    document.body.style.overflow = 'auto';
  }

  document.addEventListener("click", function(e) {
    const btn = e.target.closest('.edit-btn');
    if (!btn) return;
    openEditModal(btn.dataset.postId, btn.dataset.title || '', btn.dataset.content || '');
  });

  document.addEventListener('click', (ev) => {
    if (editModal && editModal.classList.contains('show') && ev.target === editModal) closeEditModal();
  });

  document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape') closeEditModal();
  });

  window.closeEditModal = closeEditModal;


  // DELETE POST MODAL

  (function () {
    const deleteModal = document.getElementById('deleteConfirmModal');
    const deleteIdField = document.getElementById('delete_post_id');
    const deleteForm = document.getElementById('deleteForm');

    if (!deleteModal || !deleteIdField || !deleteForm) return;

    function openDeleteModal(postId) {
      deleteIdField.value = postId || '';
      deleteModal.classList.add('show');
      document.body.style.overflow = 'hidden';
      const confirmBtn = deleteModal.querySelector('.confirm-btn');
      if (confirmBtn) confirmBtn.focus();
    }

    function closeDeleteModal() {
      deleteModal.classList.remove('show');
      document.body.style.overflow = 'auto';
      deleteIdField.value = '';
    }

    window.closeDeleteModal = closeDeleteModal;

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.delete-btn');
      if (btn) {
        if (deleteModal.contains(btn)) return;
        openDeleteModal(btn.dataset.postId || '');
      }
    });

    deleteModal.addEventListener('click', function (ev) {
      if (ev.target === deleteModal) closeDeleteModal();
    });

    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && deleteModal.classList.contains('show')) {
        closeDeleteModal();
      }
    });
  })();




  // PROFILE PICTURE UPLOAD — Upload button + Drag & Drop + PNG validation
  document.addEventListener("DOMContentLoaded", function() {
    const dropzoneContainer = document.getElementById("dropzoneContainer");
    const dropzone = document.getElementById("dropzone");
    const openBtn = document.getElementById("openDropzoneBtn");
    const closeBtn = document.getElementById("closeDropzone");
    const fileInput = document.getElementById("profile_picture");
    const fileChosen = document.getElementById("file-chosen");
    const uploadBtn = document.getElementById("upload-btn");

    if (!dropzoneContainer || !openBtn || !closeBtn || !fileInput) return;

    // 🧩 Prevent native double-triggering
    fileInput.style.pointerEvents = "none";
    fileInput.addEventListener("click", (e) => e.stopPropagation());

    // === Toggle Dropzone Visibility ===
    openBtn.addEventListener("click", () => {
      dropzoneContainer.style.display = "block";
      openBtn.style.display = "none";
      dropzoneContainer.classList.add("fade-in");
    });

    // === Close button ===
    closeBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      dropzoneContainer.style.display = "none";
      openBtn.style.display = "inline-block";
      resetFile();
    });

    // === Prevent double dialogs ===
    let fileDialogOpen = false;

    dropzone.addEventListener("click", (e) => {
      if (e.target.closest(".close-dropzone")) return;
      if (e.target === fileInput) return;
      if (fileDialogOpen) return;

      fileDialogOpen = true;
      fileInput.click();
      setTimeout(() => (fileDialogOpen = false), 500);
    });

    // === Show/Reset File Name ===
    function showFile(file) {
      fileChosen.textContent = file.name;
      fileChosen.classList.add("active");
      uploadBtn.style.display = "inline-block";
    }

    function resetFile() {
      fileChosen.textContent = "No file chosen";
      fileChosen.classList.remove("active");
      uploadBtn.style.display = "none";
    }

    // === Drag and Drop Support ===
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt =>
      dropzone.addEventListener(evt, e => e.preventDefault())
    );

    dropzone.addEventListener("dragover", () => dropzone.classList.add("dragover"));
    dropzone.addEventListener("dragleave", () => dropzone.classList.remove("dragover"));
    dropzone.addEventListener("drop", (e) => {
      dropzone.classList.remove("dragover");
      const file = e.dataTransfer.files[0];
      if (!file) return;

      if (file.type !== "image/png") {
        alert("Only PNG files are allowed.");
        resetFile();
        return;
      }
      if (file.size > 40 * 1024 * 1024) {
        alert("File is too large. Max 40MB.");
        resetFile();
        return;
      }

      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file);
      fileInput.files = dataTransfer.files;
      showFile(file);
    });

    // === Handle File Input Change ===
    fileInput.addEventListener("change", function() {
      if (this.files && this.files.length > 0) {
        const file = this.files[0];
        if (file.type !== "image/png") {
          alert("Only PNG files are allowed.");
          this.value = "";
          resetFile();
          return;
        }
        if (file.size > 40 * 1024 * 1024) {
          alert("File too large. Max 40MB.");
          this.value = "";
          resetFile();
          return;
        }
        showFile(file);
      } else {
        resetFile();
      }
    });
  });
</script>

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
    
<div id="toastContainer" class="toast-container"></div>
<?php include 'assets/server/logout-modal.php'; ?>
</body>
</html>
