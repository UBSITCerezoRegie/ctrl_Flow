<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
?>
<div class="container py-4">
  <h3 class="mb-4">Audit Logs</h3>

  <form class="row g-2 mb-4" method="GET" action="/CTRLFLOW/public/">
    <input type="hidden" name="route" value="log/index">
    <div class="col-md-2"><input type="text" name="action" class="form-control form-control-sm" placeholder="Action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>"></div>
    <div class="col-md-2"><input type="text" name="table_name" class="form-control form-control-sm" placeholder="Table" value="<?= htmlspecialchars($_GET['table_name'] ?? '') ?>"></div>
    <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>"></div>
    <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>"></div>
    <div class="col-md-2"><button class="btn btn-outline-secondary btn-sm w-100">Filter</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle small">
      <thead class="table-light"><tr><th>User</th><th>Action</th><th>Table</th><th>Record</th><th>Date</th></tr></thead>
      <tbody>
      <?php if (empty($logs)): ?>
      <tr><td colspan="5" class="text-center text-muted">No logs found.</td></tr>
      <?php else: foreach ($logs as $l): ?>
        <tr>
          <td><?= htmlspecialchars($l['user_id'] ?? '—') ?></td>
          <td><?= htmlspecialchars($l['action']) ?></td>
          <td><?= htmlspecialchars($l['table_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['record_id'] ?? '') ?></td>
          <td><?= htmlspecialchars(substr($l['created_at'],0,16)) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__.'/../partials/footer.php'; ?>
