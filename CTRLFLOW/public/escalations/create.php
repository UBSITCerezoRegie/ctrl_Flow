<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$user = $_SESSION['user'];
?>
<div class="container py-4" style="max-width:640px;">
  <h3 class="mb-4">Escalate to City Engineering Office</h3>

  <?php if (!$report): ?>
    <div class="alert alert-danger">No report selected. Go to a report and choose "Escalate".</div>
  <?php else: ?>

  <div class="alert alert-light border mb-3">
    Escalating <strong><?= htmlspecialchars($report['report_code']) ?></strong> — <?= htmlspecialchars($report['barangay']) ?><?= $report['landmark'] ? ' ('.htmlspecialchars($report['landmark']).')' : '' ?>
  </div>

  <div class="card p-4 shadow-sm">
    <form method="POST" action="/CTRLFLOW/public/?route=escalation/create">
      <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">

      <div class="mb-3">
        <label class="form-label">Reporting Barangay</label>
        <input type="text" name="reporting_barangay" class="form-control" value="<?= htmlspecialchars($user['barangay_assigned'] ?? $report['barangay']) ?>">
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Tel</label>
          <input type="text" name="reporting_tel" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Contact Email</label>
          <input type="email" name="reporting_email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Receiving Office</label>
        <input type="text" name="receiving_office" class="form-control" value="City Engineering Office">
      </div>

      <div class="mb-3">
        <label class="form-label">Receiving Barangay <span class="text-muted small">(if forwarding to a neighboring barangay's crew)</span></label>
        <select name="receiving_barangay" class="form-select">
          <option value="">— None, city office only —</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Reason for Escalation</label>
        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Needs heavy equipment / jetting truck"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Details</label>
        <textarea name="details" class="form-control" rows="3"></textarea>
      </div>

      <button class="btn btn-primary w-100">Submit Escalation</button>
    </form>
  </div>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
