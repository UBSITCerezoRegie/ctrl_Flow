<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/UserModel.php';
require_once __DIR__.'/../../src/helpers/Flash.php';
require_once __DIR__.'/../../src/helpers/BarangayHelper.php';

AuthMiddleware::requireRole(['super_admin']);

$pdo = getDB();
$q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM users WHERE role = 'citizen'";
$params = [];
if ($q !== '') { $sql .= " AND (email LIKE ? OR full_name LIKE ?)"; $like = "%$q%"; $params[] = $like; $params[] = $like; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$citizenUsers = $stmt->fetchAll();

$crewUsers = UserModel::getAllByRole('crew');
?>

<div class="container py-4">
  <h3 class="mb-4">User Management</h3>

  <div class="card shadow-sm p-4 mb-4">
    <h5 class="mb-3">Create a Citizen Account</h5>
    <form method="POST" class="row g-3" action="/CTRLFLOW/public/?route=user/create_citizen_user">
      <div class="col-md-5"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control"></div>
      <div class="col-md-5"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required><div id="email-status" class="mt-1 small"></div></div>
      <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" type="submit">Create</button></div>
    </form>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <h5>Citizen Accounts</h5>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light"><tr style="text-align:center;"><th>Full Name</th><th>Email</th><th>Verified?</th><th style="width:140px;">Actions</th></tr></thead>
        <tbody>
        <?php if (empty($citizenUsers)): ?>
        <tr><td colspan="4" class="text-center text-muted">No citizen accounts found.</td></tr>
        <?php else: foreach ($citizenUsers as $u): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($u['full_name'] ?? '—') ?></td>
            <td class="text-center"><?= htmlspecialchars($u['email']) ?></td>
            <td class="text-center"><?= $u['is_verified'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
            <td class="text-center"><a href="/CTRLFLOW/public/?route=user/delete_user&id=<?= $u['user_id'] ?>" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm">Delete</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Cleanup Crew Accounts</h5>
      <a href="/CTRLFLOW/public/?route=user/create_crew" class="btn btn-outline-primary btn-sm">+ Add Crew Member</a>
    </div>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light"><tr style="text-align:center;"><th>Full Name</th><th>Email</th><th>Barangay</th><th>Verified?</th><th style="width:140px;">Actions</th></tr></thead>
        <tbody>
        <?php if (empty($crewUsers)): ?>
        <tr><td colspan="5" class="text-center text-muted">No crew accounts found.</td></tr>
        <?php else: foreach ($crewUsers as $u): ?>
          <tr>
            <td class="text-center"><?= htmlspecialchars($u['full_name'] ?? '—') ?></td>
            <td class="text-center"><?= htmlspecialchars($u['email']) ?></td>
            <td class="text-center"><?= htmlspecialchars($u['barangay_assigned'] ?? '—') ?></td>
            <td class="text-center"><?= $u['is_verified'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
            <td class="text-center"><a href="/CTRLFLOW/public/?route=user/delete_user&id=<?= $u['user_id'] ?>" onclick="return confirm('Delete this user?');" class="btn btn-danger btn-sm">Delete</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
