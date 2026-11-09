<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$pill = ['pending'=>'bg-warning text-dark','received'=>'bg-info text-dark','completed'=>'bg-success'];
?>
<div class="container py-4">
  <h3 class="mb-4">All Escalations</h3>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light"><tr><th>Code</th><th>Report</th><th>Receiving Barangay</th><th>Date</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($escalations)): ?>
      <tr><td colspan="6" class="text-center text-muted">No escalations found.</td></tr>
      <?php else: foreach ($escalations as $e): ?>
        <tr>
          <td><?= htmlspecialchars($e['escalation_code']) ?></td>
          <td><?= htmlspecialchars($e['report_code'] ?? '—') ?></td>
          <td><?= htmlspecialchars($e['receiving_barangay'] ?? '—') ?></td>
          <td class="small text-muted"><?= htmlspecialchars($e['escalation_date'] ?? '') ?></td>
          <td><span class="badge <?= $pill[$e['escalation_status']] ?? 'bg-secondary' ?>"><?= ucfirst($e['escalation_status']) ?></span></td>
          <td><a href="/CTRLFLOW/public/?route=escalation/view&id=<?= $e['escalation_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
