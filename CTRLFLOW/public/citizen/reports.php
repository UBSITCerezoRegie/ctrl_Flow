<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$statusLabels = ['active'=>'Active','under_review'=>'Under Review','cleared'=>'Cleared'];
$statusPill = ['active'=>'bg-danger','under_review'=>'bg-warning text-dark','cleared'=>'bg-success'];
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">My Reports</h3>
    <a href="/CTRLFLOW/public/?route=report/create" class="btn btn-primary">+ Report a Blockage</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light"><tr><th>Code</th><th>Barangay</th><th>Landmark</th><th>Severity</th><th>Status</th><th>Reported</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($reports)): ?>
      <tr><td colspan="7" class="text-center text-muted">No reports yet.</td></tr>
      <?php else: foreach ($reports as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['report_code']) ?></td>
          <td><?= htmlspecialchars($r['barangay']) ?></td>
          <td><?= htmlspecialchars($r['landmark'] ?? '—') ?></td>
          <td class="text-capitalize"><?= htmlspecialchars($r['severity']) ?></td>
          <td><span class="badge <?= $statusPill[$r['status']] ?? 'bg-secondary' ?>"><?= $statusLabels[$r['status']] ?? $r['status'] ?></span></td>
          <td class="small text-muted"><?= htmlspecialchars(substr($r['created_at'],0,16)) ?></td>
          <td><a href="/CTRLFLOW/public/?route=report/view&id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
