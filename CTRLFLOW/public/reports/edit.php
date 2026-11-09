<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4" style="max-width:640px;">
  <h3 class="mb-4">Edit Report <?= htmlspecialchars($report['report_code']) ?></h3>

  <div class="card p-4 shadow-sm">
    <form method="POST" action="/CTRLFLOW/public/?route=report/edit&id=<?= $report['report_id'] ?>">

      <div class="mb-3">
        <label class="form-label">Barangay</label>
        <select name="barangay" class="form-select" required>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>" <?= ($b === $report['barangay']) ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Landmark / Street</label>
        <input type="text" name="landmark" class="form-control" value="<?= htmlspecialchars($report['landmark'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Blockage Material</label>
        <select name="material_type" class="form-select">
          <?php foreach (['garbage_plastic'=>'Garbage / Plastic Accumulation','silt_mud_leaves'=>'Silt / Mud / Leaves','grease_fatberg'=>'Grease / Fatberg','unknown_other'=>'Unknown / Other'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($report['material_type'] === $val) ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Severity</label>
        <div class="d-flex flex-column gap-2">
          <?php foreach (['low'=>'Low — slow drainage','medium'=>'Medium — backed up','high'=>'High — overflowing'] as $val => $label): ?>
            <label class="border rounded p-2"><input type="radio" name="severity" value="<?= $val ?>" <?= ($report['severity'] === $val) ? 'checked' : '' ?>> <?= $label ?></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="is_spilling" name="is_spilling" value="1" <?= $report['is_spilling'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_spilling">Sewage water is leaking out onto the street</label>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($report['description'] ?? '') ?></textarea>
      </div>

      <button class="btn btn-primary w-100" type="submit">Save Changes</button>
    </form>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
