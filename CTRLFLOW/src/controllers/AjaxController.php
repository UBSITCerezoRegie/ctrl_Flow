<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AjaxController {
    public function check_email() {
        header('Content-Type: application/json');
        $email = $_GET['email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['valid' => false, 'message' => 'Invalid email format']);
            return;
        }
        $exists = UserModel::emailExists($email);
        echo json_encode($exists
            ? ['valid' => false, 'message' => 'Email already registered']
            : ['valid' => true, 'message' => 'Email is available']);
    }

    public function search_barangay() {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../helpers/BarangayHelper.php';
        $q = strtolower(trim($_GET['q'] ?? ''));
        $all = BarangayHelper::getAll();
        $filtered = array_filter($all, function($b) use ($q) { return $q === '' || strpos(strtolower($b), $q) !== false; });
        echo json_encode(array_values($filtered));
    }

    public function fetch_crew() {
        header("Content-Type: application/json");
        $pdo = getDB();
        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $sql = "SELECT * FROM users WHERE role='crew'";
        $params = [];
        if ($q !== '') { $sql .= " AND email LIKE ?"; $params[] = "%$q%"; }
        if ($barangay !== '') { $sql .= " AND barangay_assigned LIKE ?"; $params[] = "%$barangay%"; }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public function fetch_citizen_users() {
        header("Content-Type: application/json");
        $pdo = getDB();
        $q = trim($_GET['q'] ?? '');
        $sql = "SELECT * FROM users WHERE role = 'citizen'";
        $params = [];
        if ($q !== '') {
            $sql .= " AND (email LIKE ? OR full_name LIKE ?)";
            $like = "%$q%"; $params[] = $like; $params[] = $like;
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public function fetch_reports() {
        header("Content-Type: application/json");
        $pdo = getDB();
        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $userRole = $_SESSION['user']['role'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        $sql = "SELECT * FROM blockage_reports WHERE 1=1";
        $params = [];

        if ($userRole === 'crew' && $userBarangay !== '') { $sql .= " AND barangay = ?"; $params[] = $userBarangay; }

        if ($q !== '') {
            $sql .= " AND (report_code LIKE ? OR landmark LIKE ? OR description LIKE ? OR LOWER(barangay) LIKE ?)";
            $like = "%$q%";
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = strtolower($like);
        }
        if ($barangay !== '') { $sql .= " AND barangay LIKE ?"; $params[] = "%$barangay%"; }
        if ($status !== '') { $sql .= " AND status = ?"; $params[] = $status; }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public function fetch_escalations() {
        header("Content-Type: application/json");
        $pdo = getDB();
        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        $sql = "SELECT e.*, r.report_code, r.barangay AS report_barangay FROM escalations e LEFT JOIN blockage_reports r ON r.report_id = e.report_id WHERE 1=1";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (e.escalation_code LIKE ? OR r.report_code LIKE ? OR e.details LIKE ? OR e.reason LIKE ? OR e.escalation_status LIKE ?)";
            $like = "%$q%";
            $params = [$like, $like, $like, $like, $like];
        }
        if ($barangay !== '') { $sql .= " AND e.receiving_barangay LIKE ?"; $params[] = "%$barangay%"; }

        $sql .= " ORDER BY e.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    }

    public function fetch_audit_logs() {
        header("Content-Type: application/json");
        $filters = [];
        if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
        if (!empty($_GET['action']))  $filters['action'] = $_GET['action'];
        if (!empty($_GET['table_name'])) $filters['table_name'] = $_GET['table_name'];
        if (!empty($_GET['from']))    $filters['from'] = $_GET['from'];
        if (!empty($_GET['to']))      $filters['to'] = $_GET['to'];
        echo json_encode(LogModel::getLogs($filters));
    }

    public function check_current_password() {
        header('Content-Type: application/json');
        $current = $_POST['current_password'] ?? '';
        $user_id = $_SESSION['user']['user_id'] ?? null;
        if (!$user_id) { echo json_encode(['valid' => false, 'message' => 'Not logged in']); return; }
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $hashed = $stmt->fetchColumn();
        $valid = password_verify($current, $hashed);
        echo json_encode(['valid' => $valid, 'message' => $valid ? 'Password correct' : 'Current password is incorrect']);
    }
}
