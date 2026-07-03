<?php
/**
 * ملف اختبار الاتصال بقاعدة البيانات
 */

echo "<html dir='rtl'><head><meta charset='UTF-8'><title>اختبار النظام</title>";
echo "<style>
body { font-family: Tahoma, Arial; padding: 40px; background: #f5f5f5; direction: rtl; }
.box { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #1a365d; margin-bottom: 20px; }
.success { color: #38a169; background: #c6f6d5; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: #e53e3e; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: #3182ce; background: #bee3f8; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { border: 1px solid #e2e8f0; padding: 10px; text-align: right; }
th { background: #1a365d; color: white; }
</style></head><body><div class='box'>";

echo "<h1>🔍 اختبار نظام إدارة حضور مختبرات الحاسوب</h1>";

// اختبار 1: ملف الإعدادات
echo "<h3>1. فحص ملفات النظام</h3>";
$files = ['config.php', 'api.php', 'login.php', 'logout.php', 'index.php', 'export_pdf.php', 'export_excel.php', 'setup.php'];
$allFilesExist = true;

echo "<table><tr><th>الملف</th><th>الحالة</th></tr>";
foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅ موجود' : '❌ غير موجود';
    $class = $exists ? 'success' : 'error';
    echo "<tr><td>$file</td><td class='$class'>$status</td></tr>";
    if (!$exists) $allFilesExist = false;
}
echo "</table>";

// اختبار 2: الاتصال بقاعدة البيانات
echo "<h3>2. اختبار الاتصال بقاعدة البيانات</h3>";

try {
    require_once 'config.php';
    $db = getDB();
    echo "<div class='success'>✅ تم الاتصال بقاعدة البيانات بنجاح</div>";
    
    // اختبار 3: فحص الجداول
    echo "<h3>3. فحص جداول قاعدة البيانات</h3>";
    $tables = ['users', 'labs', 'sections', 'stages', 'study_periods', 'subjects', 'students', 'attendance', 'activity_log'];
    
    echo "<table><tr><th>الجدول</th><th>الحالة</th><th>عدد السجلات</th></tr>";
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $row = $stmt->fetch();
            $count = $row['count'];
            echo "<tr><td>$table</td><td class='success'>✅ موجود</td><td>$count</td></tr>";
        } catch (PDOException $e) {
            echo "<tr><td>$table</td><td class='error'>❌ غير موجود</td><td>-</td></tr>";
        }
    }
    echo "</table>";
    
    // اختبار 4: فحص المستخدمين
    echo "<h3>4. فحص المستخدمين</h3>";
    try {
        $stmt = $db->query("SELECT id, username, full_name, role, is_active FROM users");
        $users = $stmt->fetchAll();
        
        if (count($users) > 0) {
            echo "<table><tr><th>ID</th><th>اسم المستخدم</th><th>الاسم الكامل</th><th>الصلاحية</th><th>نشط</th></tr>";
            foreach ($users as $user) {
                $active = $user['is_active'] ? '✅' : '❌';
                echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['full_name']}</td><td>{$user['role']}</td><td>$active</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ لا يوجد مستخدمين في قاعدة البيانات</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ خطأ في جلب المستخدمين: " . $e->getMessage() . "</div>";
    }
    
    // اختبار 5: اختبار كلمة المرور
    echo "<h3>5. اختبار كلمة المرور</h3>";
    try {
        $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $user = $stmt->fetch();
        
        if ($user) {
            $testPassword = '123456';
            $isValid = password_verify($testPassword, $user['password']);
            
            if ($isValid) {
                echo "<div class='success'>✅ كلمة المرور '123456' تعمل بشكل صحيح للمستخدم admin</div>";
            } else {
                echo "<div class='error'>❌ كلمة المرور '123456' لا تعمل. يرجى تشغيل setup.php</div>";
                echo "<div class='info'>💡 قم بزيارة <a href='setup.php'>setup.php</a> لإصلاح كلمات المرور</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<div class='error'>❌ خطأ: " . $e->getMessage() . "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage() . "</div>";
    echo "<div class='info'>💡 تأكد من:
    <ul>
        <li>تشغيل خدمة MySQL في XAMPP</li>
        <li>إنشاء قاعدة البيانات 'lab_attendance_system'</li>
        <li>استيراد ملف database.sql في phpMyAdmin</li>
    </ul>
    </div>";
}

echo "<hr>";
echo "<h3>📋 الخطوات التالية:</h3>";
echo "<ol>";
echo "<li>إذا كانت قاعدة البيانات غير موجودة: افتح phpMyAdmin وقم باستيراد ملف <code>database.sql</code></li>";
echo "<li>إذا كانت كلمات المرور لا تعمل: قم بزيارة <a href='setup.php'>setup.php</a></li>";
echo "<li>بعد الانتهاء: قم بزيارة <a href='login.php'>صفحة تسجيل الدخول</a></li>";
echo "</ol>";

echo "</div></body></html>";
?>
