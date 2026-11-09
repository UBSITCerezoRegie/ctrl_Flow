<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$statusLabels = ['active'=>'Active','under_review'=>'Under Review','cleared'=>'Cleared'];
$statusPill = ['active'=>'bg-danger','under_review'=>'bg-warning text-dark','cleared'=>'bg-success'];
$materialLabels = ['garbage_plastic'=>'Garbage / Plastic','silt_mud_leaves'=>'Silt / Mud / Leaves','grease_fatberg'=>'Grease / Fatberg','unknown_other'=>'Unknown / Other'];
$user = $_SESSION['user'];
$canManage = in_array($user['role'], ['super_admin','crew']);
?>
<div class="container py-4" style="max-width:720px;">

  <?php if (!$report): ?>
    <div class="alert alert-danger">Report not found.</div>
  <?php else: ?>

  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
      <h3 class="mb-1"><?= htmlspecialchars($report['report_code']) ?></h3>
      <p class="text-muted mb-0"><?= htmlspecialchars($report['barangay']) ?><?= $report['landmark'] ? ' — '.htmlspecialchars($report['landmark']) : '' ?></p>
    </div>
    <span class="badge <?= $statusPill[$report['status']] ?? 'bg-secondary' ?> fs-6"><?= $statusLabels[$report['status']] ?? $report['status'] ?></span>
  </div>

  <div class="card p-4 shadow-sm mb-3">
    <div class="row g-3">
      <div class="col-6"><strong>Material:</strong><br><?= $materialLabels[$report['material_type']] ?? $report['material_type'] ?></div>
      <div class="col-6"><strong>Severity:</strong><br class="text-capitalize"><?= htmlspecialchars($report['severity']) ?></div>
      <div class="col-6"><strong>Spilling onto street:</strong><br><?= $report['is_spilling'] ? 'Yes' : 'No' ?></div>
      <div class="col-6"><strong>Reported:</strong><br><?= htmlspecialchars(substr($report['created_at'],0,16)) ?></div>
      <?php if (!empty($report['description'])): ?>
      <div class="col-12"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($report['description'])) ?></div>
      <?php endif; ?>
      <?php if (!empty($report['photo_path'])): ?>
      <div class="col-12"><strong>Photo:</strong><br><img src="/CTRLFLOW/public/<?= htmlspecialchars($report['photo_path']) ?>" class="img-fluid rounded mt-2" style="max-height:320px;"></div>
      <?php endif; ?>
      <?php if (!empty($report['status_notes'])): ?>
      <div class="col-12"><strong>Status notes:</strong><br><?= nl2br(htmlspecialchars($report['status_notes'])) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($canManage): ?>
  <div class="card p-4 shadow-sm mb-3">
    <h5 class="mb-3">Update Status</h5>
    <form method="POST" action="/CTRLFLOW/public/?route=report/update_status" class="row g-2">
      <input type="hidden" name="id" value="<?= $report['report_id'] ?>">
      <div class="col-md-5">
        <select name="status" class="form-select">
          <option value="active" <?= $report['status']==='active'?'selected':'' ?>>Active</option>
          <option value="under_review" <?= $report['status']==='under_review'?'selected':'' ?>>Under Review</option>
          <option value="cleared" <?= $report['status']==='cleared'?'selected':'' ?>>Cleared</option>
        </select>
      </div>
      <div class="col-md-5">
        <input type="text" name="status_notes" class="form-control" placeholder="Optional note">
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Update</button></div>
    </form>
  </div>

  <div class="d-flex gap-2 flex-wrap mb-3">
    <a href="/CTRLFLOW/public/?route=report/edit&id=<?= $report['report_id'] ?>" class="btn btn-outline-secondary btn-sm">Edit Report</a>
    <a href="/CTRLFLOW/public/?route=escalation/create&report_id=<?= $report['report_id'] ?>" class="btn btn-outline-warning btn-sm">Escalate to City Engineering</a>
    <?php if ($user['role'] === 'super_admin'): ?>
    <a href="/CTRLFLOW/public/?route=report/delete&id=<?= $report['report_id'] ?>" onclick="return confirm('Delete this report?');" class="btn btn-outline-danger btn-sm">Delete</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <a href="/CTRLFLOW/public/?route=report/index" class="btn btn-link px-0">&larr; Back to reports</a>

  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
