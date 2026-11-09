<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController {
    public function reports_csv() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $user = $_SESSION['user'];

        if ($user['role'] === 'crew') {
            $barangay = $user['barangay_assigned'] ?? null;
            if (!$barangay) { echo "Barangay missing from user session."; exit; }
            $rows = ReportModel::getAllByBarangay($barangay);
        } else {
            $rows = ReportModel::exportAll();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=blockage_reports_export_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['report_code','barangay','landmark','material_type','severity','is_spilling','status','description','created_at']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['report_code'],$row['barangay'],$row['landmark'],$row['material_type'],$row['severity'],$row['is_spilling'],$row['status'],$row['description'],$row['created_at']]);
        }
        fclose($out);
        exit;
    }

    public function reports_pdf() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $user = $_SESSION['user'];

        if ($user['role'] === 'crew') {
            $barangay = $user['barangay_assigned'] ?? null;
            if (!$barangay) { echo "Barangay missing from user session."; exit; }
            $rows = ReportModel::getAllByBarangay($barangay);
        } else {
            $rows = ReportModel::exportAll();
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $html = '<h2>Blockage Reports Export</h2>';
        $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
        $html .= '<thead><tr><th>Report Code</th><th>Barangay</th><th>Landmark</th><th>Material</th><th>Severity</th><th>Spilling</th><th>Status</th><th>Created At</th></tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.htmlspecialchars($row['report_code']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['barangay']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['landmark']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['material_type']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['severity']).'</td>';
            $html .= '<td>'.($row['is_spilling'] ? 'Yes' : 'No').'</td>';
            $html .= '<td>'.htmlspecialchars($row['status']).'</td>';
            $html .= '<td>'.htmlspecialchars($row['created_at']).'</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('blockage_reports_export_' . date('Ymd_His') . '.pdf', ["Attachment" => true]);
        exit;
    }
}
