<?php
/**
 * ملف الإعداد الأولي للنظام
 * يقوم بإنشاء/تحديث كلمات المرور للمستخدمين
 * 
 * قم بتشغيل هذا الملف مرة واحدة فقط بعد استيراد قاعدة البيانات
 * http://localhost/lab-attendance/setup.php
 */

require_once 'config.php';

echo "<html dir='rtl'><head><meta charset='UTF-8'><title>إعداد النظام</title>";
echo "<style>body{font-family:Tahoma,Arial;padding:40px;background:#f5f5f5;} .box{background:white;padding:30px;border-radius:10px;max-width:600px;margin:auto;box-shadow:0 2px 10px rgba(0,0,0,0.1);} h1{color:#1a365d;} .success{color:green;} .error{color:red;} .info{color:#3182ce;}</style>";
echo "</head><body><div class='box'>";

echo "<h1>🔧 إعداد نظام إدارة حضور مختبرات الحاسوب</h1>";

try {
    $db = getDB();
    
    // كلمة المرور الافتراضية
    $defaultPassword = '123456';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    echo "<h3>📋 تحديث كلمات المرور:</h3>";
    
    // تحديث كلمات المرور لجميع المستخدمين
    $stmt = $db->query("SELECT id, username, full_name FROM users");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $user['id']]);
        echo "<p class='success'>✅ تم تحديث كلمة مرور المستخدم: <strong>{$user['username']}</strong> ({$user['full_name']})</p>";
    }
    
    echo "<hr>";
    echo "<h3>📝 بيانات تسجيل الدخول:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#1a365d;color:white;'><th>المستخدم</th><th>اسم المستخدم</th><th>كلمة المرور</th><th>الصلاحية</th></tr>";
    
    $stmt = $db->query("SELECT username, full_name, role FROM users WHERE is_active = 1");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $roleAr = $user['role'] === 'admin' ? 'مدير' : ($user['role'] === 'teacher' ? 'معلم' : 'مشرف');
        echo "<tr><td>{$user['full_name']}</td><td><code>{$user['username']}</code></td><td><code>{$defaultPassword}</code></td><td>{$roleAr}</td></tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3 class='info'>🚀 الخطوة التالية:</h3>";
    echo "<p>انتقل إلى صفحة تسجيل الدخول: <a href='login.php' style='color:#3182ce;font-weight:bold;'>تسجيل الدخول</a></p>";
    
    echo "<hr>";
    echo "<p style='color:#e53e3e;'><strong>⚠️ تحذير:</strong> احذف هذا الملف (setup.php) بعد الانتهاء من الإعداد لأسباب أمنية!</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ خطأ في قاعدة البيانات: " . $e->getMessage() . "</p>";
    echo "<p class='info'>تأكد من:</p>";
    echo "<ul>";
    echo "<li>تشغيل خدمة MySQL في XAMPP</li>";
    echo "<li>استيراد ملف database.sql في phpMyAdmin</li>";
    echo "<li>صحة إعدادات الاتصال في config.php</li>";
    echo "</ul>";
}

echo "</div></body></html>";
?>
