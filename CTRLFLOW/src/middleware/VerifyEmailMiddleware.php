<?php
class VerifyEmailMiddleware {
  public static function enforce() {
    if (empty($_SESSION['user'])) return;
    $u = $_SESSION['user'];
    if ($u['role'] === 'citizen') {
      if (!$u['is_verified']) { echo 'Please verify your email to continue.'; exit; }
      if ($u['password_reset_required']) {
        header('Location: /CTRLFLOW/public/set_new_password.php?uid='.$u['user_id']);
        exit;
      }
    }
  }
}
?>
