<?php
/**
 * ملف تصدير تقارير Excel
 * نظام إدارة حضور طلاب مختبرات الحاسوب
 */

require_once 'config.php';

// الحصول على البيانات
$subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
$periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
$sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

if (empty($subjectId) || empty($stageId) || empty($periodId)) {
    die('يرجى تحديد المادة والمرحلة والفترة');
}

try {
    $db = getDB();
    
    // جلب معلومات المادة
    $stmt = $db->prepare("SELECT s.*, st.stage_name, l.lab_name, l.lab_supervisor, sp.period_name, sec.section_name
                          FROM subjects s 
                          LEFT JOIN stages st ON s.stage_id = st.id 
                          LEFT JOIN labs l ON s.lab_id = l.id 
                          LEFT JOIN study_periods sp ON s.study_period_id = sp.id 
                          LEFT JOIN sections sec ON s.section_id = sec.id
                          WHERE s.id = ?");
    $stmt->execute([$subjectId]);
    $subject = $stmt->fetch();
    
    if (!$subject) {
        die('المادة غير موجودة');
    }
    
    // جلب التواريخ المسجلة
    $stmt = $db->prepare("SELECT DISTINCT attendance_date FROM attendance WHERE subject_id = ? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date");
    $stmt->execute([$subjectId, $startDate, $endDate]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // جلب الطلاب
    $sql = "SELECT s.id, s.student_name, s.student_id_number
            FROM students s
            WHERE s.is_active = 1 AND s.stage_id = ? AND s.study_period_id = ?";
    $params = [$stageId, $periodId];
    
    if (!empty($sectionId)) {
        $sql .= " AND s.section_id = ?";
        $params[] = $sectionId;
    }
    
    $sql .= " ORDER BY s.student_name COLLATE utf8mb4_unicode_ci ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    
    // جلب الحضور لكل طالب
    foreach ($students as &$student) {
        $student['attendance'] = [];
        $student['total_present'] = 0;
        $student['total_absent'] = 0;
        $student['total_leave'] = 0;
        
        foreach ($dates as $date) {
            $stmt = $db->prepare("SELECT status FROM attendance WHERE student_id = ? AND subject_id = ? AND attendance_date = ?");
            $stmt->execute([$student['id'], $subjectId, $date]);
            $record = $stmt->fetch();
            $status = $record ? $record['status'] : 'غائب';
            $student['attendance'][$date] = $status;
            
            if ($status === 'حاضر') $student['total_present']++;
            elseif ($status === 'غائب') $student['total_absent']++;
            else $student['total_leave']++;
        }
        
        $totalDays = count($dates);
        $student['attendance_rate'] = $totalDays > 0 ? round(($student['total_present'] / $totalDays) * 100, 1) : 0;
    }
    
    // تعيين headers للتحميل كملف Excel
    $filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    $colCount = count($dates) + 7;
    
    // إنشاء محتوى Excel (HTML Table format)
    echo '<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #1a365d; color: white; font-weight: bold; }
        .header-info { background-color: #f0f0f0; font-weight: bold; }
        .present { background-color: #c6f6d5; color: #22543d; }
        .absent { background-color: #fed7d7; color: #c53030; }
        .leave { background-color: #fefcbf; color: #d69e2e; }
        .stats { background-color: #e2e8f0; font-weight: bold; }
        .rate-good { background-color: #c6f6d5; }
        .rate-medium { background-color: #fefcbf; }
        .rate-bad { background-color: #fed7d7; }
    </style>
</head>
<body>';
    
    // معلومات التقرير
    echo '<table>
        <tr>
            <td colspan="' . $colCount . '" style="text-align: center; font-size: 18px; font-weight: bold; background-color: #1a365d; color: white;">
                تقرير الحضور التراكمي - نظام إدارة مختبرات الحاسوب
            </td>
        </tr>
        <tr class="header-info">
            <td colspan="2">المادة:</td>
            <td colspan="2">' . htmlspecialchars($subject['subject_name']) . '</td>
            <td colspan="2">المرحلة:</td>
            <td colspan="' . (count($dates) + 1) . '">' . htmlspecialchars($subject['stage_name']) . '</td>
        </tr>
        <tr class="header-info">
            <td colspan="2">الفترة:</td>
            <td colspan="2">' . htmlspecialchars($subject['period_name']) . '</td>
            <td colspan="2">الشعبة:</td>
            <td colspan="' . (count($dates) + 1) . '">' . htmlspecialchars($subject['section_name'] ? $subject['section_name'] : 'جميع الشعب') . '</td>
        </tr>
        <tr class="header-info">
            <td colspan="2">المختبر:</td>
            <td colspan="2">' . htmlspecialchars($subject['lab_name'] ? $subject['lab_name'] : '-') . '</td>
            <td colspan="2">المسؤول:</td>
            <td colspan="' . (count($dates) + 1) . '">' . htmlspecialchars($subject['lab_supervisor'] ? $subject['lab_supervisor'] : '-') . '</td>
        </tr>
        <tr class="header-info">
            <td colspan="2">من تاريخ:</td>
            <td colspan="2">' . $startDate . '</td>
            <td colspan="2">إلى تاريخ:</td>
            <td colspan="' . (count($dates) + 1) . '">' . $endDate . '</td>
        </tr>
        <tr><td colspan="' . $colCount . '"></td></tr>
    </table>';
    
    // جدول الحضور
    echo '<table>
        <tr>
            <th style="width: 40px;">ت</th>
            <th style="width: 80px;">الرقم</th>
            <th style="min-width: 150px;">اسم الطالب</th>';
    
    foreach ($dates as $date) {
        echo '<th style="width: 70px;">' . date('m/d', strtotime($date)) . '</th>';
    }
    
    echo '<th style="width: 60px;">حاضر</th>
          <th style="width: 60px;">غائب</th>
          <th style="width: 60px;">إجازة</th>
          <th style="width: 60px;">النسبة</th>
        </tr>';
    
    $counter = 1;
    foreach ($students as $student) {
        echo '<tr>
            <td>' . $counter++ . '</td>
            <td>' . htmlspecialchars($student['student_id_number']) . '</td>
            <td style="text-align: right;">' . htmlspecialchars($student['student_name']) . '</td>';
        
        foreach ($dates as $date) {
            $status = isset($student['attendance'][$date]) ? $student['attendance'][$date] : 'غائب';
            $class = $status === 'حاضر' ? 'present' : ($status === 'غائب' ? 'absent' : 'leave');
            $symbol = $status === 'حاضر' ? '✓' : ($status === 'غائب' ? '✗' : '○');
            echo '<td class="' . $class . '">' . $symbol . '</td>';
        }
        
        $rateClass = $student['attendance_rate'] >= 75 ? 'rate-good' : ($student['attendance_rate'] >= 50 ? 'rate-medium' : 'rate-bad');
        
        echo '<td class="stats present">' . $student['total_present'] . '</td>
              <td class="stats absent">' . $student['total_absent'] . '</td>
              <td class="stats leave">' . $student['total_leave'] . '</td>
              <td class="stats ' . $rateClass . '">' . $student['attendance_rate'] . '%</td>
            </tr>';
    }
    
    // صف الإجماليات
    $totalPresent = 0;
    $totalAbsent = 0;
    $totalLeave = 0;
    $totalRate = 0;
    
    foreach ($students as $s) {
        $totalPresent += $s['total_present'];
        $totalAbsent += $s['total_absent'];
        $totalLeave += $s['total_leave'];
        $totalRate += $s['attendance_rate'];
    }
    
    $avgRate = count($students) > 0 ? round($totalRate / count($students), 1) : 0;
    
    echo '<tr class="stats">
            <td colspan="3">الإجمالي / المتوسط</td>';
    
    foreach ($dates as $date) {
        echo '<td>-</td>';
    }
    
    echo '<td class="present">' . $totalPresent . '</td>
          <td class="absent">' . $totalAbsent . '</td>
          <td class="leave">' . $totalLeave . '</td>
          <td>' . $avgRate . '%</td>
        </tr>';
    
    echo '</table>';
    
    // معلومات إضافية
    echo '<table style="margin-top: 20px;">
        <tr>
            <td colspan="4" style="text-align: center; background-color: #f0f0f0;">
                <strong>دليل الرموز:</strong> ✓ = حاضر | ✗ = غائب | ○ = إجازة
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-size: 12px; color: #666;">
                تم إنشاء هذا التقرير بتاريخ: ' . date('Y/m/d H:i:s') . '
            </td>
        </tr>
    </table>';
    
    echo '</body></html>';
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
