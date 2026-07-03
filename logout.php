<?php
/**
 * ملف تسجيل الخروج
 */

session_start();
require_once 'config.php';

// تسجيل نشاط الخروج
if (isset($_SESSION['user_id'])) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], 'logout', 'تسجيل خروج', $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Exception $e) {
        // تجاهل الأخطاء
    }
}

// مسح الجلسة
session_unset();
session_destroy();

// التوجيه لصفحة تسجيل الدخول
header('Location: login.php');
exit;
?>
