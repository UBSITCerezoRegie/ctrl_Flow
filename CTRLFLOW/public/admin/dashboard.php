<?php
require_once __DIR__.'/../partials/header.php';
require_once __DIR__.'/../partials/navbar.php';
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/ReportModel.php';
AuthMiddleware::requireRole(['super_admin']);

$total = count(ReportModel::getAll());
$statusStats = ReportModel::getStatusStats();
$severityStats = ReportModel::getSeverityStats();
$materialStats = ReportModel::getMaterialStats();
$barangayStats = ReportModel::getBarangayStats();

function getPercentage($count, $total) { return $total > 0 ? round(($count / $total) * 100, 1) : 0; }
$maxCount = !empty($barangayStats) ? $barangayStats[0]['count'] : 0;
?>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Super Admin Dashboard</h3>
    <a href="/CTRLFLOW/public/?route=admin/generateReport" class="btn btn-success">
      <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
    </a>
  </div>

  <div class="row g-3 mb-4 justify-content-center">
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center"><h6>Total Reports</h6><div class="display-6 fw-bold text-primary"><?=$total?></div></div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center"><h6>Active</h6><div class="display-6 fw-bold text-danger"><?=$statusStats['active'] ?? 0?></div></div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center"><h6>Under Review</h6><div class="display-6 fw-bold" style="color:#B45309;"><?=$statusStats['under_review'] ?? 0?></div></div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center"><h6>Cleared</h6><div class="display-6 fw-bold text-success"><?=$statusStats['cleared'] ?? 0?></div></div>
    </div>
    <div class="col-lg-2 col-md-3 col-6">
      <div class="card shadow-sm p-3 text-center">
        <h6>Most-Reported Barangay</h6>
        <?php if (!empty($barangayStats)): ?>
          <div class="fw-bold text-info"><?=htmlspecialchars($barangayStats[0]['barangay'])?></div>
          <div class="small text-muted"><?=$maxCount?> reports</div>
        <?php else: ?>
          <div class="fw-bold text-info">N/A</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-3 justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light"><h5 class="mb-0">Severity Breakdown</h5></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-4 text-center">
              <div class="h4 fw-bold text-danger mb-1"><?=$severityStats['high'] ?? 0?></div>
              <small class="text-danger">High (Overflowing)</small>
              <div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-danger" style="width:<?=getPercentage($severityStats['high'] ?? 0, $total)?>%"></div></div>
            </div>
            <div class="col-4 text-center">
              <div class="h4 fw-bold mb-1" style="color:#B45309;"><?=$severityStats['medium'] ?? 0?></div>
              <small style="color:#B45309;">Medium (Backed up)</small>
              <div class="progress mt-2" style="height:6px;"><div class="progress-bar" style="width:<?=getPercentage($severityStats['medium'] ?? 0, $total)?>%;background-color:#F59E0B;"></div></div>
            </div>
            <div class="col-4 text-center">
              <div class="h4 fw-bold text-success mb-1"><?=$severityStats['low'] ?? 0?></div>
              <small class="text-success">Low (Slow drainage)</small>
              <div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-success" style="width:<?=getPercentage($severityStats['low'] ?? 0, $total)?>%"></div></div>
            </div>
          </div>
          <hr>
          <div class="text-center"><small class="text-muted">Actively spilling onto the street: <strong><?=$severityStats['spilling'] ?? 0?></strong></small></div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-light"><h5 class="mb-0">Blockage Material</h5></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-6 text-center"><div class="h4 fw-bold text-primary mb-1"><?=$materialStats['garbage_plastic'] ?? 0?></div><small class="text-primary">Garbage / Plastic</small></div>
            <div class="col-6 text-center"><div class="h4 fw-bold text-info mb-1"><?=$materialStats['silt_mud_leaves'] ?? 0?></div><small class="text-info">Silt / Mud / Leaves</small></div>
            <div class="col-6 text-center"><div class="h4 fw-bold mb-1" style="color:#856404;"><?=$materialStats['grease_fatberg'] ?? 0?></div><small style="color:#856404;">Grease / Fatberg</small></div>
            <div class="col-6 text-center"><div class="h4 fw-bold text-secondary mb-1"><?=$materialStats['unknown_other'] ?? 0?></div><small class="text-secondary">Unknown / Other</small></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2 justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-light"><h5 class="mb-0">Reports per Barangay</h5></div>
        <div class="card-body">
          <?php if (!empty($barangayStats)): ?>
            <div class="row g-3">
              <?php foreach (array_slice($barangayStats, 0, 8) as $stat): $percentage = getPercentage($stat['count'], $total); ?>
              <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold"><?=htmlspecialchars($stat['barangay'])?></span>
                  <span class="badge bg-primary"><?=$stat['count']?></span>
                </div>
                <div class="progress" style="height:8px;"><div class="progress-bar bg-primary" style="width:<?=$percentage?>%"></div></div>
              </div>
              <?php endforeach; ?>
              <?php if (count($barangayStats) > 8): ?>
              <div class="col-12"><small class="text-muted">And <?=count($barangayStats) - 8?> more barangays&hellip;</small></div>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-4">No barangay data available</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-3">
    <div class="col-12">
      <div class="card shadow-sm p-3">
        <h5>Data Export &amp; Reports</h5>
        <div class="d-flex gap-3 flex-wrap">
          <a href="/CTRLFLOW/public/?route=export/reports_csv" class="btn btn-outline-primary"><i class="bi bi-table me-2"></i>Export Reports (CSV)</a>
          <a href="/CTRLFLOW/public/?route=export/reports_pdf" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf me-2"></i>Export Reports (PDF)</a>
          <a href="/CTRLFLOW/public/?route=log/index" class="btn btn-outline-secondary"><i class="bi bi-journal-text me-2"></i>View Audit Logs</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
