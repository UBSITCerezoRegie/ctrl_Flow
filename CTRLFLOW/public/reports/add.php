<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
$user = $_SESSION['user'] ?? null;
$lockBarangay = $user && $user['role'] === 'crew';
?>
<div class="container py-4" style="max-width:640px;">
  <h3 class="mb-4">Report a Blockage</h3>

  <div class="card p-4 shadow-sm">
    <form method="POST" action="/CTRLFLOW/public/?route=report/create" enctype="multipart/form-data">

      <div class="mb-3">
        <label class="form-label">📍 Barangay</label>
        <?php if ($lockBarangay): ?>
          <input type="text" class="form-control" value="<?= htmlspecialchars($user['barangay_assigned']) ?>" disabled>
          <input type="hidden" name="barangay" value="<?= htmlspecialchars($user['barangay_assigned']) ?>">
        <?php else: ?>
          <select name="barangay" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ($barangays as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">Landmark / Street</label>
        <input type="text" name="landmark" class="form-control" placeholder="e.g. In front of the bakery">
      </div>

      <div class="mb-3">
        <label class="form-label">📦 Blockage Material</label>
        <select name="material_type" class="form-select">
          <option value="garbage_plastic">Garbage / Plastic Accumulation</option>
          <option value="silt_mud_leaves">Silt / Mud / Leaves</option>
          <option value="grease_fatberg">Grease / Fatberg</option>
          <option value="unknown_other" selected>Unknown / Other</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">⚠️ Severity</label>
        <div class="d-flex flex-column gap-2">
          <label class="border rounded p-2"><input type="radio" name="severity" value="low"> Low — slow drainage</label>
          <label class="border rounded p-2"><input type="radio" name="severity" value="medium" checked> Medium — backed up</label>
          <label class="border rounded p-2"><input type="radio" name="severity" value="high"> High — overflowing</label>
        </div>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="is_spilling" name="is_spilling" value="1">
        <label class="form-check-label" for="is_spilling">💧 Sewage water is leaking out onto the street</label>
      </div>

      <div class="mb-3">
        <label class="form-label">📸 Photo <span class="text-muted small">(optional)</span></label>
        <input type="file" name="photo" accept="image/*" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">📝 Description <span class="text-muted small">(optional)</span></label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>

      <button class="btn btn-primary w-100" type="submit">Submit Report</button>
    </form>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
