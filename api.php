<?php
/**
 * ملف API للتعامل مع جميع طلبات النظام
 * نظام إدارة حضور طلاب مختبرات الحاسوب
 * الإصدار 2.0 - محسن ومصحح
 */

// بدء الجلسة إذا لم تكن مبدوءة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// السماح بطلبات CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// الحصول على الإجراء المطلوب
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

try {
    $db = getDB();
    
    switch ($action) {
        // ==================== إحصائيات الداشبورد ====================
        case 'getDashboardStats':
            $stats = [];
            
            // عدد الطلاب
            $stmt = $db->query("SELECT COUNT(*) as total FROM students WHERE is_active = 1");
            $row = $stmt->fetch();
            $stats['totalStudents'] = $row ? $row['total'] : 0;
            
            // عدد المختبرات
            $stmt = $db->query("SELECT COUNT(*) as total FROM labs WHERE is_active = 1");
            $row = $stmt->fetch();
            $stats['totalLabs'] = $row ? $row['total'] : 0;
            
            // عدد المواد
            $stmt = $db->query("SELECT COUNT(*) as total FROM subjects WHERE is_active = 1");
            $row = $stmt->fetch();
            $stats['totalSubjects'] = $row ? $row['total'] : 0;
            
            // عدد الشعب
            $stmt = $db->query("SELECT COUNT(*) as total FROM sections WHERE is_active = 1");
            $row = $stmt->fetch();
            $stats['totalSections'] = $row ? $row['total'] : 0;
            
            // نسبة الحضور اليوم
            $today = date('Y-m-d');
            $stmt = $db->prepare("
                SELECT 
                    COUNT(CASE WHEN status = 'حاضر' THEN 1 END) as present,
                    COUNT(*) as total
                FROM attendance 
                WHERE attendance_date = ?
            ");
            $stmt->execute([$today]);
            $attendance = $stmt->fetch();
            
            $present = $attendance ? (int)$attendance['present'] : 0;
            $total = $attendance ? (int)$attendance['total'] : 0;
            
            $stats['todayAttendanceRate'] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            $stats['todayPresent'] = $present;
            $stats['todayTotal'] = $total;
            
            echo json_encode(['success' => true, 'data' => $stats], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== الشعب ====================
        case 'getSections':
            $stmt = $db->query("SELECT * FROM sections WHERE is_active = 1 ORDER BY section_name");
            $sections = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $sections ? $sections : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'addSection':
            $sectionName = isset($_POST['section_name']) ? trim($_POST['section_name']) : '';
            $sectionLabel = isset($_POST['section_label']) ? trim($_POST['section_label']) : '';
            
            if (empty($sectionName)) {
                echo json_encode(['success' => false, 'message' => 'يرجى إدخال رمز الشعبة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            if (empty($sectionLabel)) {
                $sectionLabel = "الشعبة $sectionName";
            }
            
            $stmt = $db->prepare("INSERT INTO sections (section_name, section_label) VALUES (?, ?)");
            $stmt->execute([$sectionName, $sectionLabel]);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة الشعبة بنجاح', 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateSection':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $sectionName = isset($_POST['section_name']) ? trim($_POST['section_name']) : '';
            $sectionLabel = isset($_POST['section_label']) ? trim($_POST['section_label']) : '';
            
            if ($id <= 0 || empty($sectionName)) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE sections SET section_name = ?, section_label = ? WHERE id = ?");
            $stmt->execute([$sectionName, $sectionLabel, $id]);
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث الشعبة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'deleteSection':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE sections SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'تم حذف الشعبة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== المختبرات ====================
        case 'getLabs':
            $stmt = $db->query("SELECT * FROM labs WHERE is_active = 1 ORDER BY id");
            $labs = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $labs ? $labs : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateLab':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $labName = isset($_POST['lab_name']) ? trim($_POST['lab_name']) : '';
            $supervisor = isset($_POST['lab_supervisor']) ? trim($_POST['lab_supervisor']) : '';
            $capacity = isset($_POST['capacity']) ? (int)$_POST['capacity'] : 30;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE labs SET lab_name = ?, lab_supervisor = ?, capacity = ? WHERE id = ?");
            $stmt->execute([$labName, $supervisor, $capacity, $id]);
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث بيانات المختبر بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== المراحل الدراسية ====================
        case 'getStages':
            $stmt = $db->query("SELECT * FROM stages WHERE is_active = 1 ORDER BY stage_order");
            $stages = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $stages ? $stages : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'addStage':
            $stageName = isset($_POST['stage_name']) ? trim($_POST['stage_name']) : '';
            $stageOrder = isset($_POST['stage_order']) ? (int)$_POST['stage_order'] : 1;
            
            if (empty($stageName)) {
                echo json_encode(['success' => false, 'message' => 'يرجى إدخال اسم المرحلة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("INSERT INTO stages (stage_name, stage_order) VALUES (?, ?)");
            $stmt->execute([$stageName, $stageOrder]);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة المرحلة بنجاح', 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateStage':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $stageName = isset($_POST['stage_name']) ? trim($_POST['stage_name']) : '';
            
            if ($id <= 0 || empty($stageName)) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE stages SET stage_name = ? WHERE id = ?");
            $stmt->execute([$stageName, $id]);
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث المرحلة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'deleteStage':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE stages SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'تم حذف المرحلة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== الفترات الدراسية ====================
        case 'getStudyPeriods':
            $stmt = $db->query("SELECT * FROM study_periods WHERE is_active = 1 ORDER BY id");
            $periods = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $periods ? $periods : []], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== المواد الدراسية ====================
        case 'getSubjects':
            $stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
            $periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
            $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
            
            $sql = "SELECT s.*, st.stage_name, l.lab_name, sp.period_name, sec.section_name, sec.section_label
                    FROM subjects s 
                    LEFT JOIN stages st ON s.stage_id = st.id 
                    LEFT JOIN labs l ON s.lab_id = l.id 
                    LEFT JOIN study_periods sp ON s.study_period_id = sp.id 
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.is_active = 1";
            $params = [];
            
            if (!empty($stageId)) {
                $sql .= " AND s.stage_id = ?";
                $params[] = $stageId;
            }
            if (!empty($periodId)) {
                $sql .= " AND s.study_period_id = ?";
                $params[] = $periodId;
            }
            if (!empty($sectionId)) {
                $sql .= " AND s.section_id = ?";
                $params[] = $sectionId;
            }
            
            $sql .= " ORDER BY s.stage_id, s.section_id, s.subject_name";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $subjects = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $subjects ? $subjects : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'addSubject':
            $subjectName = isset($_POST['subject_name']) ? trim($_POST['subject_name']) : '';
            $subjectCode = isset($_POST['subject_code']) ? trim($_POST['subject_code']) : '';
            $stageId = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
            $labId = isset($_POST['lab_id']) ? (int)$_POST['lab_id'] : 0;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            $periodId = isset($_POST['study_period_id']) ? (int)$_POST['study_period_id'] : 1;
            $hours = isset($_POST['hours_per_week']) ? (int)$_POST['hours_per_week'] : 2;
            
            if (empty($subjectName) || $stageId <= 0) {
                echo json_encode(['success' => false, 'message' => 'يرجى إدخال اسم المادة والمرحلة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("INSERT INTO subjects (subject_name, subject_code, stage_id, lab_id, section_id, study_period_id, hours_per_week) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $subjectName, 
                $subjectCode, 
                $stageId, 
                $labId > 0 ? $labId : null, 
                $sectionId > 0 ? $sectionId : null, 
                $periodId, 
                $hours
            ]);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة المادة بنجاح', 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateSubject':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $subjectName = isset($_POST['subject_name']) ? trim($_POST['subject_name']) : '';
            $subjectCode = isset($_POST['subject_code']) ? trim($_POST['subject_code']) : '';
            $stageId = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
            $labId = isset($_POST['lab_id']) ? (int)$_POST['lab_id'] : 0;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            $periodId = isset($_POST['study_period_id']) ? (int)$_POST['study_period_id'] : 1;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE subjects SET subject_name = ?, subject_code = ?, stage_id = ?, lab_id = ?, section_id = ?, study_period_id = ? WHERE id = ?");
            $stmt->execute([
                $subjectName, 
                $subjectCode, 
                $stageId, 
                $labId > 0 ? $labId : null, 
                $sectionId > 0 ? $sectionId : null, 
                $periodId, 
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث المادة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'deleteSubject':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE subjects SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'تم حذف المادة بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== الطلاب ====================
        case 'getStudents':
            $stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
            $periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
            $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $sortOrder = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? 'DESC' : 'ASC';
            
            $sql = "SELECT s.*, st.stage_name, sp.period_name, sec.section_name, sec.section_label
                    FROM students s 
                    LEFT JOIN stages st ON s.stage_id = st.id 
                    LEFT JOIN study_periods sp ON s.study_period_id = sp.id 
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.is_active = 1";
            $params = [];
            
            if (!empty($stageId)) {
                $sql .= " AND s.stage_id = ?";
                $params[] = $stageId;
            }
            if (!empty($periodId)) {
                $sql .= " AND s.study_period_id = ?";
                $params[] = $periodId;
            }
            if (!empty($sectionId)) {
                $sql .= " AND s.section_id = ?";
                $params[] = $sectionId;
            }
            if (!empty($search)) {
                $sql .= " AND s.student_name LIKE ?";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY s.student_name COLLATE utf8mb4_unicode_ci $sortOrder";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $students ? $students : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'addStudent':
            $studentName = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
            $studentIdNumber = isset($_POST['student_id_number']) ? trim($_POST['student_id_number']) : '';
            $stageId = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 1;
            $periodId = isset($_POST['study_period_id']) ? (int)$_POST['study_period_id'] : 1;
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $gender = isset($_POST['gender']) ? trim($_POST['gender']) : 'ذكر';
            
            if (empty($studentName) || $stageId <= 0) {
                echo json_encode(['success' => false, 'message' => 'يرجى إدخال اسم الطالب والمرحلة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            // توليد رقم طالب تلقائي إذا لم يُحدد
            if (empty($studentIdNumber)) {
                $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(student_id_number, 4) AS UNSIGNED)) as max_id FROM students WHERE student_id_number LIKE 'STU%'");
                $row = $stmt->fetch();
                $maxId = $row && $row['max_id'] ? (int)$row['max_id'] : 0;
                $studentIdNumber = 'STU' . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);
            }
            
            $stmt = $db->prepare("INSERT INTO students (student_name, student_id_number, stage_id, section_id, study_period_id, email, phone, gender, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->execute([$studentName, $studentIdNumber, $stageId, $sectionId, $periodId, $email, $phone, $gender]);
            
            echo json_encode(['success' => true, 'message' => 'تمت إضافة الطالب بنجاح', 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateStudent':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $studentName = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
            $stageId = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 1;
            $periodId = isset($_POST['study_period_id']) ? (int)$_POST['study_period_id'] : 1;
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $gender = isset($_POST['gender']) ? trim($_POST['gender']) : 'ذكر';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE students SET student_name = ?, stage_id = ?, section_id = ?, study_period_id = ?, email = ?, phone = ?, gender = ? WHERE id = ?");
            $stmt->execute([$studentName, $stageId, $sectionId, $periodId, $email, $phone, $gender, $id]);
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث بيانات الطالب بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'deleteStudent':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE students SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'تم حذف الطالب بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== استيراد الطلاب ====================
        case 'importStudents':
            $studentsDataJson = isset($_POST['students_data']) ? $_POST['students_data'] : '[]';
            $studentsData = json_decode($studentsDataJson, true);
            $stageId = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
            $sectionId = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 1;
            $periodId = isset($_POST['study_period_id']) ? (int)$_POST['study_period_id'] : 1;
            
            if (empty($studentsData) || !is_array($studentsData)) {
                echo json_encode(['success' => false, 'message' => 'لا توجد بيانات لاستيرادها'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            if ($stageId <= 0 || $periodId <= 0) {
                echo json_encode(['success' => false, 'message' => 'يرجى اختيار المرحلة والفترة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $db->beginTransaction();
            $imported = 0;
            $skipped = 0;
            $errors = [];
            
            try {
                // الحصول على آخر رقم طالب
                $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(student_id_number, 4) AS UNSIGNED)) as max_id FROM students WHERE student_id_number LIKE 'STU%'");
                $row = $stmt->fetch();
                $maxId = $row && $row['max_id'] ? (int)$row['max_id'] : 0;
                
                foreach ($studentsData as $student) {
                    $studentName = isset($student['name']) ? trim($student['name']) : '';
                    $gender = isset($student['gender']) ? trim($student['gender']) : 'ذكر';
                    
                    if (empty($studentName)) {
                        $skipped++;
                        continue;
                    }
                    
                    // التحقق من عدم وجود الطالب
                    $stmt = $db->prepare("SELECT id FROM students WHERE student_name = ? AND stage_id = ? AND section_id = ? AND study_period_id = ? AND is_active = 1");
                    $stmt->execute([$studentName, $stageId, $sectionId, $periodId]);
                    
                    if ($stmt->fetch()) {
                        $skipped++;
                        $errors[] = "الطالب '$studentName' موجود مسبقاً";
                        continue;
                    }
                    
                    $maxId++;
                    $studentIdNumber = 'STU' . str_pad($maxId, 3, '0', STR_PAD_LEFT);
                    
                    $stmt = $db->prepare("INSERT INTO students (student_name, student_id_number, stage_id, section_id, study_period_id, gender, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
                    $stmt->execute([$studentName, $studentIdNumber, $stageId, $sectionId, $periodId, $gender]);
                    $imported++;
                }
                
                $db->commit();
                
                $message = "تم استيراد $imported طالب بنجاح";
                if ($skipped > 0) {
                    $message .= " (تم تخطي $skipped)";
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message,
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ], JSON_UNESCAPED_UNICODE);
                
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
            
        // ==================== الحضور والغياب ====================
        case 'getAttendance':
            $subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
            $stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
            $periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
            $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            
            if (empty($subjectId)) {
                echo json_encode(['success' => true, 'data' => [], 'date' => $date], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            // جلب الطلاب مع حالة الحضور
            $sql = "SELECT s.id, s.student_name, s.student_id_number, s.gender,
                           COALESCE(a.status, 'غائب') as status,
                           COALESCE(a.notes, '') as notes
                    FROM students s
                    LEFT JOIN attendance a ON s.id = a.student_id 
                        AND a.attendance_date = ? 
                        AND a.subject_id = ?
                    WHERE s.is_active = 1";
            $params = [$date, $subjectId];
            
            if (!empty($stageId)) {
                $sql .= " AND s.stage_id = ?";
                $params[] = $stageId;
            }
            if (!empty($periodId)) {
                $sql .= " AND s.study_period_id = ?";
                $params[] = $periodId;
            }
            if (!empty($sectionId)) {
                $sql .= " AND s.section_id = ?";
                $params[] = $sectionId;
            }
            
            $sql .= " ORDER BY s.student_name COLLATE utf8mb4_unicode_ci ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $students ? $students : [], 'date' => $date], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'saveAttendance':
            $subjectId = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
            $labId = isset($_POST['lab_id']) ? (int)$_POST['lab_id'] : 1;
            $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
            $attendanceDataJson = isset($_POST['attendance_data']) ? $_POST['attendance_data'] : '[]';
            $attendanceData = json_decode($attendanceDataJson, true);
            $recordedBy = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            if ($subjectId <= 0 || empty($attendanceData)) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $db->beginTransaction();
            
            try {
                foreach ($attendanceData as $record) {
                    $studentId = isset($record['student_id']) ? (int)$record['student_id'] : 0;
                    $status = isset($record['status']) ? trim($record['status']) : 'غائب';
                    $notes = isset($record['notes']) ? trim($record['notes']) : '';
                    
                    if ($studentId <= 0) continue;
                    
                    // التحقق من صحة الحالة
                    if (!in_array($status, ['حاضر', 'غائب', 'إجازة'])) {
                        $status = 'غائب';
                    }
                    
                    $stmt = $db->prepare("
                        INSERT INTO attendance (student_id, subject_id, lab_id, attendance_date, status, notes, recorded_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), recorded_by = VALUES(recorded_by), updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$studentId, $subjectId, $labId, $date, $status, $notes, $recordedBy]);
                }
                
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'تم حفظ الحضور بنجاح'], JSON_UNESCAPED_UNICODE);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
            
        case 'getAttendanceHistory':
            $subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
            $stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
            $periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
            $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
            
            if (empty($subjectId)) {
                echo json_encode(['success' => true, 'data' => ['dates' => [], 'students' => []]], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            // جلب جميع التواريخ
            $stmt = $db->prepare("SELECT DISTINCT attendance_date FROM attendance WHERE subject_id = ? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date");
            $stmt->execute([$subjectId, $startDate, $endDate]);
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // جلب الطلاب
            $sql = "SELECT s.id, s.student_name, s.student_id_number
                    FROM students s
                    WHERE s.is_active = 1";
            $params = [];
            
            if (!empty($stageId)) {
                $sql .= " AND s.stage_id = ?";
                $params[] = $stageId;
            }
            if (!empty($periodId)) {
                $sql .= " AND s.study_period_id = ?";
                $params[] = $periodId;
            }
            if (!empty($sectionId)) {
                $sql .= " AND s.section_id = ?";
                $params[] = $sectionId;
            }
            
            $sql .= " ORDER BY s.student_name COLLATE utf8mb4_unicode_ci ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
            
            // جلب سجلات الحضور لكل طالب
            foreach ($students as &$student) {
                $stmt = $db->prepare("SELECT attendance_date, status FROM attendance WHERE student_id = ? AND subject_id = ? AND attendance_date BETWEEN ? AND ?");
                $stmt->execute([$student['id'], $subjectId, $startDate, $endDate]);
                $records = $stmt->fetchAll();
                
                $student['attendance'] = [];
                $student['total_present'] = 0;
                $student['total_absent'] = 0;
                $student['total_leave'] = 0;
                
                foreach ($records as $record) {
                    $student['attendance'][$record['attendance_date']] = $record['status'];
                    if ($record['status'] === 'حاضر') $student['total_present']++;
                    elseif ($record['status'] === 'غائب') $student['total_absent']++;
                    else $student['total_leave']++;
                }
            }
            
            echo json_encode(['success' => true, 'data' => ['dates' => $dates ? $dates : [], 'students' => $students ? $students : []]], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'getCumulativeAttendance':
            $stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
            $periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
            $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
            $subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
            
            $sql = "SELECT 
                        s.id,
                        s.student_name,
                        s.student_id_number,
                        COUNT(CASE WHEN a.status = 'حاضر' THEN 1 END) as total_present,
                        COUNT(CASE WHEN a.status = 'غائب' THEN 1 END) as total_absent,
                        COUNT(CASE WHEN a.status = 'إجازة' THEN 1 END) as total_leave,
                        COUNT(a.id) as total_days,
                        ROUND(COUNT(CASE WHEN a.status = 'حاضر' THEN 1 END) * 100.0 / NULLIF(COUNT(a.id), 0), 1) as attendance_rate
                    FROM students s
                    LEFT JOIN attendance a ON s.id = a.student_id";
            
            $conditions = ["s.is_active = 1"];
            $params = [];
            
            if (!empty($subjectId)) {
                $conditions[] = "(a.subject_id = ? OR a.subject_id IS NULL)";
                $params[] = $subjectId;
            }
            if (!empty($stageId)) {
                $conditions[] = "s.stage_id = ?";
                $params[] = $stageId;
            }
            if (!empty($periodId)) {
                $conditions[] = "s.study_period_id = ?";
                $params[] = $periodId;
            }
            if (!empty($sectionId)) {
                $conditions[] = "s.section_id = ?";
                $params[] = $sectionId;
            }
            
            $sql .= " WHERE " . implode(" AND ", $conditions);
            $sql .= " GROUP BY s.id, s.student_name, s.student_id_number ORDER BY s.student_name COLLATE utf8mb4_unicode_ci ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $data ? $data : []], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== التقارير ====================
        case 'getRecordedDates':
            $subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
            
            if (empty($subjectId)) {
                echo json_encode(['success' => true, 'data' => []], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("SELECT DISTINCT attendance_date FROM attendance WHERE subject_id = ? ORDER BY attendance_date DESC");
            $stmt->execute([$subjectId]);
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode(['success' => true, 'data' => $dates ? $dates : []], JSON_UNESCAPED_UNICODE);
            break;
            
        // ==================== إدارة المستخدمين ====================
        case 'getUsers':
            $stmt = $db->query("SELECT id, username, full_name, email, role, assigned_lab_id, is_active, last_login, created_at FROM users ORDER BY role, full_name");
            $users = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $users ? $users : []], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'addUser':
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : 'teacher';
            $assignedLabId = isset($_POST['assigned_lab_id']) ? (int)$_POST['assigned_lab_id'] : 0;
            
            if (empty($username) || empty($password) || empty($fullName)) {
                echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            // التحقق من عدم تكرار اسم المستخدم
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'اسم المستخدم موجود مسبقاً'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO users (username, password, full_name, email, role, assigned_lab_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $fullName, $email, $role, $assignedLabId > 0 ? $assignedLabId : null]);
            
            echo json_encode(['success' => true, 'message' => 'تم إضافة المستخدم بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'updateUser':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $role = isset($_POST['role']) ? trim($_POST['role']) : 'teacher';
            $assignedLabId = isset($_POST['assigned_lab_id']) ? (int)$_POST['assigned_lab_id'] : 0;
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, assigned_lab_id = ?, password = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $role, $assignedLabId > 0 ? $assignedLabId : null, $hashedPassword, $id]);
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, assigned_lab_id = ? WHERE id = ?");
                $stmt->execute([$fullName, $email, $role, $assignedLabId > 0 ? $assignedLabId : null, $id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'تم تحديث المستخدم بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'deleteUser':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'معرف غير صالح'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            // لا يمكن حذف المدير الرئيسي
            if ($id === 1) {
                echo json_encode(['success' => false, 'message' => 'لا يمكن حذف المدير الرئيسي'], JSON_UNESCAPED_UNICODE);
                break;
            }
            
            $stmt = $db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم بنجاح'], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف: ' . $action], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
