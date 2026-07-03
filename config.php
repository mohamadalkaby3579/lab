<?php
/**
 * ملف إعدادات الاتصال بقاعدة البيانات
 * نظام إدارة حضور طلاب مختبرات الحاسوب
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'lab_attendance_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات النظام
define('SYSTEM_NAME', 'نظام إدارة حضور مختبرات الحاسوب');
define('SYSTEM_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Baghdad');

// تعيين المنطقة الزمنية
date_default_timezone_set(TIMEZONE);

// فئة الاتصال بقاعدة البيانات
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci; SET time_zone = '+03:00';"
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode([
                'success' => false,
                'message' => 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // منع النسخ
    private function __clone() {}
    
    // منع إلغاء التسلسل
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// دالة مساعدة للحصول على اتصال قاعدة البيانات
function getDB() {
    return Database::getInstance()->getConnection();
}

// دالة لتنظيف المدخلات
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// دالة للرد بصيغة JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// دالة لترجمة اليوم للعربية
function getArabicDay($day) {
    $days = [
        'Saturday' => 'السبت',
        'Sunday' => 'الأحد',
        'Monday' => 'الاثنين',
        'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday' => 'الخميس',
        'Friday' => 'الجمعة'
    ];
    return $days[$day] ?? $day;
}

// دالة لترجمة الشهر للعربية
function getArabicMonth($month) {
    $months = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    return $months[(int)$month] ?? $month;
}

// دالة لتنسيق التاريخ بالعربية
function formatArabicDate($date) {
    $timestamp = strtotime($date);
    $day = getArabicDay(date('l', $timestamp));
    $dayNum = date('d', $timestamp);
    $month = getArabicMonth(date('n', $timestamp));
    $year = date('Y', $timestamp);
    return "$day، $dayNum $month $year";
}

// دالة للتحقق من تسجيل الدخول
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role'],
        'assigned_lab_id' => $_SESSION['assigned_lab_id'] ?? null
    ];
}

// دالة للتحقق من صلاحيات المدير
function checkAdmin() {
    $user = checkAuth();
    if ($user['role'] !== 'admin') {
        header('Location: index.php?error=unauthorized');
        exit;
    }
    return $user;
}

// دالة لتشفير كلمة المرور
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// دالة للتحقق من كلمة المرور
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
