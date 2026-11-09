<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$statusLabels = ['active'=>'Active','under_review'=>'Under Review','cleared'=>'Cleared'];
$statusPill = ['active'=>'bg-danger','under_review'=>'bg-warning text-dark','cleared'=>'bg-success'];
$materialLabels = ['garbage_plastic'=>'Garbage / Plastic','silt_mud_leaves'=>'Silt / Mud / Leaves','grease_fatberg'=>'Grease / Fatberg','unknown_other'=>'Unknown / Other'];
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-2">
    <h3 class="mb-0">Blockage Reports</h3>
    <a href="/CTRLFLOW/public/?route=report/create" class="btn btn-primary">+ Report a Blockage</a>
  </div>

  <form class="d-flex gap-3 align-items-end flex-wrap mb-4" method="GET" action="/CTRLFLOW/public/">
    <input type="hidden" name="route" value="report/index">
    <div class="d-flex flex-column" style="width:250px;">
      <label class="form-label mb-1">Search</label>
      <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control" placeholder="Code, landmark, description...">
    </div>
    <?php if ($_SESSION['user']['role'] !== 'crew'): ?>
    <div class="d-flex flex-column" style="width:200px;">
      <label class="form-label mb-1">Barangay</label>
      <select name="barangay" class="form-select">
        <option value="">All Barangays</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= htmlspecialchars($b) ?>" <?= (($_GET['barangay'] ?? '') === $b) ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="d-flex flex-column" style="width:200px;">
      <label class="form-label mb-1">Status</label>
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="active" <?= (($_GET['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
        <option value="under_review" <?= (($_GET['status'] ?? '') === 'under_review') ? 'selected' : '' ?>>Under Review</option>
        <option value="cleared" <?= (($_GET['status'] ?? '') === 'cleared') ? 'selected' : '' ?>>Cleared</option>
      </select>
    </div>
    <button class="btn btn-outline-secondary">Filter</button>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr><th>Code</th><th>Barangay</th><th>Landmark</th><th>Material</th><th>Severity</th><th>Status</th><th>Reported</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($reports)): ?>
        <tr><td colspan="8" class="text-center text-muted">No reports found.</td></tr>
        <?php else: foreach ($reports as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['report_code']) ?></td>
          <td><?= htmlspecialchars($r['barangay']) ?></td>
          <td><?= htmlspecialchars($r['landmark'] ?? '—') ?></td>
          <td><?= $materialLabels[$r['material_type']] ?? $r['material_type'] ?></td>
          <td class="text-capitalize"><?= htmlspecialchars($r['severity']) ?><?= $r['is_spilling'] ? ' <span class="badge bg-danger">Spilling</span>' : '' ?></td>
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
