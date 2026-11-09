<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4">
  <h3 class="mb-4">Incoming Escalations</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light"><tr><th>Code</th><th>Report</th><th>Reporting Barangay</th><th>Date</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($escalations)): ?>
      <tr><td colspan="5" class="text-center text-muted">No incoming escalations.</td></tr>
      <?php else: foreach ($escalations as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['escalation_code']) ?></td>
          <td><?= htmlspecialchars($e['report_code'] ?? '—') ?></td>
          <td><?= htmlspecialchars($e['reporting_barangay'] ?? '—') ?></td>
          <td class="small text-muted"><?= htmlspecialchars($e['escalation_date'] ?? '') ?></td>
          <td>
            <form method="POST" action="/CTRLFLOW/public/?route=escalation/receive" class="d-flex gap-1">
              <input type="hidden" name="id" value="<?= $e['escalation_id'] ?>">
              <input type="text" name="receiving_officer" class="form-control form-control-sm" placeholder="Officer name" required>
              <button class="btn btn-sm btn-success">Receive</button>
            </form>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
