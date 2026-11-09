<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4" style="max-width:720px;">
  <h3 class="mb-4">Notifications</h3>
  <?php if (empty($rows)): ?>
    <div class="alert alert-light border text-center text-muted">No notifications yet.</div>
  <?php else: ?>
    <div class="list-group">
      <?php foreach ($rows as $n): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between">
            <strong><?= htmlspecialchars($n['title']) ?></strong>
            <small class="text-muted"><?= htmlspecialchars(substr($n['created_at'],0,16)) ?></small>
          </div>
          <div class="text-muted small mb-2"><?= htmlspecialchars($n['message']) ?></div>
          <?php if (!empty($n['link'])): ?>
            <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
