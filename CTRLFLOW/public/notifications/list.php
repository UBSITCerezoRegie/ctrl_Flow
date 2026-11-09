<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$role = $_SESSION['user']['role'];
?>
<div class="container py-4" style="max-width:800px;">
  <h3 class="mb-4">Notifications</h3>

  <?php if (empty($rows)): ?>
    <div class="alert alert-light border text-center text-muted">No notifications yet.</div>
  <?php else: ?>
    <div class="list-group mb-4">
      <?php foreach ($rows as $n): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between flex-wrap gap-2">
            <strong><?= htmlspecialchars($n['title']) ?></strong>
            <small class="text-muted"><?= htmlspecialchars(substr($n['created_at'],0,16)) ?><?php if ($role === 'super_admin' && !empty($n['email'])): ?> &middot; <?= htmlspecialchars($n['email']) ?><?php endif; ?></small>
          </div>
          <div class="text-muted small mb-2"><?= htmlspecialchars($n['message']) ?></div>
          <?php if (!empty($n['link'])): ?>
            <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($pagination['has_pagination'])): ?>
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
          <li class="page-item <?= $p == $pagination['current_page'] ? 'active' : '' ?>">
            <a class="page-link" href="/CTRLFLOW/public/?route=notification/list&page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
