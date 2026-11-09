<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4" style="max-width:600px;">
  <h3 class="mb-4">Import Reports (CSV)</h3>
  <div class="card p-4 shadow-sm">
    <p class="text-muted small">CSV columns: <code>report_code,barangay,landmark,material_type,severity,is_spilling,description</code>. <code>report_code</code> and <code>is_spilling</code> are optional.</p>
    <form method="POST" action="/CTRLFLOW/public/?route=import/upload" enctype="multipart/form-data">
      <div class="mb-3">
        <input type="file" name="csv_file" accept=".csv" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Upload &amp; Import</button>
    </form>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
