<?php
if (!isset($_SESSION)) session_start();
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../../src/models/NotificationModel.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<nav class="navbar navbar-expand-lg mb-4" data-bs-theme="dark" style="background:#1E293B;">
  <div class="container-fluid">

    <?php
      $brandLink = "/CTRLFLOW/public/login.php";
      if ($user) {
          if ($user['role'] === 'super_admin') $brandLink = "/CTRLFLOW/public/?route=admin/dashboard";
          elseif ($user['role'] === 'crew') $brandLink = "/CTRLFLOW/public/?route=crew/dashboard";
          elseif ($user['role'] === 'citizen') $brandLink = "/CTRLFLOW/public/?route=citizen/dashboard";
      }
    ?>

    <a class="navbar-brand fw-bold" href="<?= $brandLink ?>">💧 Ctrl+Flow</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarColor01">
      <ul class="navbar-nav me-auto">

        <?php if ($user): ?>

          <?php if ($user['role'] === 'super_admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=admin/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=report/index">Reports</a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">User Management</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=admin/users">Manage Users</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=user/create_crew">Add Cleanup Crew</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Escalations</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/index">All Escalations</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/incoming">Incoming</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/sent">Sent</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Tools</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=import/upload">Import CSV</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=log/index">Audit Logs</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=notification/list">Notifications</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=followup/index">Stale Report Follow-ups</a></li>
              </ul>
            </li>
          <?php endif; ?>

          <?php if ($user['role'] === 'crew'): ?>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=crew/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=report/index">Reports in <?= htmlspecialchars($user['barangay_assigned'] ?? '') ?></a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Escalations</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/sent">Sent</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/incoming">Incoming</a></li>
                <li><a class="dropdown-item" href="/CTRLFLOW/public/?route=escalation/received">Received</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=notification/list">Notifications</a></li>
          <?php endif; ?>

          <?php if ($user['role'] === 'citizen'): ?>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=citizen/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=report/create">Report a Blockage</a></li>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=citizen/reports">My Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=citizen/notifications">Notifications</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav align-items-center">
        <?php if ($user): ?>

          <li class="nav-item me-2">
            <?php if ($user['role'] === 'super_admin'): ?>
              <a class="nav-link" href="/CTRLFLOW/public/?route=admin/profile">Profile</a>
            <?php elseif ($user['role'] === 'crew'): ?>
              <a class="nav-link" href="/CTRLFLOW/public/?route=crew/profile">Profile</a>
            <?php elseif ($user['role'] === 'citizen'): ?>
              <a class="nav-link" href="/CTRLFLOW/public/?route=citizen/profile">Profile</a>
            <?php endif; ?>
          </li>

          <li class="nav-item dropdown me-2">
            <?php $unread = NotificationModel::countUnreadForUser($user['user_id']); ?>
            <a class="nav-link position-relative" href="#" id="navNotifDropdown" data-bs-toggle="dropdown">
              <i class="bi bi-bell" style="font-size:1.2rem;color:#fff;"></i>
              <span id="notif-badge" class="<?= $unread > 0 ? 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger' : 'd-none' ?>">
                <?= intval($unread) ?>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:360px;" id="notif-dropdown">
              <li class="d-flex justify-content-between align-items-center mb-2 px-2">
                <strong>Notifications</strong>
                <a href="/CTRLFLOW/public/?route=notification/list" class="small">View all</a>
              </li>
              <li><div id="notif-list" style="max-height:320px;overflow:auto;"></div></li>
              <li class="dropdown-divider"></li>
              <li class="px-2"><button id="mark-all-read" class="btn btn-sm btn-outline-secondary w-100">Mark all as read</button></li>
            </ul>
          </li>

          <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/?route=auth/logout">Logout</a></li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/CTRLFLOW/public/login.php">Login</a></li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<?php require_once __DIR__ . '/../../src/helpers/Flash.php'; ?>
<?php Flash::display(); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  function renderNotifications(items) {
    const container = document.getElementById('notif-list');
    if (!container) return;
    container.innerHTML = '';
    if (!items || items.length === 0) {
      container.innerHTML = '<div class="text-center text-muted p-3">No notifications.</div>';
      return;
    }
    items.forEach(n => {
      const isRead = (n.is_read == 1);
      const tr = document.createElement('div');
      tr.className = 'd-flex align-items-start gap-2 px-2 py-2 border-bottom cursor-pointer';
      tr.addEventListener('click', (e) => {
        if (e.target.closest('a')) return;
        if (!isRead) {
          fetch('/CTRLFLOW/public/?route=notification/mark_read&id=' + n.notification_id)
            .then(() => { loadNotifications(); updateUnreadCount(); });
        }
      });
      tr.innerHTML = `
        <div class="flex-grow-1">
          <div class="small fw-bold">${escapeHtml(n.title)}</div>
          <div class="small text-muted">${escapeHtml(n.message)}</div>
          <div class="small text-muted mt-1">${escapeHtml(n.created_at)}</div>
        </div>
        <div class="ms-2 d-flex flex-column gap-1">
          ${n.link ? `<a href="${escapeAttr(n.link)}" class="btn btn-sm btn-outline-primary">Open</a>` : ''}
          <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${n.notification_id}" ${isRead ? 'disabled' : ''}>
            ${isRead ? 'Read' : 'Mark read'}
          </button>
        </div>
      `;
      container.appendChild(tr);
    });
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        fetch('/CTRLFLOW/public/?route=notification/mark_read&id=' + btn.dataset.id)
          .then(() => { loadNotifications(); updateUnreadCount(); });
      });
    });
  }

  function escapeHtml(s) { return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escapeAttr(s) { return (s || '').replace(/"/g,'&quot;'); }

  async function loadNotifications() {
    const res = await fetch('/CTRLFLOW/public/?route=notification/json_latest');
    renderNotifications(await res.json());
  }

  async function updateUnreadCount() {
    const res = await fetch('/CTRLFLOW/public/?route=notification/json_unread_count');
    const json = await res.json();
    const badge = document.getElementById('notif-badge');
    if (!badge) return;
    if (json.count > 0) { badge.classList.remove('d-none'); badge.innerText = json.count; }
    else { badge.classList.add('d-none'); }
  }

  document.getElementById('navNotifDropdown')?.addEventListener('show.bs.dropdown', loadNotifications);
  document.getElementById('mark-all-read')?.addEventListener('click', async () => {
    await fetch('/CTRLFLOW/public/?route=notification/mark_all_read', { method: 'POST' });
    await loadNotifications();
    await updateUnreadCount();
  });

  updateUnreadCount();
  setInterval(() => { loadNotifications(); updateUnreadCount(); }, 20000);
});
</script>
