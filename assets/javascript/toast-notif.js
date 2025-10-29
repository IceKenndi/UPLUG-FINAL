// Toast queue system (optional if you want stacking)
const toastQueue = [];
let toastActive = false;

function showToast(message, type = 'info', source = 'manual', link = null, postId = null, timestamp = null, wasEdited = false) {
  toastQueue.push({ message, type, source, link, postId, timestamp, wasEdited });
  processToastQueue();
}

function processToastQueue() {
  if (toastActive || toastQueue.length === 0) return;

  const { message, type, source, link, postId, timestamp, wasEdited } = toastQueue.shift(); // ✅ FIXED

  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast ${type} clickable`;
  if (postId) toast.dataset.postId = postId;

  toast.innerHTML = `
    <button class="dismiss-toast">✕</button>
    <div class="toast-message">
      <span>${message}</span>
      ${timestamp ? `<small class="toast-timestamp">${wasEdited ? 'Edited at: ' : 'Posted: '} ${new Date(timestamp).toLocaleString()}</small>` : ''}
    </div>
  `;

  container.appendChild(toast);

  toast.querySelector('.dismiss-toast').addEventListener('click', () => {
    toast.classList.add('slideFade');

    setTimeout(() => {
      toast.remove();
      if (postId) {
        fetch('/assets/server/ack-toast.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `post_id=${encodeURIComponent(postId)}`
        });
      }
    }, 600);
  });

  setTimeout(() => {
    toastActive = false;
    processToastQueue();
  }, 1000);
}

// ✅ DOMContentLoaded: show session-based toast if present
  document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('toastContainer');
  const staticToasts = container?.querySelectorAll('.toast');

  staticToasts?.forEach(toastEl => {
    const postId = toastEl.dataset.postId || null;
    const message = toastEl.querySelector('span')?.textContent?.trim();
    const timestampEl = toastEl.querySelector('.toast-timestamp');
    const timestampText = timestampEl?.textContent?.replace(/^(Posted: |Edited at: )/, '') || null;
    let timestamp = null;
    if (timestampText) {
      const parsedDate = new Date(timestampText);
      if (!isNaN(parsedDate.getTime())) {
        timestamp = parsedDate.toISOString();
      }
    }
    const wasEdited = timestampEl?.textContent?.startsWith('Edited at:') || false;

    const type = toastEl.classList.contains('success') ? 'success'
                : toastEl.classList.contains('error') ? 'error'
                : toastEl.classList.contains('warning') ? 'warning'
                : 'info';

    if (message) {
      toastEl.remove(); // Remove static toast
      showToast(message, type, 'manual', null, postId, timestamp, wasEdited); // ✅ Re-queue for animation
    }
  });
});



function pollForToasts() {
  fetch('/assets/server/check-toast.php')
  .then(res => res.json())
  .then(data => {
    [...data].reverse().forEach(toast => {
      const existing = document.querySelector(`.toast[data-post-id="${toast.post_id}"]`);
      const link = (toast.post_type === 'official' || toast.post_type === 'department') ? 'news.php' : null;
      const newTimestamp = `${toast.was_edited ? 'Edited at: ' : 'Posted: '} ${new Date(toast.timestamp).toLocaleString()}`;

      if (existing) {
        const currentMessage = existing.querySelector('span')?.textContent;
        const currentTimestamp = existing.querySelector('.toast-timestamp')?.textContent;

        if (currentMessage !== toast.message || currentTimestamp !== newTimestamp) {
          existing.remove();
          showToast(toast.message, 'info', 'poll', link, toast.post_id, toast.timestamp, toast.was_edited);
        }
      } else {
        showToast(toast.message, 'info', 'poll', link, toast.post_id, toast.timestamp, toast.was_edited);
      }
    });
  });
}

setInterval(pollForToasts, 1000);
