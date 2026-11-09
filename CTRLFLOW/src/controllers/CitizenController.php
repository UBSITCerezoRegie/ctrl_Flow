<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CitizenController {
    public function dashboard() {
        AuthMiddleware::requireRole(['citizen']);
        $user = $_SESSION['user'];
        $reports = ReportModel::getAllByReporter($user['user_id']);
        include __DIR__ . '/../../public/citizen/dashboard.php';
    }

    public function reports() {
        AuthMiddleware::requireRole(['citizen']);
        $reports = ReportModel::getAllByReporter($_SESSION['user']['user_id']);
        include __DIR__ . '/../../public/citizen/reports.php';
    }

    public function notifications() {
        AuthMiddleware::requireRole(['citizen']);
        $uid = $_SESSION['user']['user_id'];
        NotificationModel::markAllReadForUser($uid);
        $rows = NotificationModel::getByUser($uid, 50, 0);
        include __DIR__ . '/../../public/citizen/notifications.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['citizen']);
        include __DIR__ . '/../../public/citizen/profile.php';
    }
}
