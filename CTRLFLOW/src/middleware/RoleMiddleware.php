<?php
class RoleMiddleware {
  public static function ensureBarangayMatch($reportBarangay) {
    if (empty($_SESSION['user'])) return false;
    $u = $_SESSION['user'];
    if ($u['role'] === 'super_admin') return true;
    if ($u['role'] === 'crew') return $u['barangay_assigned'] === $reportBarangay;
    return false;
  }
}
?>
