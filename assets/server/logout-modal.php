<!-- logout-modal.php -->
<div id="logoutModal" class="logout-modal hidden">
  <div class="logout-modal-content">
    <h3>Are you sure you want to log out?</h3>
    <div class="logout-modal-actions">
      <button id="confirmLogout" class="btn-confirm">Yes, Logout</button>
      <button id="cancelLogout" class="btn-cancel">Cancel</button>
    </div>
  </div>
</div>

<style>
.logout-modal {
  position: fixed !important;
  inset: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999 !important;
}

.logout-modal.hidden {
  display: none;
}
.logout-modal-content {
  background: #fff;
  padding: 25px 35px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.logout-modal-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 15px;
}
.btn-confirm {
  background-color: #c0392b;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}
.btn-cancel {
  background-color: #7f8c8d;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const logoutBtn = document.getElementById('logoutBtn');
  const logoutModal = document.getElementById('logoutModal');
  const confirmLogout = document.getElementById('confirmLogout');
  const cancelLogout = document.getElementById('cancelLogout');

  if (logoutBtn && logoutModal) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      logoutModal.classList.remove('hidden');
    });

    cancelLogout.addEventListener('click', () => {
      logoutModal.classList.add('hidden');
    });

    confirmLogout.addEventListener('click', () => {
      window.location.href = 'assets/server/logout-process.php';
    });
  }
});
</script>