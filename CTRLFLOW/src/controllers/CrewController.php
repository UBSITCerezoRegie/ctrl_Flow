<?php
require_once __DIR__ . '/../models/ReportModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CrewController {
    public function dashboard() {
        AuthMiddleware::requireRole(['crew']);
        $barangay = $_SESSION['user']['barangay_assigned'];
        $reports = ReportModel::getAllByBarangay($barangay);

        $active = count(array_filter($reports, fn($r) => $r['status'] === 'active'));
        $underReview = count(array_filter($reports, fn($r) => $r['status'] === 'under_review'));
        $cleared = count(array_filter($reports, fn($r) => $r['status'] === 'cleared'));

        include __DIR__ . '/../../public/crew/dashboard.php';
    }

    public function profile() {
        AuthMiddleware::requireRole(['crew']);
        include __DIR__ . '/../../public/crew/profile.php';
    }
}
