<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AdminController {
    public function dashboard() {
        AuthMiddleware::requireRole(['super_admin']);
        $reports = ReportModel::getAll();
        include __DIR__ . '/../../public/admin/dashboard.php';
    }

    public function users() {
        AuthMiddleware::requireRole(['super_admin']);
        include __DIR__ . '/../../public/admin/users.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['super_admin']);
        include __DIR__ . '/../../public/admin/profile.php';
    }

    public function generateReport() {
        AuthMiddleware::requireRole(['super_admin']);

        $total = count(ReportModel::getAll());
        $statusStats = ReportModel::getStatusStats();
        $severityStats = ReportModel::getSeverityStats();
        $materialStats = ReportModel::getMaterialStats();
        $barangayStats = ReportModel::getBarangayStats();

        $getPercentage = function($count, $total) {
            return $total > 0 ? round(($count / $total) * 100, 1) : 0;
        };
        $clearRate = $getPercentage($statusStats['cleared'] ?? 0, $total);

        $barangayRowsHtml = '';
        foreach ($barangayStats as $stat) {
            $barangayRowsHtml .= '<tr><td>'.htmlspecialchars($stat['barangay']).'</td><td>'.$stat['count'].'</td></tr>';
        }
        $barangaySection = !empty($barangayStats)
            ? "<table><tr><th>Barangay</th><th>Report Count</th></tr>{$barangayRowsHtml}</table>"
            : "<p>No barangay data available</p>";

        $mostReported = !empty($barangayStats)
            ? "<p><strong>Most-Reported Barangay:</strong> {$barangayStats[0]['barangay']} ({$barangayStats[0]['count']} reports)</p>"
            : "";

        $html = "
<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; line-height:1.4; }
.container { width: 100%; position: relative; padding: 20px; }
.header { text-align:center; font-size:20px; margin-bottom:20px; font-weight:bold; text-transform:uppercase; color: #333; }
.subheader { text-align:center; font-size:14px; margin-bottom:30px; color: #666; }
.section { margin-bottom: 25px; }
.section-title { font-size: 16px; font-weight: bold; margin-bottom: 12px; color: #444; border-bottom: 2px solid #ccc; padding-bottom: 5px; }
table { width:100%; border-collapse:collapse; margin-bottom:15px; }
th, td { border:1px solid #444; padding:8px; font-size:11px; text-align:left; vertical-align:top; }
th { background:#f5f5f5; font-weight:bold; }
.footer-note { position: fixed; bottom: 10px; left: 20px; right: 20px; text-align: center; font-size: 10px; color: #666; border-top:1px solid #ccc; padding-top:8px; }
</style></head>
<body><div class='container'>
  <div class='header'>Ctrl+Flow Drainage Report Analytics</div>
  <div class='subheader'>Report Generated on " . date('F j, Y \a\t H:i') . "</div>

  <div class='section'>
    <div class='section-title'>Executive Summary</div>
    <table><tr>
      <td width='25%'><strong>Total Reports</strong><br><span style='font-size:16px;color:#007bff;'>$total</span></td>
      <td width='25%'><strong>Active</strong><br><span style='font-size:16px;color:#dc3545;'>".($statusStats['active'] ?? 0)."</span></td>
      <td width='25%'><strong>Under Review</strong><br><span style='font-size:16px;color:#f0ad4e;'>".($statusStats['under_review'] ?? 0)."</span></td>
      <td width='25%'><strong>Cleared</strong><br><span style='font-size:16px;color:#5cb85c;'>{$clearRate}%</span></td>
    </tr></table>
    {$mostReported}
  </div>

  <div class='section'>
    <div class='section-title'>Severity Breakdown</div>
    <table><tr><th>Severity</th><th>Count</th><th>Percentage</th></tr>
      <tr><td>High (Overflowing)</td><td>".($severityStats['high'] ?? 0)."</td><td>".$getPercentage($severityStats['high'] ?? 0, $total)."%</td></tr>
      <tr><td>Medium (Backed up)</td><td>".($severityStats['medium'] ?? 0)."</td><td>".$getPercentage($severityStats['medium'] ?? 0, $total)."%</td></tr>
      <tr><td>Low (Slow drainage)</td><td>".($severityStats['low'] ?? 0)."</td><td>".$getPercentage($severityStats['low'] ?? 0, $total)."%</td></tr>
      <tr><td>Spilling onto street</td><td>".($severityStats['spilling'] ?? 0)."</td><td>".$getPercentage($severityStats['spilling'] ?? 0, $total)."%</td></tr>
    </table>
  </div>

  <div class='section'>
    <div class='section-title'>Blockage Material</div>
    <table><tr><th>Material</th><th>Count</th></tr>
      <tr><td>Garbage / Plastic</td><td>".($materialStats['garbage_plastic'] ?? 0)."</td></tr>
      <tr><td>Silt / Mud / Leaves</td><td>".($materialStats['silt_mud_leaves'] ?? 0)."</td></tr>
      <tr><td>Grease / Fatberg</td><td>".($materialStats['grease_fatberg'] ?? 0)."</td></tr>
      <tr><td>Unknown / Other</td><td>".($materialStats['unknown_other'] ?? 0)."</td></tr>
    </table>
  </div>

  <div class='section'>
    <div class='section-title'>Reports per Barangay</div>
    {$barangaySection}
  </div>

  <div class='footer-note'>
    Generated by Ctrl+Flow — Barangay Drainage Report System. For internal barangay use only.<br>
    Generated on " . date('F j, Y \a\t H:i:s') . "
  </div>
</div></body></html>";

        require_once __DIR__ . '/../../vendor/autoload.php';
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'CtrlFlow_Analytics_Report_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }
}
