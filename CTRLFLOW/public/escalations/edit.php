<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4" style="max-width:640px;">
  <h3 class="mb-4">Update Escalation <?= htmlspecialchars($escalation['escalation_code']) ?></h3>
  <div class="card p-4 shadow-sm">
    <form method="POST" action="/CTRLFLOW/public/?route=escalation/edit&id=<?= $escalation['escalation_id'] ?>">
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="escalation_status" class="form-select">
          <option value="pending" <?= $escalation['escalation_status']==='pending'?'selected':'' ?>>Pending</option>
          <option value="received" <?= $escalation['escalation_status']==='received'?'selected':'' ?>>Received</option>
          <option value="completed" <?= $escalation['escalation_status']==='completed'?'selected':'' ?>>Completed (clears the report)</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Action Taken</label>
        <textarea name="action_taken" class="form-control" rows="3"><?= htmlspecialchars($escalation['action_taken'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2"><?= htmlspecialchars($escalation['remarks'] ?? '') ?></textarea>
      </div>
      <button class="btn btn-primary w-100">Save</button>
    </form>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
