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
<title>U-Plug | Student Users</title>
<link rel="stylesheet" href="/assets/css/admin-dashboard.css">
<link rel="stylesheet" href="/assets/css/student.css">
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
      <li><a href="student.php" class="nav-link active">Student Users</a></li>
      <li><a href="posts.php" class="nav-link">Posts</a></li>
      <li class="divider">Settings</li>
      <li><a href="#" class="nav-link">About</a></li>
      <li><a href="/assets/server/logout-process.php" class="nav-link">Logout</a></li>
    </ul>
  </aside>

  <!-- Burger -->
  <button id="burger" class="burger">&#9776;</button>

  <!-- Main Content -->
  <main class="main-content" id="main">
    <div class="scrollable-panel">
      <h1 class="page-title">Student Users</h1>

      <!-- Filter + Search -->
      <div class="filter-bar">
        <div class="filter-group">
          <label for="department-filter">Filter by Department:</label>
          <select id="department-filter">
            <option value="all">All Departments</option>
            <?php
            $departments = $conn->query("SELECT DISTINCT department FROM student_users ORDER BY department ASC");
            while ($dep = $departments->fetch_assoc()) {
                $depName = htmlspecialchars($dep['department']);
                echo "<option value='{$depName}'>{$depName}</option>";
            }
            ?>
          </select>
        </div>
        <div class="search-group">
          <label for="search-input">Search:</label>
          <input type="text" id="search-input" placeholder="Search student name...">
        </div>
      </div>

      <!-- Department Panels -->
      <section id="student-panels" class="student-panels">
        <?php
        $deptQuery = $conn->query("SELECT DISTINCT department FROM student_users ORDER BY department ASC");
        if ($deptQuery->num_rows > 0) {
            while ($deptRow = $deptQuery->fetch_assoc()) {
                $dept = htmlspecialchars($deptRow['department']);
                echo "<section class='panel' id='{$dept}-panel'>";
                echo "<div class='panel-header'>
                        <h2>{$dept}</h2>
                        <button class='expand-btn' data-target='{$dept}'>＋</button>
                      </div>";
                echo "<ul class='list collapsible' id='{$dept}-list'>";
                $studentQuery = $conn->query("SELECT * FROM student_users WHERE department='$dept' ORDER BY full_name ASC");
                while ($stu = $studentQuery->fetch_assoc()) {
                    $email = htmlspecialchars($stu['email']);
                    $name = htmlspecialchars($stu['full_name']);
                    $deptName = htmlspecialchars($stu['department']);
                    $seq_id = $stu['seq_id'];
                    $studentId = htmlspecialchars($stu['student_id']);
                    $profilePic = !empty($stu['profile_picture']) ? "../../" . htmlspecialchars($stu['profile_picture']) : '/assets/images/client/default/profile.png';
                    echo "
                    <li data-id='{$seq_id}'>
                      <div>{$name}</div>
                      <div class='actions'>
                        <button class='edit-btn' data-name='{$name}' data-studentid='{$studentId}' data-dept='{$deptName}' data-email='{$email}' data-profile='{$profilePic}'>View</button>
                        <button class='delete-btn' data-id='{$seq_id}'>Delete</button>
                      </div>
                    </li>";
                }
                echo "</ul></section>";
            }
        } else {
            echo "<p class='no-results'>No student data found.</p>";
        }
        ?>
      </section>

      <div class="footer">U-Plug ©2025. All rights reserved.</div>
    </div>
  </main>
</div>

<!-- View Modal -->
<div id="viewModal" class="modal">
  <div class="modal-content large">
    <span class="close-btn" id="closeViewModal">&times;</span>
    <h2>Student Information</h2>
    <div class="modal-body">
      <div class="profile-section">
        <img id="studentProfilePic" src="/assets/images/default-profile.png" alt="Profile Picture" class="profile-pic">
      </div>
      <p><strong>Name:</strong> <span id="studentName"></span></p>
      <p><strong>ID:</strong> <span id="studentID"></span></p>
      <p><strong>Department:</strong> <span id="studentDept"></span></p>
      <p><strong>Email:</strong> <span id="studentEmail"></span></p>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
  <div class="modal-content small">
    <span class="close-btn" id="closeDeleteModal">&times;</span>
    <h3>Confirm Deletion</h3>
    <p>Are you sure you want to delete this student?</p>
    <div class="modal-actions">
      <button id="confirm-delete">Delete</button>
      <button id="cancel-delete">Cancel</button>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const burger = document.getElementById("burger");
  const viewModal = document.getElementById("viewModal");
  const deleteModal = document.getElementById("deleteModal");
  let studentToDelete = null;

  // Sidebar toggle
  burger.addEventListener("click", () => sidebar.classList.toggle("hidden"));
  document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !burger.contains(e.target)) sidebar.classList.add("hidden");
  });

  // Expand panels
  document.querySelectorAll(".expand-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const targetId = btn.dataset.target;
      const panel = document.getElementById(`${targetId}-panel`);
      const expanded = panel.classList.toggle("expanded");
      btn.textContent = expanded ? "−" : "＋";
    });
  });

  // Search & filter
  const searchInput = document.getElementById("search-input");
  const departmentFilter = document.getElementById("department-filter");

  function filterStudents() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedDept = departmentFilter.value;
    document.querySelectorAll(".panel").forEach(panel => {
      const panelDept = panel.id.replace("-panel","");
      let panelHasVisibleItems = false;
      const studentItems = panel.querySelectorAll("li");
      const deptMatches = selectedDept === "all" || panelDept === selectedDept;
      studentItems.forEach(item => {
        const name = item.querySelector("div").textContent.toLowerCase();
        const show = deptMatches && name.includes(searchTerm);
        item.style.display = show ? "flex" : "none";
        if(show) panelHasVisibleItems = true;
      });
      panel.style.display = panelHasVisibleItems ? "block" : "none";
    });
  }
  searchInput.addEventListener("input", filterStudents);
  departmentFilter.addEventListener("change", filterStudents);

  // View modal
  document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.getElementById("studentName").textContent = btn.dataset.name;
      document.getElementById("studentID").textContent = btn.dataset.studentid || "Unknown ID";
      document.getElementById("studentDept").textContent = btn.dataset.dept;
      document.getElementById("studentEmail").textContent = btn.dataset.email;
      const profile = document.getElementById("studentProfilePic");
      profile.src = btn.dataset.profile || "/assets/images/default-profile.png";
      viewModal.style.display = "flex";
    });
  });

  // Delete modal
  document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      studentToDelete = btn.dataset.id;
      deleteModal.style.display = "flex";
    });
  });

  document.getElementById("confirm-delete").addEventListener("click", () => {
    if(!studentToDelete) return;
    fetch("server/delete_student.php", {
      method:"POST",
      headers:{"Content-Type":"application/x-www-form-urlencoded"},
      body:"id=" + encodeURIComponent(studentToDelete)
    }).then(res=>res.json())
      .then(data=>{
        if(data.success){
          const li = document.querySelector(`li[data-id='${studentToDelete}']`);
          if(li) li.remove();
        } else {
          alert(data.message);
        }
        deleteModal.style.display = "none";
        studentToDelete = null;
      }).catch(err=>{
        alert("Error deleting student");
        console.error(err);
      });
  });

  // Close modals
  document.getElementById("closeViewModal").onclick = () => viewModal.style.display = "none";
  document.getElementById("closeDeleteModal").onclick = () => deleteModal.style.display = "none";
  document.getElementById("cancel-delete").onclick = () => deleteModal.style.display = "none";
  window.onclick = e => {
    if(e.target === viewModal) viewModal.style.display="none";
    if(e.target === deleteModal) deleteModal.style.display="none";
  };
});
</script>
</body>
</html>
