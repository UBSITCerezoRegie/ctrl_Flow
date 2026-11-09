<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$statusLabels = ['active'=>'Active','under_review'=>'Under Review','cleared'=>'Cleared'];
$statusPill = ['active'=>'bg-danger','under_review'=>'bg-warning text-dark','cleared'=>'bg-success'];
?>
<div class="container py-4">
  <h3 class="mb-1">Crew Dashboard</h3>
  <p class="text-muted mb-4">Barangay <?= htmlspecialchars($_SESSION['user']['barangay_assigned']) ?></p>

  <div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card shadow-sm p-3 text-center"><h6>Active</h6><div class="display-6 fw-bold text-danger"><?=$active?></div></div></div>
    <div class="col-md-4"><div class="card shadow-sm p-3 text-center"><h6>Under Review</h6><div class="display-6 fw-bold" style="color:#B45309;"><?=$underReview?></div></div></div>
    <div class="col-md-4"><div class="card shadow-sm p-3 text-center"><h6>Cleared</h6><div class="display-6 fw-bold text-success"><?=$cleared?></div></div></div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Recent Reports</h5>
    <a href="/CTRLFLOW/public/?route=report/index" class="btn btn-sm btn-outline-primary">View All</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light"><tr><th>Code</th><th>Landmark</th><th>Severity</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($reports)): ?>
      <tr><td colspan="5" class="text-center text-muted">No reports yet in this barangay.</td></tr>
      <?php else: foreach (array_slice($reports, 0, 10) as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['report_code']) ?></td>
          <td><?= htmlspecialchars($r['landmark'] ?? '—') ?></td>
          <td class="text-capitalize"><?= htmlspecialchars($r['severity']) ?></td>
          <td><span class="badge <?= $statusPill[$r['status']] ?? 'bg-secondary' ?>"><?= $statusLabels[$r['status']] ?? $r['status'] ?></span></td>
          <td><a href="/CTRLFLOW/public/?route=report/view&id=<?= $r['report_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
