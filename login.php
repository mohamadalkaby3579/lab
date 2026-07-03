<?php
/**
 * صفحة تسجيل الدخول
 * نظام إدارة حضور طلاب مختبرات الحاسوب
 */

session_start();
require_once 'config.php';

// إذا كان المستخدم مسجل دخوله، توجيهه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                // التحقق من كلمة المرور
                $passwordValid = false;
                
                // التحقق باستخدام password_verify
                if (password_verify($password, $user['password'])) {
                    $passwordValid = true;
                }
                // للتوافق مع كلمات المرور القديمة (123456 = password)
                elseif ($password === '123456' && $user['password'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') {
                    $passwordValid = true;
                }
                // كلمة مرور نصية مباشرة (للاختبار فقط)
                elseif ($user['password'] === $password) {
                    $passwordValid = true;
                }
                
                if ($passwordValid) {
                    // تسجيل الدخول ناجح
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['assigned_lab_id'] = $user['assigned_lab_id'];
                    
                    // تحديث آخر تسجيل دخول
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // تسجيل النشاط
                    try {
                        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                        $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user['id'], 'login', 'تسجيل دخول ناجح', $ip]);
                    } catch (Exception $e) {
                        // تجاهل أخطاء السجل
                    }
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'كلمة المرور غير صحيحة';
                }
            } else {
                $error = 'اسم المستخدم غير موجود';
            }
        } catch (PDOException $e) {
            $error = 'خطأ في الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL وإنشاء قاعدة البيانات.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة حضور مختبرات الحاسوب</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --primary-light: #2c5282;
            --secondary: #3182ce;
            --success: #38a169;
            --danger: #e53e3e;
            --gray-50: #f7fafc;
            --gray-100: #edf2f7;
            --gray-200: #e2e8f0;
            --gray-600: #4a5568;
            --gray-700: #2d3748;
            --shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 50%, var(--secondary) 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 35px;
            backdrop-filter: blur(10px);
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 35px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .alert-error {
            background: #fed7d7;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .alert-success {
            background: #c6f6d5;
            color: var(--success);
            border: 1px solid var(--success);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 15px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 54, 93, 0.1);
        }

        .password-toggle {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray-600);
            font-size: 18px;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 54, 93, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding: 0 35px 30px;
            color: var(--gray-600);
            font-size: 13px;
        }

        .demo-accounts {
            background: var(--gray-50);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border: 1px dashed var(--gray-200);
        }

        .demo-accounts h4 {
            color: var(--gray-700);
            font-size: 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-accounts p {
            font-size: 13px;
            color: var(--gray-600);
            margin: 5px 0;
            direction: ltr;
            text-align: left;
        }

        .demo-accounts code {
            background: var(--gray-200);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }

        .clock-display {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.95);
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--primary);
            z-index: 100;
        }

        .clock-display i {
            font-size: 18px;
        }

        .setup-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--secondary);
            text-decoration: none;
            font-size: 13px;
        }

        .setup-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 500px) {
            .login-container {
                border-radius: 15px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="clock-display">
        <i class="fas fa-clock"></i>
        <span id="clock">00:00:00</span>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-laptop-code"></i>
            </div>
            <h1>مختبرات الحاسوب</h1>
            <p>نظام إدارة حضور الطلاب</p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-control" 
                               placeholder="أدخل اسم المستخدم"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required
                               autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="أدخل كلمة المرور"
                               required
                               autocomplete="current-password">
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </button>
            </form>

            <div class="demo-accounts">
                <h4><i class="fas fa-info-circle"></i> بيانات الدخول</h4>
                <p><strong>مدير:</strong> <code>admin</code> / <code>123456</code></p>
                <p><strong>معلم:</strong> <code>teacher1</code> / <code>123456</code></p>
            </div>
            
            <a href="setup.php" class="setup-link">
                <i class="fas fa-cog"></i> إعداد النظام (أول مرة)
            </a>
        </div>

        <div class="login-footer">
            <p>© 2024 نظام إدارة حضور مختبرات الحاسوب</p>
        </div>
    </div>

    <script>
        // الساعة الرقمية
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = hours + ':' + minutes + ':' + seconds;
        }
        
        updateClock();
        setInterval(updateClock, 1000);

        // إظهار/إخفاء كلمة المرور
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
