<?php
require_once __DIR__ . '/../../config/db.php';

class ReportModel {

  public static function generateReportCode() {
      $pdo = getDB();
      $year = date("Y");
      $prefix = "CF-$year-";
      $stmt = $pdo->prepare("SELECT report_code FROM blockage_reports WHERE report_code LIKE ? ORDER BY CAST(SUBSTRING_INDEX(report_code, '-', -1) AS UNSIGNED) DESC LIMIT 1");
      $stmt->execute([$prefix . "%"]);
      $last = $stmt->fetchColumn();
      $nextNum = $last ? ((int)substr($last, -5)) + 1 : 1;
      return $prefix . str_pad($nextNum, 5, "0", STR_PAD_LEFT);
  }

  public static function create($data) {
      if (empty(trim($data['barangay'] ?? ''))) throw new PDOException("Barangay is required.");
      $pdo = getDB();
      if (empty($data['report_code'])) $data['report_code'] = self::generateReportCode();

      $sql = "INSERT INTO blockage_reports (report_code, reported_by_user_id, barangay, landmark, material_type, severity, is_spilling, description, photo_path, status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $data['report_code'], $data['reported_by_user_id'] ?? null, $data['barangay'], $data['landmark'] ?? null,
        $data['material_type'] ?? 'unknown_other', $data['severity'] ?? 'medium', !empty($data['is_spilling']) ? 1 : 0,
        $data['description'] ?? null, $data['photo_path'] ?? null, $data['status'] ?? 'active', $data['created_by'] ?? null,
      ]);
      return $pdo->lastInsertId();
  }

  public static function getById($id) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM blockage_reports WHERE report_id=?");
      $stmt->execute([$id]);
      return $stmt->fetch();
  }

  public static function getAll() {
      return getDB()->query("SELECT * FROM blockage_reports ORDER BY created_at DESC")->fetchAll();
  }

  public static function getAllByBarangay($b) {
      if (empty($b)) return [];
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM blockage_reports WHERE barangay=? ORDER BY created_at DESC");
      $stmt->execute([$b]);
      return $stmt->fetchAll();
  }

  public static function getAllByReporter($userId) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM blockage_reports WHERE reported_by_user_id=? ORDER BY created_at DESC");
      $stmt->execute([$userId]);
      return $stmt->fetchAll();
  }

  public static function getAllFiltered($q = '', $barangay = '', $status = '') {
      $pdo = getDB();
      $sql = "SELECT * FROM blockage_reports WHERE 1=1 ";
      $params = [];
      if (!empty($q)) {
          $sql .= "AND (report_code LIKE ? OR landmark LIKE ? OR description LIKE ? OR barangay LIKE ?) ";
          $like = '%' . $q . '%';
          $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
      }
      if (!empty($barangay)) { $sql .= "AND barangay = ? "; $params[] = $barangay; }
      if (!empty($status)) { $sql .= "AND status = ? "; $params[] = $status; }
      $sql .= "ORDER BY created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }

  public static function getAllByBarangayFiltered($b, $q = '', $status = '') {
      if (empty($b)) return [];
      $pdo = getDB();
      $sql = "SELECT * FROM blockage_reports WHERE barangay = ? ";
      $params = [$b];
      if (!empty($q)) {
          $like = '%' . $q . '%';
          $sql .= "AND (report_code LIKE ? OR landmark LIKE ? OR description LIKE ?) ";
          $params[] = $like; $params[] = $like; $params[] = $like;
      }
      if (!empty($status)) { $sql .= "AND status = ? "; $params[] = $status; }
      $sql .= "ORDER BY created_at DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->fetchAll();
  }

  public static function update($id, $data) {
      $pdo = getDB();
      $sql = "UPDATE blockage_reports SET barangay=?, landmark=?, material_type=?, severity=?, is_spilling=?, description=? WHERE report_id=?";
      return $pdo->prepare($sql)->execute([
        $data['barangay'], $data['landmark'] ?? null, $data['material_type'] ?? 'unknown_other',
        $data['severity'] ?? 'medium', !empty($data['is_spilling']) ? 1 : 0, $data['description'] ?? null, $id
      ]);
  }

  public static function updateStatus($id, $status, $notes = null, $crewId = null) {
      $pdo = getDB();
      $resolvedAt = ($status === 'cleared') ? date('Y-m-d H:i:s') : null;
      $stmt = $pdo->prepare("UPDATE blockage_reports SET status=?, status_notes=?, assigned_crew_id = COALESCE(?, assigned_crew_id), resolved_at=? WHERE report_id=?");
      return $stmt->execute([$status, $notes, $crewId, $resolvedAt, $id]);
  }

  public static function delete($id) {
      $pdo = getDB();
      $stmt = $pdo->prepare("DELETE FROM blockage_reports WHERE report_id=?");
      return $stmt->execute([$id]);
  }

  public static function createFromImport($data, $userId = null) {
      $data['reported_by_user_id'] = (!empty($userId) && is_numeric($userId)) ? $userId : null;
      return self::create($data);
  }

  public static function existsByCode($code) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM blockage_reports WHERE report_code=?");
      $stmt->execute([$code]);
      return $stmt->fetch()['c'] > 0;
  }

  public static function exportAll() { return self::getAll(); }

  public static function getStatusStats() {
      $pdo = getDB();
      return $pdo->query("SELECT COUNT(CASE WHEN status = 'active' THEN 1 END) as active, COUNT(CASE WHEN status = 'under_review' THEN 1 END) as under_review, COUNT(CASE WHEN status = 'cleared' THEN 1 END) as cleared FROM blockage_reports")->fetch();
  }

  public static function getSeverityStats() {
      $pdo = getDB();
      return $pdo->query("SELECT COUNT(CASE WHEN severity = 'high' THEN 1 END) as high, COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium, COUNT(CASE WHEN severity = 'low' THEN 1 END) as low, COUNT(CASE WHEN is_spilling = 1 THEN 1 END) as spilling FROM blockage_reports")->fetch();
  }

  public static function getMaterialStats() {
      $pdo = getDB();
      return $pdo->query("SELECT COUNT(CASE WHEN material_type = 'garbage_plastic' THEN 1 END) as garbage_plastic, COUNT(CASE WHEN material_type = 'silt_mud_leaves' THEN 1 END) as silt_mud_leaves, COUNT(CASE WHEN material_type = 'grease_fatberg' THEN 1 END) as grease_fatberg, COUNT(CASE WHEN material_type = 'unknown_other' THEN 1 END) as unknown_other FROM blockage_reports")->fetch();
  }

  public static function getMonthlyStats($months = 6) {
      $pdo = getDB();
      $months = intval($months);
      $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as reports FROM blockage_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL " . $months . " MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT " . $months);
      $stmt->execute();
      $results = $stmt->fetchAll();
      $formatted = [];
      foreach ($results as $result) {
          $date = DateTime::createFromFormat('Y-m', $result['month']);
          $formatted[] = ['month' => $date ? $date->format('M Y') : $result['month'], 'reports' => $result['reports']];
      }
      return $formatted;
  }

  public static function getBarangayStats() {
      $pdo = getDB();
      return $pdo->query("SELECT barangay, COUNT(*) as count FROM blockage_reports GROUP BY barangay ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
  }
}
