<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../helpers/Flash.php';

class UserController {
    public function delete_user() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) die("Missing user ID");

        $user = UserModel::getById($id);
        if ($user && $user['role'] === 'super_admin') {
            Flash::set('danger','Cannot delete super admin account.');
            header("Location: /CTRLFLOW/public/?route=admin/users");
            exit;
        }

        UserModel::delete($id);
        LogModel::insertLog($_SESSION['user']['user_id'], 'delete', 'users', $id, null, null, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        Flash::set('success','User deleted.');
        $redirectRoute = ($user['role'] === 'crew') ? 'user/create_crew' : 'admin/users';
        header("Location: /CTRLFLOW/public/?route=$redirectRoute");
        exit;
    }

    public function create_citizen_user() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::set('danger', 'Invalid request.');
            header("Location: /CTRLFLOW/public/?route=admin/users");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('danger', 'Enter a valid email.');
            header("Location: /CTRLFLOW/public/?route=admin/users");
            exit;
        }

        if (UserModel::emailExists($email)) {
            Flash::set('danger', 'Email already registered.');
            header("Location: /CTRLFLOW/public/?route=admin/users");
            exit;
        }

        try {
            $token = bin2hex(random_bytes(16));
            $tempPass = bin2hex(random_bytes(6));
            $uid = UserModel::createCitizenUser($email, $tempPass, $token, $fullName);
            EmailHelper::sendVerificationEmail($email, $token);

            LogModel::insertLog($_SESSION['user']['user_id'], 'create', 'users', $uid, null, json_encode(['role'=>'citizen','email'=>$email]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

            Flash::set('success', 'Citizen account created and verification email sent.');
        } catch (Exception $e) {
            Flash::set('danger', 'Error: '.$e->getMessage());
        }

        header("Location: /CTRLFLOW/public/?route=admin/users");
        exit;
    }

    public function create_crew() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $fullName = trim($_POST['full_name'] ?? '');
            $barangay_assigned = trim($_POST['barangay_assigned']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('danger','Invalid email.');
                include __DIR__ . '/../../public/admin/create_crew.php';
                return;
            }

            $tempPass = bin2hex(random_bytes(6));
            $token = bin2hex(random_bytes(16));

            try {
                $uid = UserModel::createCrewMember($email, $barangay_assigned, $tempPass, $token, $fullName);
                EmailHelper::sendVerificationEmail($email, $token);
                LogModel::insertLog($_SESSION['user']['user_id'],'create','users',$uid,null,json_encode(['role'=>'crew','email'=>$email]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
                Flash::set('success','Cleanup crew account created. Verification email sent.');
            } catch (Exception $e) {
                Flash::set('danger', $e->getMessage());
                header("Location: /CTRLFLOW/public/?route=user/create_crew");
                exit;
            }

            header("Location: /CTRLFLOW/public/?route=user/create_crew");
            exit;
        }

        include __DIR__ . '/../../public/admin/create_crew.php';
    }
}
