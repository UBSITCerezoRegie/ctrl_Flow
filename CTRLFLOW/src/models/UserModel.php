<?php
require_once __DIR__ . '/../../config/db.php';

class UserModel {

    public static function getByEmail($email) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByToken($token) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function emailExists($email) { return self::getByEmail($email) ? true : false; }

    public static function markEmailVerified($uid) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?")->execute([$uid]);
    }

    public static function clearVerificationToken($uid) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE users SET verification_token = NULL WHERE user_id = ?")->execute([$uid]);
    }

    public static function createCitizenUser($email, $plain, $token, $fullName = null) {
        if (self::emailExists($email)) return false;
        $pdo = getDB();
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, full_name, is_verified, verification_token, password_reset_required) VALUES (?, ?, 'citizen', ?, 0, ?, 1)");
        $stmt->execute([$email, $hash, $fullName, $token]);
        return $pdo->lastInsertId();
    }

    public static function updatePassword($uid, $hash) {
        $pdo = getDB();
        return $pdo->prepare("UPDATE users SET password_hash = ?, password_reset_required = 0 WHERE user_id = ?")->execute([$hash, $uid]);
    }

    public static function delete($id) {
        $pdo = getDB();
        return $pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$id]);
    }

    public static function createCrewMember($email, $barangay_assigned, $plain, $token, $fullName = null) {
        if (self::emailExists($email)) return false;
        $pdo = getDB();
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, full_name, is_verified, verification_token, barangay_assigned, password_reset_required) VALUES (?, ?, 'crew', ?, 0, ?, ?, 1)");
        $stmt->execute([$email, $hash, $fullName, $token, $barangay_assigned]);
        return $pdo->lastInsertId();
    }

    public static function getCrewByBarangay($barangay) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role='crew' AND barangay_assigned=? LIMIT 1");
        $stmt->execute([$barangay]);
        return $stmt->fetch();
    }

    public static function getAllCrewByBarangay($barangay) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role='crew' AND barangay_assigned=?");
        $stmt->execute([$barangay]);
        return $stmt->fetchAll();
    }

    public static function getAllByRole($role) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role=? ORDER BY created_at DESC");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
}
?>
