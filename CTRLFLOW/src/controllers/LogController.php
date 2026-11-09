<?php
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class LogController {
    public function index() {
        AuthMiddleware::requireRole(['super_admin']);
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action']))  $filters['action'] = $_GET['action'];
        if (!empty($_GET['table_name'])) $filters['table_name'] = $_GET['table_name'];
        if (!empty($_GET['from']))    $filters['from'] = $_GET['from'];
        if (!empty($_GET['to']))      $filters['to'] = $_GET['to'];
        $logs = LogModel::getLogs($filters);
        include __DIR__ . '/../../public/logs/index.php';
    }
}
