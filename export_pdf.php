<?php
/**
 * ملف تصدير تقارير PDF
 * نظام إدارة حضور طلاب مختبرات الحاسوب
 */

require_once 'config.php';

// الحصول على البيانات
$subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$stageId = isset($_GET['stage_id']) ? $_GET['stage_id'] : '';
$periodId = isset($_GET['period_id']) ? $_GET['period_id'] : '';
$sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : '';
$datesParam = isset($_GET['dates']) ? $_GET['dates'] : date('Y-m-d');
$dates = !empty($datesParam) ? explode(',', $datesParam) : [date('Y-m-d')];

if (empty($subjectId) || empty($stageId) || empty($periodId)) {
    die('يرجى تحديد المادة والمرحلة والفترة');
}

try {
    $db = getDB();
    
    // جلب معلومات المادة
    $stmt = $db->prepare("SELECT s.*, st.stage_name, l.lab_name, l.lab_supervisor, sp.period_name, sec.section_name, sec.section_label
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
    }
    
    // إنشاء HTML للطباعة
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الحضور - <?php echo htmlspecialchars($subject['subject_name']); ?></title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Tajawal", Arial, sans-serif;
            background: #fff;
            color: #333;
            padding: 20px;
            direction: rtl;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double #1a365d;
        }
        
        .header h1 {
            font-size: 24px;
            color: #1a365d;
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-item label {
            font-weight: bold;
            color: #4a5568;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-item span {
            color: #1a365d;
            font-size: 16px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        
        th, td {
            border: 1px solid #cbd5e0;
            padding: 10px 8px;
            text-align: center;
        }
        
        th {
            background: #1a365d;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background: #f7fafc;
        }
        
        tr:hover {
            background: #edf2f7;
        }
        
        .present {
            color: #22543d;
            font-weight: bold;
            background: #c6f6d5;
        }
        
        .absent {
            color: #c53030;
            font-weight: bold;
            background: #fed7d7;
        }
        
        .leave {
            color: #d69e2e;
            font-weight: bold;
            background: #fefcbf;
        }
        
        .stats-row {
            background: #ebf8ff !important;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        
        .signature {
            text-align: center;
            min-width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
        
        .print-date {
            text-align: center;
            margin-top: 30px;
            color: #718096;
            font-size: 12px;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            padding: 10px;
            background: #f7fafc;
            border-radius: 8px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
        
        .btn-print {
            background: #1a365d;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
            font-family: inherit;
        }
        
        .btn-print:hover {
            background: #2c5282;
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ طباعة التقرير</button>
    
    <div class="header">
        <h1>نظام إدارة حضور مختبرات الحاسوب</h1>
        <h2>تقرير حضور الطلاب</h2>
    </div>
    
    <div class="info-grid">
        <div class="info-item">
            <label>المادة الدراسية</label>
            <span><?php echo htmlspecialchars($subject['subject_name']); ?></span>
        </div>
        <div class="info-item">
            <label>المرحلة</label>
            <span><?php echo htmlspecialchars($subject['stage_name']); ?></span>
        </div>
        <div class="info-item">
            <label>الفترة</label>
            <span><?php echo htmlspecialchars($subject['period_name']); ?></span>
        </div>
        <div class="info-item">
            <label>الشعبة</label>
            <span><?php echo htmlspecialchars($subject['section_label'] ? $subject['section_label'] : 'جميع الشعب'); ?></span>
        </div>
        <div class="info-item">
            <label>المختبر</label>
            <span><?php echo htmlspecialchars($subject['lab_name'] ? $subject['lab_name'] : '-'); ?></span>
        </div>
        <div class="info-item">
            <label>عدد الطلاب</label>
            <span><?php echo count($students); ?></span>
        </div>
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <div class="legend-icon present">✔</div>
            <span>حاضر</span>
        </div>
        <div class="legend-item">
            <div class="legend-icon absent">✖</div>
            <span>غائب</span>
        </div>
        <div class="legend-item">
            <div class="legend-icon leave">⚬</div>
            <span>إجازة</span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">ت</th>
                <th style="width: 80px;">الرقم</th>
                <th>اسم الطالب</th>
                <?php foreach ($dates as $date): ?>
                <th><?php echo date('m/d', strtotime($date)); ?></th>
                <?php endforeach; ?>
                <?php if (count($dates) > 1): ?>
                <th>حاضر</th>
                <th>غائب</th>
                <th>إجازة</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 1;
            $totalPresent = 0;
            $totalAbsent = 0;
            $totalLeave = 0;
            
            foreach ($students as $student): 
            ?>
            <tr>
                <td><?php echo $counter++; ?></td>
                <td><?php echo htmlspecialchars($student['student_id_number']); ?></td>
                <td style="text-align: right;"><?php echo htmlspecialchars($student['student_name']); ?></td>
                <?php foreach ($dates as $date): 
                    $status = isset($student['attendance'][$date]) ? $student['attendance'][$date] : 'غائب';
                    $class = $status === 'حاضر' ? 'present' : ($status === 'غائب' ? 'absent' : 'leave');
                    $symbol = $status === 'حاضر' ? '✔' : ($status === 'غائب' ? '✖' : '⚬');
                ?>
                <td class="<?php echo $class; ?>"><?php echo $symbol; ?></td>
                <?php endforeach; ?>
                <?php if (count($dates) > 1): ?>
                <td class="present"><?php echo $student['total_present']; ?></td>
                <td class="absent"><?php echo $student['total_absent']; ?></td>
                <td class="leave"><?php echo $student['total_leave']; ?></td>
                <?php 
                endif;
                $totalPresent += $student['total_present'];
                $totalAbsent += $student['total_absent'];
                $totalLeave += $student['total_leave'];
                ?>
            </tr>
            <?php endforeach; ?>
            
            <?php if (count($dates) > 1): ?>
            <tr class="stats-row">
                <td colspan="3">الإجمالي</td>
                <?php foreach ($dates as $date): ?>
                <td>-</td>
                <?php endforeach; ?>
                <td class="present"><?php echo $totalPresent; ?></td>
                <td class="absent"><?php echo $totalAbsent; ?></td>
                <td class="leave"><?php echo $totalLeave; ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <div class="signature">
            <div class="signature-line">توقيع مسؤول المختبر</div>
        </div>
        <div class="signature">
            <div class="signature-line">توقيع رئيس القسم</div>
        </div>
    </div>
    
    <div class="print-date">
        تم إنشاء هذا التقرير بتاريخ: <?php echo date('Y/m/d H:i:s'); ?>
    </div>
</body>
</html>
<?php
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>
