<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$pill = ['pending'=>'bg-warning text-dark','received'=>'bg-info text-dark','completed'=>'bg-success'];
$user = $_SESSION['user'];
$canManage = in_array($user['role'], ['super_admin','crew']);
?>
<div class="container py-4" style="max-width:720px;">
  <?php if (!$escalation): ?>
    <div class="alert alert-danger">Escalation not found.</div>
  <?php else: ?>

  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <h3 class="mb-0"><?= htmlspecialchars($escalation['escalation_code']) ?></h3>
    <span class="badge <?= $pill[$escalation['escalation_status']] ?? 'bg-secondary' ?> fs-6"><?= ucfirst($escalation['escalation_status']) ?></span>
  </div>

  <div class="card p-4 shadow-sm mb-3">
    <div class="row g-3">
      <div class="col-6"><strong>Report:</strong><br><?= $report ? htmlspecialchars($report['report_code']).' — '.htmlspecialchars($report['barangay']) : '—' ?></div>
      <div class="col-6"><strong>Date:</strong><br><?= htmlspecialchars($escalation['escalation_date'] ?? '') ?></div>
      <div class="col-6"><strong>Reporting Barangay:</strong><br><?= htmlspecialchars($escalation['reporting_barangay'] ?? '—') ?></div>
      <div class="col-6"><strong>Receiving Office:</strong><br><?= htmlspecialchars($escalation['receiving_office'] ?? '—') ?></div>
      <div class="col-12"><strong>Reason:</strong><br><?= nl2br(htmlspecialchars($escalation['reason'] ?? '—')) ?></div>
      <div class="col-12"><strong>Details:</strong><br><?= nl2br(htmlspecialchars($escalation['details'] ?? '—')) ?></div>
      <?php if (!empty($escalation['action_taken'])): ?>
      <div class="col-12"><strong>Action Taken:</strong><br><?= nl2br(htmlspecialchars($escalation['action_taken'])) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex gap-2 flex-wrap mb-3">
    <a href="/CTRLFLOW/public/?route=escalation/print&id=<?= $escalation['escalation_id'] ?>" class="btn btn-outline-secondary btn-sm" target="_blank">Print / PDF Slip</a>
    <?php if ($canManage): ?>
    <a href="/CTRLFLOW/public/?route=escalation/edit&id=<?= $escalation['escalation_id'] ?>" class="btn btn-outline-primary btn-sm">Update Action Taken</a>
    <?php endif; ?>
  </div>

  <a href="/CTRLFLOW/public/?route=escalation/index" class="btn btn-link px-0">&larr; Back to escalations</a>
  <?php endif; ?>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
