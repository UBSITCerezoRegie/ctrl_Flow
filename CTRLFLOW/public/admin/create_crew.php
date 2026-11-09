<?php
include __DIR__ . '/../partials/header.php';
include __DIR__ . '/../partials/navbar.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';
$barangays = BarangayHelper::getAll();
?>
<div class="container py-4" style="max-width:600px;">
  <h3 class="mb-4">Add Cleanup Crew Member</h3>
  <div class="card p-4 shadow-sm">
    <form method="POST" action="/CTRLFLOW/public/?route=user/create_crew">
      <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control"></div>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
      <div class="mb-3">
        <label class="form-label">Assigned Barangay</label>
        <select name="barangay_assigned" class="form-select" required>
          <option value="">-- Select --</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary w-100" type="submit">Create Crew Account</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../partials/footer.php'; ?>
