<?php
require_once __DIR__ . '/../models/EscalationModel.php';
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/BarangayHelper.php';
require_once __DIR__ . '/../helpers/PDFHelper.php';

class EscalationController {

    public function index() {
        AuthMiddleware::requireRole(['super_admin']);
        $escalations = EscalationModel::getAll();
        include __DIR__ . '/../../public/escalations/list.php';
    }

    public function create() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $reportId = $_GET['report_id'] ?? $_POST['report_id'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['created_by'] = $_SESSION['user']['user_id'];
            $id = EscalationModel::create($data);

            LogModel::insertLog($_SESSION['user']['user_id'], 'create', 'escalations', $id, null, json_encode($data), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
            ReportModel::updateStatus($data['report_id'], 'under_review', 'Escalated to City Engineering Office.');

            Flash::set('success','Escalation created.');
            header("Location: /CTRLFLOW/public/?route=escalation/view&id=".$id);
            exit;
        }

        $report = $reportId ? ReportModel::getById($reportId) : null;
        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/escalations/create.php';
    }

    public function view() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=escalation/index"); exit; }
        $escalation = EscalationModel::getById($id);
        $report = $escalation ? ReportModel::getById($escalation['report_id']) : null;
        include __DIR__ . '/../../public/escalations/view.php';
    }

    public function sent() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $escalations = EscalationModel::getSentBy($_SESSION['user']['user_id']);
        include __DIR__ . '/../../public/escalations/sent.php';
    }

    public function incoming() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $barangay = $_SESSION['user']['barangay_assigned'] ?? '';
        $escalations = EscalationModel::getIncomingFor($barangay);
        include __DIR__ . '/../../public/escalations/incoming.php';
    }

    public function received() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $barangay = $_SESSION['user']['barangay_assigned'] ?? '';
        $escalations = EscalationModel::getReceivedFor($barangay);
        include __DIR__ . '/../../public/escalations/received.php';
    }

    public function receive() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        $officer = $_POST['receiving_officer'] ?? ($_SESSION['user']['email'] ?? '');
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=escalation/incoming"); exit; }

        EscalationModel::receive($id, $officer, $_SESSION['user']['user_id']);
        LogModel::insertLog($_SESSION['user']['user_id'], 'receive', 'escalations', $id, null, json_encode(['receiving_officer' => $officer]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        Flash::set('success','Escalation marked received.');
        header("Location: /CTRLFLOW/public/?route=escalation/incoming");
        exit;
    }

    public function edit() {
        AuthMiddleware::requireRole(['super_admin', 'crew']);
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=escalation/index"); exit; }
        $escalation = EscalationModel::getById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EscalationModel::update($id, $_POST);
            if (($_POST['escalation_status'] ?? '') === 'completed') {
                ReportModel::updateStatus($escalation['report_id'], 'cleared', 'Cleared after escalation action.');
            }
            LogModel::insertLog($_SESSION['user']['user_id'], 'update', 'escalations', $id, json_encode($escalation), json_encode($_POST), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
            Flash::set('success','Escalation updated.');
            header("Location: /CTRLFLOW/public/?route=escalation/view&id=".$id);
            exit;
        }

        $report = ReportModel::getById($escalation['report_id']);
        include __DIR__ . '/../../public/escalations/edit.php';
    }

    public function print() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if (!$id) die('Missing ID');
        PDFHelper::generateEscalationPDF($id);
    }
}
