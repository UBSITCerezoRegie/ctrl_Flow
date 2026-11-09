<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/BarangayHelper.php';

class ReportController {

    public function index() {
        AuthMiddleware::requireLogin();
        $user = $_SESSION['user'];
        $q = trim($_GET['q'] ?? '');
        $barangayFilter = trim($_GET['barangay'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        if ($user['role'] === 'crew') {
            $reports = ReportModel::getAllByBarangayFiltered($user['barangay_assigned'], $q, $statusFilter);
        } else {
            $reports = ReportModel::getAllFiltered($q, $barangayFilter, $statusFilter);
        }

        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/reports/list.php';
    }

    public function create() {
        AuthMiddleware::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $user = $_SESSION['user'] ?? null;

            if ($user && $user['role'] === 'crew' && empty($data['barangay'])) {
                $data['barangay'] = $user['barangay_assigned'];
            }

            $data['is_spilling'] = !empty($_POST['is_spilling']) ? 1 : 0;
            $data['reported_by_user_id'] = $user['user_id'] ?? null;
            $data['created_by'] = $user['user_id'] ?? null;

            if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $safeName = 'report_' . bin2hex(random_bytes(6)) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $safeName)) {
                    $data['photo_path'] = 'assets/uploads/' . $safeName;
                }
            }

            $id = ReportModel::create($data);

            LogModel::insertLog($_SESSION['user']['user_id'], 'create', 'blockage_reports', $id, null, json_encode($data), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

            $crew = UserModel::getAllCrewByBarangay($data['barangay']);
            foreach ($crew as $c) {
                NotificationModel::create([
                    'user_id' => $c['user_id'], 'report_id' => $id, 'type' => 'new_report',
                    'title' => 'New blockage report', 'message' => 'A new blockage was reported in ' . $data['barangay'] . '.',
                    'link' => '/CTRLFLOW/public/?route=report/view&id=' . $id
                ]);
            }

            Flash::set('success','Report submitted. Thank you.');
            header("Location: /CTRLFLOW/public/?route=report/view&id=".$id);
            exit;
        }

        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/reports/add.php';
    }

    public function edit() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=report/index"); exit; }

        $report = ReportModel::getById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldData = $report;
            $_POST['is_spilling'] = !empty($_POST['is_spilling']) ? 1 : 0;
            ReportModel::update($id, $_POST);

            LogModel::insertLog($_SESSION['user']['user_id'], 'update', 'blockage_reports', $id, json_encode($oldData), json_encode($_POST), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

            Flash::set('success','Report updated.');
            header("Location: /CTRLFLOW/public/?route=report/view&id=".$id);
            exit;
        }

        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/reports/edit.php';
    }

    public function view() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=report/index"); exit; }
        $report = ReportModel::getById($id);
        include __DIR__ . '/../../public/reports/view.php';
    }

    public function update_status() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $status = $_POST['status'] ?? $_GET['status'] ?? null;
        $notes = $_POST['status_notes'] ?? null;

        if (!$id || !in_array($status, ['active','under_review','cleared'])) {
            Flash::set('danger','Invalid status update.');
            header("Location: /CTRLFLOW/public/?route=report/index");
            exit;
        }

        $report = ReportModel::getById($id);
        $crewId = ($_SESSION['user']['role'] === 'crew') ? $_SESSION['user']['user_id'] : null;
        ReportModel::updateStatus($id, $status, $notes, $crewId);

        LogModel::insertLog($_SESSION['user']['user_id'], 'status_update', 'blockage_reports', $id, json_encode(['status' => $report['status']]), json_encode(['status' => $status]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        if (!empty($report['reported_by_user_id'])) {
            $labels = ['active' => 'Active', 'under_review' => 'Under review', 'cleared' => 'Cleared'];
            NotificationModel::create([
                'user_id' => $report['reported_by_user_id'], 'report_id' => $id, 'type' => 'status_update',
                'title' => 'Your report status changed', 'message' => 'Report ' . $report['report_code'] . ' is now: ' . $labels[$status],
                'link' => '/CTRLFLOW/public/?route=report/view&id=' . $id
            ]);
        }

        Flash::set('success','Status updated.');
        header("Location: /CTRLFLOW/public/?route=report/view&id=".$id);
        exit;
    }

    public function delete() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) die("Missing ID");
        ReportModel::delete($id);
        LogModel::insertLog($_SESSION['user']['user_id'], 'delete', 'blockage_reports', $id, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
        Flash::set('success','Report deleted.');
        header('Location: /CTRLFLOW/public/?route=report/index');
        exit;
    }
}
