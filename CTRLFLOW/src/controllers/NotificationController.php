<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class NotificationController {
    public function list() {
        AuthMiddleware::requireLogin();
        $uid = $_SESSION['user']['user_id'];
        $role = $_SESSION['user']['role'];

        $perPage = 20;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $perPage;

        if ($role === 'super_admin') {
            $totalCount = NotificationModel::getTotalNotifications();
            $rows = NotificationModel::getAllWithUserInfo($perPage, $offset);

            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND type != 'crew_follow_up' AND is_read = 0 LIMIT " . intval($perPage) . " OFFSET " . intval($offset));
            $stmt->execute([$uid]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nid) { NotificationModel::markRead($nid); }
        } else {
            NotificationModel::markAllReadForUser($uid);
            $totalCount = NotificationModel::getTotalNotificationsForUser($uid);
            $rows = NotificationModel::getByUser($uid, $perPage, $offset);
        }

        $totalPages = ceil($totalCount / $perPage);
        $pagination = ['current_page' => $page, 'total_pages' => $totalPages, 'total_records' => $totalCount, 'per_page' => $perPage, 'has_pagination' => $totalCount > $perPage];

        include __DIR__ . '/../../public/notifications/list.php';
    }

    public function json_unread_count() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        echo json_encode(['count' => NotificationModel::countUnreadForUser($_SESSION['user']['user_id'])]);
    }

    public function json_latest() {
        AuthMiddleware::requireLogin();
        header('Content-Type: application/json');
        echo json_encode(NotificationModel::getLatestForUser($_SESSION['user']['user_id'], 10));
    }

    public function mark_read() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT type FROM notifications WHERE notification_id = ?");
            $stmt->execute([$id]);
            $notification = $stmt->fetch();
            if ($notification && $notification['type'] !== 'crew_follow_up') NotificationModel::markRead($id);
        }

        $isAjax = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        if ($isAjax) { header('Content-Type: application/json'); echo json_encode(['ok' => true]); }
        else { header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/')); }
        exit;
    }

    public function mark_unread() {
        AuthMiddleware::requireLogin();
        $id = $_GET['id'] ?? null;
        if ($id) NotificationModel::markUnread($id);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    public function mark_all_read() {
        AuthMiddleware::requireLogin();
        $uid = $_SESSION['user']['user_id'];
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT notification_id FROM notifications WHERE user_id = ? AND type != 'crew_follow_up' AND is_read = 0");
        $stmt->execute([$uid]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nid) { NotificationModel::markRead($nid); }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { header('Content-Type: application/json'); echo json_encode(['ok' => true]); }
        else { header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/')); }
        exit;
    }
}
?>
