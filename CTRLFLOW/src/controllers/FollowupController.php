<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

// Surfaces blockage reports that have sat unresolved too long, so admins can
// nudge crews. Replaces the former StaffFollowUpController.
class FollowupController {

    public function index() {
        AuthMiddleware::requireRole(['super_admin']);
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE type = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute(['crew_follow_up']);
        $rows = $stmt->fetchAll();
        include __DIR__ . '/../../public/followup/index.php';
    }

    public function resolve() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /CTRLFLOW/public/?route=followup/index"); exit; }

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE notification_id = ?");
        $stmt->execute([$id]);
        $notification = $stmt->fetch();

        if (!$notification) {
            Flash::set('danger', 'Notification not found.');
            header("Location: /CTRLFLOW/public/?route=followup/index");
            exit;
        }

        NotificationModel::markRead($id);

        if (!empty($notification['report_id'])) {
            $reportStmt = $pdo->prepare("SELECT barangay, reported_by_user_id FROM blockage_reports WHERE report_id = ?");
            $reportStmt->execute([$notification['report_id']]);
            $report = $reportStmt->fetch();

            if ($report) {
                $crew = UserModel::getAllCrewByBarangay($report['barangay']);
                foreach ($crew as $c) {
                    NotificationModel::create([
                        'user_id' => $c['user_id'], 'report_id' => $notification['report_id'], 'type' => 'follow_up_resolved_crew',
                        'title' => 'Follow-up noted', 'message' => 'Admin flagged a stale report in your barangay — please check status.',
                        'link' => '/CTRLFLOW/public/?route=report/view&id=' . $notification['report_id']
                    ]);
                }
                if (!empty($report['reported_by_user_id'])) {
                    NotificationModel::create([
                        'user_id' => $report['reported_by_user_id'], 'report_id' => $notification['report_id'], 'type' => 'follow_up_resolved_citizen',
                        'title' => 'Your report is being followed up on', 'message' => 'Thanks for your patience — the barangay team has been reminded about your report.',
                        'link' => '/CTRLFLOW/public/?route=citizen/reports'
                    ]);
                }
            }
        }

        LogModel::insertLog($_SESSION['user']['user_id'], 'follow_up_resolved', 'notifications', $notification['notification_id'], null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        Flash::set('success','Follow-up marked as handled. Crew and citizen notified.');
        header("Location: /CTRLFLOW/public/?route=followup/index");
        exit;
    }
}
