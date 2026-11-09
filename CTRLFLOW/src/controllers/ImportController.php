<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/ImportLogModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class ImportController {
    public function upload() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flash::set('danger', 'Upload error');
                header("Location: /CTRLFLOW/public/?route=import/upload");
                exit;
            }

            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                Flash::set('danger', 'Could not read file');
                header("Location: /CTRLFLOW/public/?route=import/upload");
                exit;
            }

            $header = fgetcsv($handle);
            $rowsImported = 0;
            $skipped = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                $data = @array_combine($header, $row);
                if (!$data) { $skipped++; continue; }
                if (empty($data['barangay'])) { $skipped++; continue; }
                if (empty($data['report_code'])) $data['report_code'] = ReportModel::generateReportCode();
                if (ReportModel::existsByCode($data['report_code'])) { $skipped++; continue; }

                try {
                    ReportModel::createFromImport($data, null);
                    $rowsImported++;
                } catch (PDOException $e) {
                    $errors[] = "Row error: " . $e->getMessage();
                    $skipped++;
                }
            }

            fclose($handle);
            ImportLogModel::logUpload($file['name'], $rowsImported, $_SESSION['user']['user_id']);

            LogModel::insertLog($_SESSION['user']['user_id'], 'import_reports', 'blockage_reports', null, null, json_encode(['file' => $file['name'], 'inserted' => $rowsImported, 'skipped' => $skipped, 'errors' => $errors]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

            $f = $file;
            $rows = $rowsImported;
            $inserted = $rowsImported;
            include __DIR__ . '/../../public/import/upload_result.php';
            return;
        }

        include __DIR__ . '/../../public/import/upload.php';
    }
}
