-- =============================================
-- نظام إدارة حضور طلاب مختبرات الحاسوب
-- قاعدة البيانات: lab_attendance_system
-- الإصدار 2.0 - مع الشعب ونظام تسجيل الدخول
-- =============================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS lab_attendance_system 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lab_attendance_system;

-- =============================================
-- حذف الجداول القديمة بالترتيب الصحيح
-- =============================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_log;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sections;
DROP TABLE IF EXISTS study_periods;
DROP TABLE IF EXISTS stages;
DROP TABLE IF EXISTS labs;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- جدول المختبرات
-- =============================================
CREATE TABLE labs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_name VARCHAR(100) NOT NULL,
    lab_supervisor VARCHAR(100) NOT NULL,
    capacity INT DEFAULT 30,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال المختبرات الأربعة
INSERT INTO labs (lab_name, lab_supervisor, capacity) VALUES
('مختبر الحاسوب 1', 'أ. محمد أحمد', 30),
('مختبر الحاسوب 2', 'أ. علي حسين', 25),
('مختبر الحاسوب 3', 'أ. سارة محمود', 28),
('مختبر الحاسوب 4', 'أ. فاطمة علي', 32);

-- =============================================
-- جدول الشعب
-- =============================================
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(10) NOT NULL,
    section_label VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال الشعب
INSERT INTO sections (section_name, section_label) VALUES
('A', 'الشعبة A'),
('B', 'الشعبة B'),
('C', 'الشعبة C'),
('D', 'الشعبة D');

-- =============================================
-- جدول المراحل الدراسية
-- =============================================
CREATE TABLE stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stage_name VARCHAR(100) NOT NULL,
    stage_order INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال المراحل الدراسية
INSERT INTO stages (stage_name, stage_order) VALUES
('المرحلة الأولى', 1),
('المرحلة الثانية', 2),
('المرحلة الثالثة', 3),
('المرحلة الرابعة', 4);

-- =============================================
-- جدول الفترات الدراسية (صباحي/مسائي)
-- =============================================
CREATE TABLE study_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_name VARCHAR(50) NOT NULL,
    start_time TIME,
    end_time TIME,
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال الفترات الدراسية
INSERT INTO study_periods (period_name, start_time, end_time) VALUES
('الدراسة الصباحية', '08:00:00', '14:00:00'),
('الدراسة المسائية', '14:30:00', '20:00:00');

-- =============================================
-- جدول المستخدمين (للنظام)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'supervisor', 'teacher') DEFAULT 'teacher',
    assigned_lab_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_lab_id) REFERENCES labs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال مستخدمين افتراضيين
-- كلمة مرور للجميع: 123456
INSERT INTO users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$YourHashedPasswordHere123456789012345678901234567890', 'مدير النظام', 'admin@system.com', 'admin'),
('teacher1', '$2y$10$YourHashedPasswordHere123456789012345678901234567890', 'أ. محمد أحمد', 'teacher1@system.com', 'teacher'),
('teacher2', '$2y$10$YourHashedPasswordHere123456789012345678901234567890', 'أ. علي حسين', 'teacher2@system.com', 'teacher');

-- =============================================
-- جدول المواد الدراسية
-- =============================================
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(150) NOT NULL,
    subject_code VARCHAR(20),
    stage_id INT NOT NULL,
    lab_id INT,
    section_id INT,
    study_period_id INT NOT NULL,
    hours_per_week INT DEFAULT 2,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stage_id) REFERENCES stages(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (study_period_id) REFERENCES study_periods(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال بعض المواد الدراسية كأمثلة
INSERT INTO subjects (subject_name, subject_code, stage_id, lab_id, section_id, study_period_id, hours_per_week) VALUES
('أساسيات الحاسوب', 'CS101', 1, 1, 1, 1, 3),
('أساسيات الحاسوب', 'CS101', 1, 1, 2, 1, 3),
('البرمجة بلغة C++', 'CS102', 1, 2, 1, 1, 4),
('البرمجة بلغة C++', 'CS102', 1, 2, 2, 1, 4),
('قواعد البيانات', 'CS201', 2, 1, 1, 1, 3),
('قواعد البيانات', 'CS201', 2, 1, 2, 1, 3),
('تصميم المواقع', 'CS202', 2, 3, 1, 1, 3),
('الشبكات الحاسوبية', 'CS301', 3, 4, 1, 1, 3),
('أمن المعلومات', 'CS302', 3, 2, 1, 1, 2),
('الذكاء الاصطناعي', 'CS401', 4, 1, 1, 1, 3),
('مشروع التخرج', 'CS402', 4, 3, 1, 1, 4),
('أساسيات الحاسوب', 'CS101E', 1, 1, 1, 2, 3),
('البرمجة بلغة C++', 'CS102E', 1, 2, 1, 2, 4),
('قواعد البيانات', 'CS201E', 2, 1, 1, 2, 3);

-- =============================================
-- جدول الطلاب
-- =============================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    student_id_number VARCHAR(50),
    stage_id INT NOT NULL,
    section_id INT NOT NULL DEFAULT 1,
    study_period_id INT NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    gender ENUM('ذكر', 'أنثى') DEFAULT 'ذكر',
    is_active TINYINT(1) DEFAULT 1,
    enrollment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (stage_id) REFERENCES stages(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    FOREIGN KEY (study_period_id) REFERENCES study_periods(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال بيانات طلاب كأمثلة
INSERT INTO students (student_name, student_id_number, stage_id, section_id, study_period_id, gender, enrollment_date) VALUES
-- المرحلة الأولى - شعبة A - صباحي
('أحمد محمد علي', 'STU001', 1, 1, 1, 'ذكر', '2024-09-01'),
('باسم حسين كريم', 'STU002', 1, 1, 1, 'ذكر', '2024-09-01'),
('تامر صالح جاسم', 'STU003', 1, 1, 1, 'ذكر', '2024-09-01'),
('جاسم عبدالله نور', 'STU004', 1, 1, 1, 'ذكر', '2024-09-01'),
('حسين محمود فاضل', 'STU005', 1, 1, 1, 'ذكر', '2024-09-01'),
('فاطمة أحمد حسين', 'STU006', 1, 1, 1, 'أنثى', '2024-09-01'),
('مريم علي جواد', 'STU007', 1, 1, 1, 'أنثى', '2024-09-01'),
('نور محمد كاظم', 'STU008', 1, 1, 1, 'أنثى', '2024-09-01'),
-- المرحلة الأولى - شعبة B - صباحي
('خالد إبراهيم عمر', 'STU009', 1, 2, 1, 'ذكر', '2024-09-01'),
('رائد سامي حسن', 'STU010', 1, 2, 1, 'ذكر', '2024-09-01'),
('زيد عادل محمد', 'STU011', 1, 2, 1, 'ذكر', '2024-09-01'),
('سامر يوسف خليل', 'STU012', 1, 2, 1, 'ذكر', '2024-09-01'),
('هدى سالم عبدالرحمن', 'STU013', 1, 2, 1, 'أنثى', '2024-09-01'),
('ياسمين فؤاد عادل', 'STU014', 1, 2, 1, 'أنثى', '2024-09-01'),
-- المرحلة الثانية - شعبة A - صباحي
('علي كامل رشيد', 'STU015', 2, 1, 1, 'ذكر', '2023-09-01'),
('عمر فاضل عباس', 'STU016', 2, 1, 1, 'ذكر', '2023-09-01'),
('قاسم نجم حميد', 'STU017', 2, 1, 1, 'ذكر', '2023-09-01'),
('ليث هادي صباح', 'STU018', 2, 1, 1, 'ذكر', '2023-09-01'),
('رقية حسن محمود', 'STU019', 2, 1, 1, 'أنثى', '2023-09-01'),
('سجى عامر توفيق', 'STU020', 2, 1, 1, 'أنثى', '2023-09-01'),
-- المرحلة الثانية - شعبة B - صباحي
('مصطفى جبار كريم', 'STU021', 2, 2, 1, 'ذكر', '2023-09-01'),
('عفراء مهدي جعفر', 'STU022', 2, 2, 1, 'أنثى', '2023-09-01'),
-- المرحلة الثالثة - شعبة A - صباحي
('نبيل حسين علوان', 'STU023', 3, 1, 1, 'ذكر', '2022-09-01'),
('هاشم قيس ناظم', 'STU024', 3, 1, 1, 'ذكر', '2022-09-01'),
('وسام جلال فرحان', 'STU025', 3, 1, 1, 'ذكر', '2022-09-01'),
('زينب كاظم حسون', 'STU026', 3, 1, 1, 'أنثى', '2022-09-01'),
('بتول صادق مجيد', 'STU027', 3, 1, 1, 'أنثى', '2022-09-01'),
-- المرحلة الرابعة - شعبة A - صباحي
('يحيى رعد سلمان', 'STU028', 4, 1, 1, 'ذكر', '2021-09-01'),
('إيهاب فلاح حمود', 'STU029', 4, 1, 1, 'ذكر', '2021-09-01'),
('آلاء نزار عبدالكريم', 'STU030', 4, 1, 1, 'أنثى', '2021-09-01'),
('دعاء سعد توفيق', 'STU031', 4, 1, 1, 'أنثى', '2021-09-01'),
-- طلاب مسائي
('ثامر جواد محسن', 'STU032', 1, 1, 2, 'ذكر', '2024-09-01'),
('جمال عباس شاكر', 'STU033', 1, 1, 2, 'ذكر', '2024-09-01'),
('حيدر منذر حاتم', 'STU034', 1, 1, 2, 'ذكر', '2024-09-01'),
('شيماء فاروق عدنان', 'STU035', 1, 1, 2, 'أنثى', '2024-09-01'),
('غادة رياض منير', 'STU036', 1, 1, 2, 'أنثى', '2024-09-01'),
('ماجد صلاح حامد', 'STU037', 2, 1, 2, 'ذكر', '2023-09-01'),
('نادر عماد ثابت', 'STU038', 2, 1, 2, 'ذكر', '2023-09-01'),
('هناء باسل قحطان', 'STU039', 2, 1, 2, 'أنثى', '2023-09-01');

-- =============================================
-- جدول سجلات الحضور
-- =============================================
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    lab_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('حاضر', 'غائب', 'إجازة') DEFAULT 'غائب',
    notes TEXT,
    recorded_by INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (lab_id) REFERENCES labs(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, subject_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول سجل النشاطات
-- =============================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول جلسات المستخدمين
-- =============================================
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- إنشاء الفهارس لتحسين الأداء
-- =============================================
CREATE INDEX idx_attendance_date ON attendance(attendance_date);
CREATE INDEX idx_attendance_student ON attendance(student_id);
CREATE INDEX idx_attendance_subject ON attendance(subject_id);
CREATE INDEX idx_students_stage ON students(stage_id);
CREATE INDEX idx_students_section ON students(section_id);
CREATE INDEX idx_students_period ON students(study_period_id);
CREATE INDEX idx_subjects_stage ON subjects(stage_id);
CREATE INDEX idx_subjects_section ON subjects(section_id);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);

-- =============================================
-- تحديث كلمات المرور بشكل صحيح
-- كلمة المرور: 123456
-- =============================================
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin';
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'teacher1';
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'teacher2';

-- =============================================
-- إدخال بعض سجلات الحضور كأمثلة
-- =============================================
INSERT INTO attendance (student_id, subject_id, lab_id, attendance_date, status, recorded_by) VALUES
(1, 1, 1, CURDATE(), 'حاضر', 1),
(2, 1, 1, CURDATE(), 'حاضر', 1),
(3, 1, 1, CURDATE(), 'غائب', 1),
(4, 1, 1, CURDATE(), 'حاضر', 1),
(5, 1, 1, CURDATE(), 'إجازة', 1),
(6, 1, 1, CURDATE(), 'حاضر', 1),
(7, 1, 1, CURDATE(), 'حاضر', 1),
(8, 1, 1, CURDATE(), 'غائب', 1);
