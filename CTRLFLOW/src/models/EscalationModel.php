<?php
require_once __DIR__ . '/../../config/db.php';

class EscalationModel {

  public static function generateEscalationCode() {
      $pdo = getDB();
      $year = date("Y");
      $prefix = "ESC-$year-";
      $stmt = $pdo->prepare("SELECT escalation_code FROM escalations WHERE escalation_code LIKE ? ORDER BY CAST(SUBSTRING_INDEX(escalation_code, '-', -1) AS UNSIGNED) DESC LIMIT 1");
      $stmt->execute([$prefix . "%"]);
      $last = $stmt->fetchColumn();
      $nextNum = $last ? ((int)substr($last, -5)) + 1 : 1;
      return $prefix . str_pad($nextNum, 5, "0", STR_PAD_LEFT);
  }

  public static function create($data) {
      $pdo = getDB();
      if (empty($data['escalation_code'])) $data['escalation_code'] = self::generateEscalationCode();

      $sql = "INSERT INTO escalations (escalation_code, report_id, escalation_date, reporting_barangay, reporting_tel, reporting_email, reason, details, receiving_office, receiving_barangay, escalation_status, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $data['escalation_code'], $data['report_id'], $data['escalation_date'] ?? date('Y-m-d'),
        $data['reporting_barangay'] ?? null, $data['reporting_tel'] ?? null, $data['reporting_email'] ?? null,
        $data['reason'] ?? null, $data['details'] ?? null, $data['receiving_office'] ?? 'City Engineering Office',
        $data['receiving_barangay'] ?? null, 'pending', $data['created_by'] ?? null,
      ]);
      return $pdo->lastInsertId();
  }

  public static function getById($id) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT * FROM escalations WHERE escalation_id=?");
      $stmt->execute([$id]);
      return $stmt->fetch();
  }

  public static function getAll() {
      $pdo = getDB();
      return $pdo->query("SELECT e.*, r.report_code, r.barangay AS report_barangay, r.landmark FROM escalations e LEFT JOIN blockage_reports r ON r.report_id = e.report_id ORDER BY e.created_at DESC")->fetchAll();
  }

  public static function getSentBy($userId) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT e.*, r.report_code, r.barangay AS report_barangay, r.landmark FROM escalations e LEFT JOIN blockage_reports r ON r.report_id = e.report_id WHERE e.created_by = ? ORDER BY e.created_at DESC");
      $stmt->execute([$userId]);
      return $stmt->fetchAll();
  }

  public static function getIncomingFor($barangay) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT e.*, r.report_code, r.barangay AS report_barangay, r.landmark FROM escalations e LEFT JOIN blockage_reports r ON r.report_id = e.report_id WHERE e.receiving_barangay = ? AND e.escalation_status = 'pending' ORDER BY e.escalation_date DESC");
      $stmt->execute([$barangay]);
      return $stmt->fetchAll();
  }

  public static function getReceivedFor($barangay) {
      $pdo = getDB();
      $stmt = $pdo->prepare("SELECT e.*, r.report_code, r.barangay AS report_barangay, r.landmark FROM escalations e LEFT JOIN blockage_reports r ON r.report_id = e.report_id WHERE e.receiving_barangay = ? AND e.escalation_status IN ('received','completed') ORDER BY e.date_received DESC");
      $stmt->execute([$barangay]);
      return $stmt->fetchAll();
  }

  public static function receive($id, $officer, $receivedByUserId) {
      $pdo = getDB();
      $stmt = $pdo->prepare("UPDATE escalations SET escalation_status='received', receiving_officer=?, date_received=CURDATE(), received_by=? WHERE escalation_id=?");
      return $stmt->execute([$officer, $receivedByUserId, $id]);
  }

  public static function update($id, $data) {
      $pdo = getDB();
      $stmt = $pdo->prepare("UPDATE escalations SET action_taken=?, remarks=?, escalation_status=? WHERE escalation_id=?");
      return $stmt->execute([$data['action_taken'] ?? null, $data['remarks'] ?? null, $data['escalation_status'] ?? 'pending', $id]);
  }
}
