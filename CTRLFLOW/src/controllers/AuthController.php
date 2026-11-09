<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {

    private function dashboardRouteFor($role) {
        if ($role === 'super_admin') return 'admin/dashboard';
        if ($role === 'crew') return 'crew/dashboard';
        return 'citizen/dashboard';
    }

    public function login() {
        if (!empty($_SESSION['user'])) {
            header("Location: /CTRLFLOW/public/?route=" . $this->dashboardRouteFor($_SESSION['user']['role']));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $user = UserModel::getByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (!$user['is_verified']) {
                    Flash::set('danger', 'Please verify your email first.');
                    header("Location: /CTRLFLOW/public/login.php");
                    exit;
                }
                if ($user['password_reset_required']) {
                    header("Location: /CTRLFLOW/public/set_new_password.php?uid=".$user['user_id']);
                    exit;
                }
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                header("Location: /CTRLFLOW/public/?route=" . $this->dashboardRouteFor($user['role']));
                exit;
            }

            Flash::set('danger', 'Invalid email or password.');
            header("Location: /CTRLFLOW/public/login.php");
            exit;
        }

        include __DIR__ . '/../../public/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: /CTRLFLOW/public/login.php");
        exit;
    }

    public function verify() {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            Flash::set('danger', 'Invalid verification token.');
            header("Location: /CTRLFLOW/public/login.php");
            exit;
        }

        $user = UserModel::getByToken($token);
        if (!$user) {
            Flash::set('danger', 'This verification link is invalid or already used.');
            header("Location: /CTRLFLOW/public/login.php");
            exit;
        }

        UserModel::markEmailVerified($user['user_id']);

        NotificationModel::create([
            'user_id' => $user['user_id'],
            'type' => 'account_verified',
            'title' => 'Email Verified',
            'message' => 'Your email has been verified. Please set your password to complete account setup.',
            'link' => "/CTRLFLOW/public/?route=auth/set_new_password"
        ]);

        Flash::set('success', 'Email verified. Please set your new password.');
        header("Location: /CTRLFLOW/public/set_new_password.php?uid=" . $user['user_id']);
        exit;
    }

    public function reset_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid = $_POST['uid'];
            $pass = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if ($pass !== $confirm) {
                Flash::set('danger', 'Passwords do not match.');
                header("Location: /CTRLFLOW/public/set_new_password.php?uid=$uid");
                exit;
            }

            UserModel::updatePassword($uid, password_hash($pass, PASSWORD_DEFAULT));
            UserModel::clearVerificationToken($uid);

            NotificationModel::create([
                'user_id' => $uid, 'type' => 'password_created', 'title' => 'Password Set',
                'message' => 'Your password has been set successfully. You may now log in.',
                'link' => "/CTRLFLOW/public/?route=auth/login"
            ]);

            Flash::set('success', 'Password created successfully. You may now log in.');
            header("Location: /CTRLFLOW/public/login.php");
            exit;
        }

        include __DIR__ . '/../../public/set_new_password.php';
    }

    public function change_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid = $_POST['uid'];
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $user = UserModel::getById($uid);

            if (!$user) {
                Flash::set('danger', 'User not found.');
            } elseif (!password_verify($current, $user['password_hash'])) {
                Flash::set('danger', 'Current password is incorrect.');
            } elseif (password_verify($new, $user['password_hash'])) {
                Flash::set('danger', 'New password cannot be the same as the old one.');
            } elseif ($new !== $confirm) {
                Flash::set('danger', 'New passwords do not match.');
            } else {
                UserModel::updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
                NotificationModel::create([
                    'user_id' => $uid, 'type' => 'password_changed', 'title' => 'Password Changed',
                    'message' => 'Your account password was changed successfully.',
                    'link' => "/CTRLFLOW/public/?route=auth/login"
                ]);
                Flash::set('success', 'Password updated successfully.');
            }

            $profileRoute = $user['role'] === 'citizen' ? 'citizen/profile'
                : ($user['role'] === 'crew' ? 'crew/profile' : 'admin/profile');
            header("Location: /CTRLFLOW/public/?route=$profileRoute");
            exit;
        }
    }
}
