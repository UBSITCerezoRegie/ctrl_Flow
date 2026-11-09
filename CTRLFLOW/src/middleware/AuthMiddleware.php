<?php
class AuthMiddleware {
  public static function requireLogin() {
    if (empty($_SESSION['user'])) { header('Location: /CTRLFLOW/public/login.php'); exit; }
  }

  public static function requireRole($roles) {
    self::requireLogin();
    $roles = (array)$roles;
    $user = $_SESSION['user'];
    if (!in_array($user['role'], $roles)) {
      header("Location: /CTRLFLOW/public/error.php?code=403&msg=Access+Denied");
      exit;
    }
  }

  public static function requireVerified() {
    self::requireLogin();
    $u = $_SESSION['user'];
    if (!$u['is_verified']) {
      header("Location: /CTRLFLOW/public/error.php?code=403&msg=Please+verify+your+email+first");
      exit;
    }
    if ($u['password_reset_required']) {
      header('Location: /CTRLFLOW/public/set_new_password.php?uid='.$u['user_id']);
      exit;
    }
  }
}
?>
