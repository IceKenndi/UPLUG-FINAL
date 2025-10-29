<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
  header("Location: /../home.php");
  exit();
}

require __DIR__ . "/../assets/config/dbconfig.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>U-Plug Admin Dashboard</title>
  <link rel="stylesheet" href="/assets/css/admin-dashboard.css">
  <link rel="icon" href="/assets/images/client/UplugLogo.png" type="image/png">
</head>
<body>

<div class="layout" id="layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="logo">Administrator</div>
    <ul class="nav">
      <li><a href="admin.php" class="nav-link">Dashboard</a></li>
      <li class="divider">User Settings</li>
      <li><a href="faculty.php" class="nav-link">Faculty Users</a></li>
      <li><a href="student.php" class="nav-link">Student Users</a></li>
      <li><a href="posts.php" class="nav-link">Posts</a></li>
      <li class="divider">Settings</li>
      <li><a href="#" class="nav-link">About</a></li>
      <li><a href="/assets/server/logout-process.php" class="nav-link">Logout</a></li>
    </ul>
  </aside>

  <!-- Burger Button -->
  <button id="burger" class="burger">&#9776;</button>

  <!-- Main Content -->
  <main class="main-content" id="main">
    <section class="summary-cards">
      <div class="card">
        <h3>Posts Today</h3>
        <p id="post-count">0</p>
      </div>
      <div class="card">
        <h3>New Users</h3>
        <p id="user-count">0</p>
      </div>
    </section>

    <!-- Recent Posts -->
    <section class="panel" id="recent-posts-panel">
      <div class="panel-header">
        <h2>Recent Posts</h2>
        <button class="expand-btn" data-target="recent-posts">＋</button>
      </div>
      <ul class="list collapsible" id="recent-posts"></ul>
    </section>

    <!-- New Users -->
    <section class="panel" id="new-users-panel">
      <div class="panel-header">
        <h2>New Users</h2>
        <button class="expand-btn" data-target="new-users">＋</button>
      </div>
      <ul class="list collapsible" id="new-users"></ul>
    </section>

    <div class="footer">
      U-Plug ©2025. All rights reserved.
    </div>
  </main>
</div>

<!-- Modals -->
<div id="view-post-modal" class="modal">
  <div class="modal-content large">
    <span class="close-btn">&times;</span>
    <h3>Post Details</h3>
    <p><strong>Author:</strong> <span id="view-post-author">—</span></p>
    <p><strong>Title:</strong> <span id="view-post-title">—</span></p>
    <p><strong>Content:</strong></p>
    <p id="view-post-content">—</p>
    <p><strong>Date:</strong> <span id="view-post-date">—</span></p>
  </div>
</div>

<div id="delete-modal" class="modal">
  <div class="modal-content small">
    <h3>Confirm Deletion</h3>
    <p>Are you sure you want to delete this entry?</p>
    <div class="modal-actions">
      <button id="confirm-delete">Yes</button>
      <button id="cancel-delete">No</button>
    </div>
  </div>
</div>

<div id="view-user-modal" class="modal">
  <div class="modal-content large">
    <span class="close-btn">&times;</span>
    <h3>User Details</h3>
    <p><strong>ID:</strong> <span id="view-user-id">—</span></p>
    <p><strong>Profile Name:</strong> <span id="view-user-name">—</span></p>
    <p><strong>Email:</strong> <span id="view-user-email">—</span></p>
    <p><strong>Department:</strong> <span id="view-user-department">—</span></p>
    <p><strong>Joined:</strong> <span id="view-user-date">—</span></p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const burger = document.getElementById('burger');
  const sidebar = document.getElementById('sidebar');
  const postList = document.getElementById("recent-posts");
  const userList = document.getElementById("new-users");
  const deleteModal = document.getElementById("delete-modal");
  const confirmBtn = document.getElementById("confirm-delete");
  const cancelBtn = document.getElementById("cancel-delete");

  let itemToDelete = null;

  // Sidebar active link
  const currentPage = window.location.pathname.split("/").pop() || "admin.html";
  document.querySelectorAll(".nav-link").forEach(link => {
    link.classList.toggle("active", link.getAttribute("href") === currentPage);
  });

  burger.addEventListener('click', () => sidebar.classList.toggle('hidden'));

  // Expand buttons
  document.querySelectorAll('.expand-btn').forEach(button => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-target');
      const panel = document.getElementById(`${targetId}-panel`);
      panel && panel.classList.toggle('expanded');
      button.classList.toggle('expanded');
      button.textContent = button.classList.contains('expanded') ? '−' : '＋';
    });
  });

  // Fetch dashboard data
  fetch("./server/admin-dashboard-data.php")
    .then(async res => {
      const text = await res.text();
      try { return JSON.parse(text); } 
      catch (err) { console.error("Raw response:", text); throw err; }
    })
    .then(data => {
      const recentPosts = data.posts || [];
      const allUsers = data.users || [];

      const now = new Date();

      // Posts today
      const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const todayEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
      const todayPosts = recentPosts.filter(p => {
        const postDate = new Date(p.create_date || p.created_at);
        return postDate >= todayStart && postDate < todayEnd;
      });
      document.getElementById("post-count").textContent = todayPosts.length;

      // New users last 24h
      const yesterday = new Date(now.getTime() - 24*60*60*1000);
      const newUsers24h = allUsers.filter(u => {
        const userDate = new Date(u.create_date || u.joined_at);
        return userDate >= yesterday && userDate <= now;
      });
      document.getElementById("user-count").textContent = newUsers24h.length;

      // Render posts
      postList.innerHTML = "";
      recentPosts.forEach(post => {
        const li = document.createElement("li");
        let authorName = post.author_name || post.author_id;
        let role = "";
        if (post.author_id.startsWith("STU-")) role = "Student";
        else if (post.author_id.startsWith("FAC-")) role = "Faculty";
        const label = `[${post.department || ""} ${role}]`;
        li.innerHTML = `
          <div>
            <strong>${label}</strong>
            ${authorName} — <em>${post.title || ""}</em><br>
            ${(post.content || "").substring(0,80)}...<br>
            <small>${post.create_date || post.created_at || ""}</small>
          </div>
          <div class="actions">
            <button class="edit-btn" data-type="post" data-item='${JSON.stringify({...post, author_name: authorName})}'>View</button>
            <button class="delete-btn" data-type="post" data-id='${post.post_id}'>Delete</button>
          </div>
        `;
        postList.appendChild(li);
      });

      // Render users
      userList.innerHTML = "";
      allUsers.forEach(user => {
        const typeLabel = (user.type === "student")
          ? `${user.department || ""} Student`
          : (user.type === "faculty")
            ? `${user.department || ""} Faculty`
            : "";
        const li = document.createElement("li");
        li.innerHTML = `
          <div>
            <strong>${typeLabel}</strong> | ${user.name || ""} | ${user.email || ""} | ${user.create_date || ""} 
          </div>
          <div class="actions">
            <button class="edit-btn" data-type="user" data-item='${JSON.stringify(user)}'>View</button>
            <button class="delete-btn" data-type="user" data-id='${user.seq_id}'>Delete</button>
          </div>
        `;
        userList.appendChild(li);
      });

      // Delegated click events
      [postList, userList].forEach(list => {
        list.addEventListener("click", (e) => {
          if (e.target.classList.contains("edit-btn")) {
            const type = e.target.dataset.type;
            const item = JSON.parse(e.target.dataset.item);
            type === "post" ? showPostModal(item) : showUserModal(item);
          } else if (e.target.classList.contains("delete-btn")) {
            const type = e.target.dataset.type; 
            let id = e.target.dataset.id;
            let role = null;

            if (type === "user") {
              const userData = JSON.parse(e.target.closest("li").querySelector(".edit-btn").dataset.item);
              role = userData.type.toLowerCase(); // normalize to lowercase

              if (userData.id) {
                const parts = userData.id.split('-');
                if (parts.length >= 3) {
                  id = parseInt(parts[1]); // middle part = seq_id
                } else {
                  console.warn("Unexpected user ID format:", userData.id);
                  alert("Cannot determine user ID for deletion.");
                  return;
                }
              } else {
                console.warn("userData.id missing");
                alert("Cannot determine user ID for deletion.");
                return;
              }
            }


            itemToDelete = { type, id, role };
            deleteModal.style.display = "flex";
          }
        });
      });

      cancelBtn.onclick = () => deleteModal.style.display = "none";

      confirmBtn.onclick = () => {
        deleteModal.style.display = "none";
        if (!itemToDelete) return;

        if (itemToDelete.type === "post") deletePost(itemToDelete.id);
        else if (itemToDelete.type === "user") deleteUser(itemToDelete.id, itemToDelete.role);

        itemToDelete = null;
      };

      // Modal functions
      function showPostModal(post) {
        const modal = document.getElementById("view-post-modal");
        modal.style.display = "flex";
        document.getElementById("view-post-author").textContent = (post.author_name ? post.author_name + " " : "") + " - " + (post.author_id || "—");
        document.getElementById("view-post-title").textContent = post.title || "—";
        document.getElementById("view-post-content").textContent = post.content || "—";
        document.getElementById("view-post-date").textContent = post.create_date || post.created_at || "—";
        modal.querySelector(".close-btn").onclick = () => modal.style.display = "none";
      }

      function showUserModal(user) {
        const modal = document.getElementById("view-user-modal");
        modal.style.display = "flex";
        document.getElementById("view-user-id").textContent = user.id || "—";
        document.getElementById("view-user-name").textContent = user.name || "—";
        document.getElementById("view-user-email").textContent = user.email || "—";
        document.getElementById("view-user-department").textContent = user.department || "—";
        document.getElementById("view-user-date").textContent = user.create_date || "—";
        modal.querySelector(".close-btn").onclick = () => modal.style.display = "none";
      }

      // Delete functions
      function deletePost(postId) {
          postId = parseInt(postId);
          if (!postId || postId <= 0) {
              alert("Invalid post ID.");
              return;
          }
        
          fetch("server/delete-posts.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `id=${postId}`
          })
          .then(res => res.json())
          .then(data => {
              alert(data.message || "Deleted");
              if (data.success) location.reload();
          })
          .catch(err => { 
              console.error("Delete failed:", err); 
              alert("Failed to delete post."); 
          });
      }

      function deleteUser(userId, role) {
        if (!role) { alert("User role missing."); return; }
        fetch("server/delete-user.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(userId)}&role=${encodeURIComponent(role)}`
        })
        .then(res => res.json())
        .then(data => {
          alert(data.message || "Deleted");
          if (data.success) location.reload();
        })
        .catch(err => { console.error("Delete user failed:", err); alert("Failed to delete user."); });
      }

    })
    .catch(err => { console.error("Error loading dashboard data:", err); alert("Failed to load dashboard data."); });

});
</script>

</body>
</html>