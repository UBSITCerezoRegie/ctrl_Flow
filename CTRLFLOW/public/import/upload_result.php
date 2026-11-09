<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4" style="max-width:600px;">
  <h3 class="mb-4">Import Result</h3>
  <div class="card p-4 shadow-sm">
    <p><strong>File:</strong> <?= htmlspecialchars($f['name']) ?></p>
    <p><strong>Rows Imported:</strong> <?= intval($inserted) ?></p>
    <a href="/CTRLFLOW/public/?route=report/index" class="btn btn-primary">View Reports</a>
    <a href="/CTRLFLOW/public/?route=import/upload" class="btn btn-outline-secondary mt-2">Import Another File</a>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
