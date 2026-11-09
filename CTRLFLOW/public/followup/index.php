<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4">
  <h3 class="mb-2">Stale Report Follow-ups</h3>
  <p class="text-muted mb-4">Reports flagged as sitting unresolved too long. Resolving notifies the barangay crew and the citizen who reported it.</p>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light"><tr><th>Title</th><th>Message</th><th>Flagged</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
      <tr><td colspan="4" class="text-center text-muted">No stale reports flagged right now.</td></tr>
      <?php else: foreach ($rows as $n): ?>
        <tr>
          <td><?= htmlspecialchars($n['title']) ?></td>
          <td class="small"><?= htmlspecialchars($n['message']) ?></td>
          <td class="small text-muted"><?= htmlspecialchars(substr($n['created_at'],0,16)) ?></td>
          <td>
            <a href="/CTRLFLOW/public/?route=followup/resolve&id=<?= $n['notification_id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark as followed up?');">Resolve</a>
            <?php if (!empty($n['report_id'])): ?>
            <a href="/CTRLFLOW/public/?route=report/view&id=<?= $n['report_id'] ?>" class="btn btn-sm btn-outline-primary">View Report</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
