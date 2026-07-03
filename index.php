<?php
/**
 * الصفحة الرئيسية - نظام إدارة حضور مختبرات الحاسوب
 */
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'full_name' => $_SESSION['full_name'],
    'role' => $_SESSION['role']
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة حضور مختبرات الحاسوب</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a365d;
            --primary-light: #2c5282;
            --primary-dark: #1a202c;
            --secondary: #3182ce;
            --success: #38a169;
            --success-light: #c6f6d5;
            --danger: #e53e3e;
            --danger-light: #fed7d7;
            --warning: #d69e2e;
            --warning-light: #fefcbf;
            --info: #3182ce;
            --gray-50: #f7fafc;
            --gray-100: #edf2f7;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-400: #a0aec0;
            --gray-500: #718096;
            --gray-600: #4a5568;
            --gray-700: #2d3748;
            --gray-800: #1a202c;
            --sidebar-width: 280px;
            --header-height: 70px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background: var(--gray-100);
            color: var(--gray-700);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* =============== SIDEBAR =============== */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 30px;
            color: white;
        }

        .sidebar-header h2 { color: white; font-size: 18px; font-weight: 700; }
        .sidebar-header p { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 5px; }

        .sidebar-menu { padding: 20px 15px; }

        .menu-category {
            color: rgba(255,255,255,0.5);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0 10px 10px;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: var(--transition);
            cursor: pointer;
            font-size: 15px;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(-5px);
        }

        .menu-item i { width: 22px; margin-left: 12px; font-size: 18px; }
        .menu-item .badge {
            margin-right: auto;
            background: var(--secondary);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* =============== MAIN CONTENT =============== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            background: var(--gray-100);
        }

        /* =============== HEADER =============== */
        .header {
            position: sticky;
            top: 0;
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 100;
        }

        .header-right { display: flex; align-items: center; gap: 20px; }
        .header-title h1 { font-size: 22px; color: var(--primary); font-weight: 700; }
        .header-left { display: flex; align-items: center; gap: 25px; }

        .digital-clock {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow);
        }

        .digital-clock i { font-size: 18px; }

        .current-date {
            color: var(--gray-600);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: var(--gray-50);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .user-profile:hover { background: var(--gray-100); }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            display: none;
            z-index: 1000;
            overflow: hidden;
            margin-top: 10px;
        }

        .user-profile:hover .user-dropdown { display: block; }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: var(--gray-700);
            text-decoration: none;
            transition: var(--transition);
        }

        .user-dropdown a:hover { background: var(--gray-50); }
        .user-dropdown a.logout { color: var(--danger); }

        /* =============== CONTENT AREA =============== */
        .content { padding: 30px; }

        .page-section { display: none; }
        .page-section.active { display: block; animation: fadeIn 0.3s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =============== DASHBOARD CARDS =============== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: var(--transition);
            border-right: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.students { border-right-color: var(--secondary); }
        .stat-card.labs { border-right-color: var(--success); }
        .stat-card.subjects { border-right-color: var(--warning); }
        .stat-card.attendance { border-right-color: var(--danger); }
        .stat-card.sections { border-right-color: var(--info); }

        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-card.students .stat-icon { background: rgba(49, 130, 206, 0.15); color: var(--secondary); }
        .stat-card.labs .stat-icon { background: rgba(56, 161, 105, 0.15); color: var(--success); }
        .stat-card.subjects .stat-icon { background: rgba(214, 158, 46, 0.15); color: var(--warning); }
        .stat-card.attendance .stat-icon { background: rgba(229, 62, 62, 0.15); color: var(--danger); }
        .stat-card.sections .stat-icon { background: rgba(49, 130, 206, 0.15); color: var(--info); }

        .stat-info h3 { font-size: 32px; font-weight: 800; color: var(--gray-800); }
        .stat-info p { color: var(--gray-500); font-size: 14px; margin-top: 5px; }

        /* =============== CARDS & PANELS =============== */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--gray-50);
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h3 {
            font-size: 18px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body { padding: 25px; }

        /* =============== FORMS =============== */
        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        /* =============== BUTTONS =============== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 54, 93, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #48bb78 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 161, 105, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #fc8181 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #ecc94b 100%);
            color: white;
        }

        .btn-secondary { background: var(--gray-200); color: var(--gray-700); }
        .btn-sm { padding: 8px 15px; font-size: 13px; }
        .btn-icon { width: 38px; height: 38px; padding: 0; justify-content: center; border-radius: 10px; }

        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }

        /* =============== TABLES =============== */
        .table-container { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        th, td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }

        tr:hover { background: var(--gray-50); }

        /* =============== ATTENDANCE TABLE =============== */
        .attendance-table { font-size: 14px; }
        .attendance-table th { padding: 12px 8px; position: sticky; top: 0; z-index: 10; }
        .attendance-table td { padding: 10px 8px; text-align: center; }

        .attendance-table td:first-child,
        .attendance-table td:nth-child(2),
        .attendance-table td:nth-child(3) {
            text-align: right;
            white-space: nowrap;
        }

        .status-select {
            padding: 8px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
            min-width: 90px;
        }

        .status-select.present { border-color: var(--success); background: var(--success-light); color: var(--success); }
        .status-select.absent { border-color: var(--danger); background: var(--danger-light); color: var(--danger); }
        .status-select.leave { border-color: var(--warning); background: var(--warning-light); color: var(--warning); }

        .present { color: #22543d; font-weight: bold; }
        .absent { color: #c53030; font-weight: bold; }
        .leave { color: #d69e2e; font-weight: bold; }

        /* =============== FILTERS =============== */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            align-items: flex-end;
        }

        .filter-group { flex: 1; min-width: 150px; }

        .filter-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-600);
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* =============== BADGES =============== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success { background: var(--success-light); color: var(--success); }
        .badge-danger { background: var(--danger-light); color: var(--danger); }
        .badge-warning { background: var(--warning-light); color: var(--warning); }
        .badge-info { background: rgba(49, 130, 206, 0.15); color: var(--info); }
        .badge-primary { background: rgba(26, 54, 93, 0.15); color: var(--primary); }

        /* =============== MODALS =============== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal-overlay.active { display: flex; animation: fadeIn 0.2s ease; }

        .modal {
            background: white;
            border-radius: var(--radius);
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal.modal-lg { max-width: 800px; }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--gray-50);
        }

        .modal-header h3 { font-size: 18px; color: var(--primary); }

        .modal-close {
            width: 35px;
            height: 35px;
            border: none;
            background: var(--gray-200);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--gray-600);
            transition: var(--transition);
        }

        .modal-close:hover { background: var(--danger); color: white; }

        .modal-body { padding: 25px; }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: var(--gray-50);
        }

        /* =============== TOAST NOTIFICATIONS =============== */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 3000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast.success { border-right: 4px solid var(--success); }
        .toast.error { border-right: 4px solid var(--danger); }
        .toast.warning { border-right: 4px solid var(--warning); }

        .toast-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast.success .toast-icon { background: var(--success-light); color: var(--success); }
        .toast.error .toast-icon { background: var(--danger-light); color: var(--danger); }

        /* =============== LABS CARDS =============== */
        .labs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .lab-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .lab-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .lab-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .lab-card-header i { font-size: 40px; margin-bottom: 10px; }
        .lab-card-header h4 { font-size: 18px; font-weight: 700; }

        .lab-card-body { padding: 20px; }

        .lab-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .lab-info-item:last-child { border-bottom: none; }
        .lab-info-item i { color: var(--primary); width: 20px; }

        .lab-card-actions {
            padding: 15px 20px;
            background: var(--gray-50);
            display: flex;
            justify-content: center;
        }

        /* =============== DATE CHIPS =============== */
        .date-picker-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .date-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border-radius: 20px;
            font-size: 13px;
        }

        .date-chip .remove-date { cursor: pointer; opacity: 0.8; }
        .date-chip .remove-date:hover { opacity: 1; }

        /* =============== IMPORT AREA =============== */
        .import-area {
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            padding: 30px;
            text-align: center;
            background: var(--gray-50);
            transition: var(--transition);
        }

        .import-area:hover { border-color: var(--primary); background: white; }

        .import-area i { font-size: 48px; color: var(--gray-400); margin-bottom: 15px; }
        .import-area p { color: var(--gray-600); margin-bottom: 10px; }

        .import-textarea {
            width: 100%;
            min-height: 200px;
            padding: 15px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .import-textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* =============== PROGRESS BAR =============== */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-fill.success { background: var(--success); }
        .progress-fill.danger { background: var(--danger); }
        .progress-fill.warning { background: var(--warning); }

        /* =============== EMPTY STATE =============== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-500);
        }

        .empty-state i { font-size: 60px; margin-bottom: 20px; opacity: 0.5; }
        .empty-state h4 { font-size: 20px; margin-bottom: 10px; color: var(--gray-600); }

        /* =============== SCROLLBAR =============== */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--gray-100); }
        ::-webkit-scrollbar-thumb { background: var(--gray-400); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gray-500); }

        /* =============== RESPONSIVE =============== */
        .menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-right: 0; }
            .menu-toggle { display: flex; }
        }

        @media (max-width: 768px) {
            .header { padding: 0 15px; }
            .content { padding: 15px; }
            .filters-bar { flex-direction: column; }
            .filter-group { width: 100%; }
            .digital-clock { display: none; }
        }

        /* Admin only elements */
        .admin-only { display: none; }
        body.is-admin .admin-only { display: block; }
        body.is-admin .menu-item.admin-only { display: flex; }
    </style>
</head>
<body class="<?php echo $user['role'] === 'admin' ? 'is-admin' : ''; ?>">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo"><i class="fas fa-laptop-code"></i></div>
            <h2>مختبرات الحاسوب</h2>
            <p>نظام إدارة الحضور</p>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-category">الرئيسية</div>
            <a class="menu-item active" data-page="dashboard">
                <i class="fas fa-chart-pie"></i>
                <span>لوحة التحكم</span>
            </a>
            
            <div class="menu-category">إدارة المختبرات</div>
            <a class="menu-item" data-page="labs">
                <i class="fas fa-desktop"></i>
                <span>المختبرات</span>
                <span class="badge">4</span>
            </a>
            <a class="menu-item" data-page="sections">
                <i class="fas fa-th-large"></i>
                <span>الشعب</span>
            </a>
            
            <div class="menu-category">الطلاب والمواد</div>
            <a class="menu-item" data-page="stages">
                <i class="fas fa-layer-group"></i>
                <span>المراحل الدراسية</span>
            </a>
            <a class="menu-item" data-page="subjects">
                <i class="fas fa-book"></i>
                <span>المواد الدراسية</span>
            </a>
            <a class="menu-item" data-page="students">
                <i class="fas fa-user-graduate"></i>
                <span>الطلاب</span>
            </a>
            <a class="menu-item" data-page="import">
                <i class="fas fa-file-import"></i>
                <span>استيراد الطلاب</span>
            </a>
            
            <div class="menu-category">الحضور والغياب</div>
            <a class="menu-item" data-page="attendance">
                <i class="fas fa-clipboard-check"></i>
                <span>تسجيل الحضور</span>
            </a>
            <a class="menu-item" data-page="cumulative">
                <i class="fas fa-chart-bar"></i>
                <span>الحضور التراكمي</span>
            </a>
            <a class="menu-item" data-page="reports">
                <i class="fas fa-file-pdf"></i>
                <span>التقارير والطباعة</span>
            </a>
            
            <div class="menu-category admin-only">إدارة النظام</div>
            <a class="menu-item admin-only" data-page="users">
                <i class="fas fa-users-cog"></i>
                <span>المستخدمين</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-right">
                <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
                <div class="header-title"><h1 id="pageTitle">لوحة التحكم</h1></div>
            </div>
            
            <div class="header-left">
                <div class="current-date" id="currentDate">
                    <i class="fas fa-calendar-alt"></i>
                    <span></span>
                </div>
                <div class="digital-clock" id="digitalClock">
                    <i class="fas fa-clock"></i>
                    <span>00:00:00</span>
                </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo mb_substr($user['full_name'], 0, 1); ?></div>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                    <div class="user-dropdown">
                        <a href="#"><i class="fas fa-user"></i> الملف الشخصي</a>
                        <a href="#"><i class="fas fa-cog"></i> الإعدادات</a>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content">
            <div class="toast-container" id="toastContainer"></div>

            <!-- Dashboard Page -->
            <section class="page-section active" id="page-dashboard">
                <div class="stats-grid">
                    <div class="stat-card students">
                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                        <div class="stat-info">
                            <h3 id="totalStudents">0</h3>
                            <p>إجمالي الطلاب</p>
                        </div>
                    </div>
                    <div class="stat-card labs">
                        <div class="stat-icon"><i class="fas fa-desktop"></i></div>
                        <div class="stat-info">
                            <h3 id="totalLabs">0</h3>
                            <p>المختبرات</p>
                        </div>
                    </div>
                    <div class="stat-card sections">
                        <div class="stat-icon"><i class="fas fa-th-large"></i></div>
                        <div class="stat-info">
                            <h3 id="totalSections">0</h3>
                            <p>الشعب</p>
                        </div>
                    </div>
                    <div class="stat-card subjects">
                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                        <div class="stat-info">
                            <h3 id="totalSubjects">0</h3>
                            <p>المواد الدراسية</p>
                        </div>
                    </div>
                    <div class="stat-card attendance">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-info">
                            <h3 id="attendanceRate">0%</h3>
                            <p>نسبة الحضور اليوم</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> ملخص حضور اليوم</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div style="text-align: center; padding: 20px;">
                                <h2 style="font-size: 48px; color: var(--success);" id="todayPresent">0</h2>
                                <p style="color: var(--gray-600);">حاضرون اليوم</p>
                            </div>
                            <div style="text-align: center; padding: 20px;">
                                <h2 style="font-size: 48px; color: var(--danger);" id="todayAbsent">0</h2>
                                <p style="color: var(--gray-600);">غائبون اليوم</p>
                            </div>
                            <div style="text-align: center; padding: 20px;">
                                <h2 style="font-size: 48px; color: var(--primary);" id="todayTotal">0</h2>
                                <p style="color: var(--gray-600);">إجمالي المسجلين</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="labs-grid" id="dashboardLabs"></div>
            </section>

            <!-- Labs Page -->
            <section class="page-section" id="page-labs">
                <div class="labs-grid" id="labsGrid"></div>
            </section>

            <!-- Sections Page -->
            <section class="page-section" id="page-sections">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-th-large"></i> الشعب الدراسية</h3>
                        <button class="btn btn-primary" onclick="showAddSectionModal()">
                            <i class="fas fa-plus"></i> إضافة شعبة
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>رمز الشعبة</th>
                                        <th>اسم الشعبة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="sectionsTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stages Page -->
            <section class="page-section" id="page-stages">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-layer-group"></i> المراحل الدراسية</h3>
                        <button class="btn btn-primary" onclick="showAddStageModal()">
                            <i class="fas fa-plus"></i> إضافة مرحلة
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المرحلة</th>
                                        <th>الترتيب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="stagesTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Subjects Page -->
            <section class="page-section" id="page-subjects">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>الفترة</label>
                        <select id="subjectPeriodFilter" onchange="loadSubjects()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المرحلة</label>
                        <select id="subjectStageFilter" onchange="loadSubjects()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>الشعبة</label>
                        <select id="subjectSectionFilter" onchange="loadSubjects()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="showAddSubjectModal()">
                        <i class="fas fa-plus"></i> إضافة مادة
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المادة</th>
                                        <th>الرمز</th>
                                        <th>المرحلة</th>
                                        <th>الشعبة</th>
                                        <th>المختبر</th>
                                        <th>الفترة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="subjectsTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Students Page -->
            <section class="page-section" id="page-students">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>الفترة</label>
                        <select id="studentPeriodFilter" onchange="loadStudents()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المرحلة</label>
                        <select id="studentStageFilter" onchange="loadStudents()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>الشعبة</label>
                        <select id="studentSectionFilter" onchange="loadStudents()">
                            <option value="">الكل</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>بحث</label>
                        <input type="text" id="studentSearch" placeholder="ابحث بالاسم..." oninput="loadStudents()">
                    </div>
                    <div class="filter-group">
                        <label>الترتيب</label>
                        <select id="studentSort" onchange="loadStudents()">
                            <option value="ASC">أبجدي (أ-ي)</option>
                            <option value="DESC">أبجدي (ي-أ)</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="showAddStudentModal()">
                        <i class="fas fa-plus"></i> إضافة طالب
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الرقم</th>
                                        <th>اسم الطالب</th>
                                        <th>المرحلة</th>
                                        <th>الشعبة</th>
                                        <th>الفترة</th>
                                        <th>الجنس</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Import Students Page -->
            <section class="page-section" id="page-import">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-import"></i> استيراد الطلاب دفعة واحدة</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>الفترة الدراسية</label>
                                <select id="importPeriod" class="form-control"></select>
                            </div>
                            <div class="form-group">
                                <label>المرحلة</label>
                                <select id="importStage" class="form-control"></select>
                            </div>
                            <div class="form-group">
                                <label>الشعبة</label>
                                <select id="importSection" class="form-control"></select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>أدخل أسماء الطلاب (كل اسم في سطر جديد)</label>
                            <textarea id="importStudentsText" class="import-textarea" placeholder="أحمد محمد علي
سارة حسين كريم
محمد عباس جاسم
..."></textarea>
                            <p style="color: var(--gray-500); font-size: 13px; margin-top: 10px;">
                                <i class="fas fa-info-circle"></i> 
                                أدخل كل اسم طالب في سطر منفصل. سيتم توليد أرقام الطلاب تلقائياً.
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label>الجنس الافتراضي</label>
                            <select id="importGender" class="form-control" style="max-width: 200px;">
                                <option value="ذكر">ذكر</option>
                                <option value="أنثى">أنثى</option>
                            </select>
                        </div>
                        
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="importStudents()">
                                <i class="fas fa-upload"></i> استيراد الطلاب
                            </button>
                            <button class="btn btn-secondary" onclick="clearImportForm()">
                                <i class="fas fa-eraser"></i> مسح
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Attendance Page -->
            <section class="page-section" id="page-attendance">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>الفترة</label>
                        <select id="attendancePeriodFilter" onchange="updateAttendanceFilters()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المرحلة</label>
                        <select id="attendanceStageFilter" onchange="updateAttendanceSubjects()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>الشعبة</label>
                        <select id="attendanceSectionFilter" onchange="updateAttendanceSubjects()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المادة</label>
                        <select id="attendanceSubjectFilter" onchange="loadAttendance()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>التاريخ</label>
                        <input type="date" id="attendanceDate" onchange="loadAttendance()">
                    </div>
                    <button class="btn btn-success" onclick="saveAttendance()">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clipboard-check"></i> تسجيل الحضور</h3>
                        <div style="display: flex; gap: 10px;">
                            <span class="badge badge-success"><i class="fas fa-check"></i> حاضر</span>
                            <span class="badge badge-danger"><i class="fas fa-times"></i> غائب</span>
                            <span class="badge badge-warning"><i class="fas fa-circle"></i> إجازة</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-container" style="max-height: 600px; overflow-y: auto;">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 100px;">الرقم</th>
                                        <th>اسم الطالب</th>
                                        <th style="width: 120px;">الحالة</th>
                                        <th style="width: 150px;">ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Cumulative Attendance Page -->
            <section class="page-section" id="page-cumulative">
                <div class="filters-bar">
                    <div class="filter-group">
                        <label>الفترة</label>
                        <select id="cumulativePeriodFilter" onchange="updateCumulativeFilters()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المرحلة</label>
                        <select id="cumulativeStageFilter" onchange="updateCumulativeSubjects()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>الشعبة</label>
                        <select id="cumulativeSectionFilter" onchange="updateCumulativeSubjects()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>المادة</label>
                        <select id="cumulativeSubjectFilter" onchange="loadCumulativeAttendance()">
                            <option value="">اختر</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>من تاريخ</label>
                        <input type="date" id="cumulativeStartDate">
                    </div>
                    <div class="filter-group">
                        <label>إلى تاريخ</label>
                        <input type="date" id="cumulativeEndDate">
                    </div>
                    <button class="btn btn-primary" onclick="loadAttendanceHistory()">
                        <i class="fas fa-search"></i> عرض
                    </button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> سجل الحضور التفصيلي</h3>
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> تصدير Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-container" style="max-height: 500px; overflow: auto;">
                            <table class="attendance-table" id="historyTable">
                                <thead id="historyTableHead"></thead>
                                <tbody id="historyTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> ملخص الحضور التراكمي</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الرقم</th>
                                        <th>اسم الطالب</th>
                                        <th>حاضر</th>
                                        <th>غائب</th>
                                        <th>إجازة</th>
                                        <th>المجموع</th>
                                        <th>نسبة الحضور</th>
                                    </tr>
                                </thead>
                                <tbody id="cumulativeTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reports Page -->
            <section class="page-section" id="page-reports">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-pdf"></i> تصدير التقارير</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>الفترة</label>
                                <select id="reportPeriodFilter" class="form-control" onchange="updateReportFilters()"></select>
                            </div>
                            <div class="form-group">
                                <label>المرحلة</label>
                                <select id="reportStageFilter" class="form-control" onchange="updateReportSubjects()"></select>
                            </div>
                            <div class="form-group">
                                <label>الشعبة</label>
                                <select id="reportSectionFilter" class="form-control" onchange="updateReportSubjects()"></select>
                            </div>
                            <div class="form-group">
                                <label>المادة</label>
                                <select id="reportSubjectFilter" class="form-control" onchange="loadRecordedDates()"></select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>التواريخ المسجلة</label>
                            <div id="recordedDatesContainer" class="date-picker-wrapper" style="border: 2px solid var(--gray-200); padding: 15px; border-radius: 10px; min-height: 80px; background: var(--gray-50);">
                                <p style="color: var(--gray-500); text-align: center; width: 100%;">اختر المادة لعرض التواريخ</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>التواريخ المختارة</label>
                            <div id="selectedDatesContainer" class="date-picker-wrapper" style="border: 2px dashed var(--primary); padding: 15px; border-radius: 10px; min-height: 60px;">
                                <p style="color: var(--gray-500); text-align: center; width: 100%;" id="noDatesSelected">لم يتم اختيار أي تاريخ</p>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> تصدير PDF
                            </button>
                            <button class="btn btn-secondary" onclick="clearSelectedDates()">
                                <i class="fas fa-eraser"></i> مسح التحديد
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Users Page (Admin Only) -->
            <section class="page-section" id="page-users">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-users-cog"></i> إدارة المستخدمين</h3>
                        <button class="btn btn-primary" onclick="showAddUserModal()">
                            <i class="fas fa-plus"></i> إضافة مستخدم
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم المستخدم</th>
                                        <th>الاسم الكامل</th>
                                        <th>البريد</th>
                                        <th>الصلاحية</th>
                                        <th>آخر دخول</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modals -->
    <!-- Lab Modal -->
    <div class="modal-overlay" id="labModal">
        <div class="modal">
            <div class="modal-header">
                <h3>تعديل المختبر</h3>
                <button class="modal-close" onclick="closeModal('labModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editLabId">
                <div class="form-group">
                    <label>اسم المختبر</label>
                    <input type="text" id="editLabName" class="form-control">
                </div>
                <div class="form-group">
                    <label>مسؤول المختبر</label>
                    <input type="text" id="editLabSupervisor" class="form-control">
                </div>
                <div class="form-group">
                    <label>السعة</label>
                    <input type="number" id="editLabCapacity" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('labModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveLab()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Section Modal -->
    <div class="modal-overlay" id="sectionModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="sectionModalTitle">إضافة شعبة</h3>
                <button class="modal-close" onclick="closeModal('sectionModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editSectionId">
                <div class="form-group">
                    <label>رمز الشعبة</label>
                    <input type="text" id="editSectionName" class="form-control" placeholder="مثال: A, B, C">
                </div>
                <div class="form-group">
                    <label>اسم الشعبة</label>
                    <input type="text" id="editSectionLabel" class="form-control" placeholder="مثال: الشعبة A">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('sectionModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveSection()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Stage Modal -->
    <div class="modal-overlay" id="stageModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="stageModalTitle">إضافة مرحلة</h3>
                <button class="modal-close" onclick="closeModal('stageModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editStageId">
                <div class="form-group">
                    <label>اسم المرحلة</label>
                    <input type="text" id="editStageName" class="form-control">
                </div>
                <div class="form-group">
                    <label>الترتيب</label>
                    <input type="number" id="editStageOrder" class="form-control" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('stageModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveStage()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Subject Modal -->
    <div class="modal-overlay" id="subjectModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="subjectModalTitle">إضافة مادة</h3>
                <button class="modal-close" onclick="closeModal('subjectModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editSubjectId">
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المادة</label>
                        <input type="text" id="editSubjectName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>رمز المادة</label>
                        <input type="text" id="editSubjectCode" class="form-control">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الفترة</label>
                        <select id="editSubjectPeriod" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>المرحلة</label>
                        <select id="editSubjectStage" class="form-control"></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الشعبة</label>
                        <select id="editSubjectSection" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>المختبر</label>
                        <select id="editSubjectLab" class="form-control"></select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('subjectModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveSubject()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- Student Modal -->
    <div class="modal-overlay" id="studentModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="studentModalTitle">إضافة طالب</h3>
                <button class="modal-close" onclick="closeModal('studentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editStudentId">
                <div class="form-group">
                    <label>اسم الطالب</label>
                    <input type="text" id="editStudentName" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الفترة</label>
                        <select id="editStudentPeriod" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>المرحلة</label>
                        <select id="editStudentStage" class="form-control"></select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الشعبة</label>
                        <select id="editStudentSection" class="form-control"></select>
                    </div>
                    <div class="form-group">
                        <label>الجنس</label>
                        <select id="editStudentGender" class="form-control">
                            <option value="ذكر">ذكر</option>
                            <option value="أنثى">أنثى</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>الهاتف</label>
                        <input type="text" id="editStudentPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>البريد</label>
                        <input type="email" id="editStudentEmail" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('studentModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveStudent()">حفظ</button>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="userModalTitle">إضافة مستخدم</h3>
                <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editUserId">
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" id="editUsername" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" id="editUserPassword" class="form-control" placeholder="اتركها فارغة للإبقاء على الحالية">
                    </div>
                </div>
                <div class="form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" id="editUserFullName" class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>البريد</label>
                        <input type="email" id="editUserEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>الصلاحية</label>
                        <select id="editUserRole" class="form-control">
                            <option value="teacher">معلم</option>
                            <option value="supervisor">مشرف</option>
                            <option value="admin">مدير</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('userModal')">إلغاء</button>
                <button class="btn btn-primary" onclick="saveUser()">حفظ</button>
            </div>
        </div>
    </div>

    <script>
        // =============== GLOBAL VARIABLES ===============
        let stages = [], periods = [], labs = [], sections = [], subjects = [], students = [], users = [];
        let selectedDates = [];
        const userRole = '<?php echo $user['role']; ?>';

        // =============== INITIALIZATION ===============
        document.addEventListener('DOMContentLoaded', function() {
            initializeClock();
            initializeDate();
            initializeNavigation();
            loadInitialData();
            document.getElementById('attendanceDate').value = getLocalDateString();
            document.getElementById('cumulativeStartDate').value = getFirstDayOfMonth();
            document.getElementById('cumulativeEndDate').value = getLocalDateString();
        });

        // =============== DATE HELPERS ===============
        function getLocalDateString(date = new Date()) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function getFirstDayOfMonth() {
            const now = new Date();
            return getLocalDateString(new Date(now.getFullYear(), now.getMonth(), 1));
        }

        function formatDate(dateStr) {
            const parts = dateStr.split('-');
            return parts.length === 3 ? `${parts[0]}/${parts[1]}/${parts[2]}` : dateStr;
        }

        // =============== CLOCK ===============
        function initializeClock() {
            updateClock();
            setInterval(updateClock, 1000);
        }

        function updateClock() {
            const now = new Date();
            document.querySelector('#digitalClock span').textContent = 
                `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
        }

        function initializeDate() {
            const now = new Date();
            document.querySelector('#currentDate span').textContent = 
                now.toLocaleDateString('ar-IQ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }

        // =============== NAVIGATION ===============
        function initializeNavigation() {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function() {
                    const page = this.dataset.page;
                    document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
                    this.classList.add('active');
                    document.querySelectorAll('.page-section').forEach(p => p.classList.remove('active'));
                    document.getElementById(`page-${page}`).classList.add('active');
                    document.getElementById('pageTitle').textContent = this.querySelector('span').textContent;
                    loadPageData(page);
                });
            });

            document.getElementById('menuToggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('open');
            });
        }

        function loadPageData(page) {
            const loaders = {
                'dashboard': loadDashboardStats,
                'labs': loadLabs,
                'sections': loadSections,
                'stages': loadStages,
                'subjects': loadSubjects,
                'students': loadStudents,
                'users': loadUsers
            };
            if (loaders[page]) loaders[page]();
        }

        // =============== LOAD INITIAL DATA ===============
        async function loadInitialData() {
            try {
                const [stagesRes, periodsRes, labsRes, sectionsRes] = await Promise.all([
                    fetch('api.php?action=getStages'),
                    fetch('api.php?action=getStudyPeriods'),
                    fetch('api.php?action=getLabs'),
                    fetch('api.php?action=getSections')
                ]);
                
                const [stagesData, periodsData, labsData, sectionsData] = await Promise.all([
                    stagesRes.json(), periodsRes.json(), labsRes.json(), sectionsRes.json()
                ]);
                
                if (stagesData.success) stages = stagesData.data;
                if (periodsData.success) periods = periodsData.data;
                if (labsData.success) labs = labsData.data;
                if (sectionsData.success) sections = sectionsData.data;
                
                populateFilters();
                loadDashboardStats();
            } catch (error) {
                showToast('error', 'خطأ في تحميل البيانات');
            }
        }

        function populateFilters() {
            const periodSelects = ['subjectPeriodFilter', 'studentPeriodFilter', 'attendancePeriodFilter', 
                                   'cumulativePeriodFilter', 'reportPeriodFilter', 'editSubjectPeriod', 
                                   'editStudentPeriod', 'importPeriod'];
            const stageSelects = ['subjectStageFilter', 'studentStageFilter', 'attendanceStageFilter',
                                  'cumulativeStageFilter', 'reportStageFilter', 'editSubjectStage', 
                                  'editStudentStage', 'importStage'];
            const sectionSelects = ['subjectSectionFilter', 'studentSectionFilter', 'attendanceSectionFilter',
                                    'cumulativeSectionFilter', 'reportSectionFilter', 'editSubjectSection',
                                    'editStudentSection', 'importSection'];
            
            periodSelects.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const first = el.querySelector('option');
                    el.innerHTML = first && first.value === '' ? first.outerHTML : '';
                    periods.forEach(p => el.innerHTML += `<option value="${p.id}">${p.period_name}</option>`);
                }
            });
            
            stageSelects.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const first = el.querySelector('option');
                    el.innerHTML = first && first.value === '' ? first.outerHTML : '';
                    stages.forEach(s => el.innerHTML += `<option value="${s.id}">${s.stage_name}</option>`);
                }
            });
            
            sectionSelects.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    const first = el.querySelector('option');
                    el.innerHTML = first && first.value === '' ? first.outerHTML : '';
                    sections.forEach(s => el.innerHTML += `<option value="${s.id}">${s.section_label}</option>`);
                }
            });
            
            const labSelect = document.getElementById('editSubjectLab');
            if (labSelect) {
                labSelect.innerHTML = '<option value="">اختر</option>';
                labs.forEach(l => labSelect.innerHTML += `<option value="${l.id}">${l.lab_name}</option>`);
            }
        }

        // =============== DASHBOARD ===============
        async function loadDashboardStats() {
            try {
                const response = await fetch('api.php?action=getDashboardStats');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalStudents').textContent = data.data.totalStudents;
                    document.getElementById('totalLabs').textContent = data.data.totalLabs;
                    document.getElementById('totalSubjects').textContent = data.data.totalSubjects;
                    document.getElementById('totalSections').textContent = data.data.totalSections || 4;
                    document.getElementById('attendanceRate').textContent = data.data.todayAttendanceRate + '%';
                    document.getElementById('todayPresent').textContent = data.data.todayPresent;
                    document.getElementById('todayAbsent').textContent = data.data.todayTotal - data.data.todayPresent;
                    document.getElementById('todayTotal').textContent = data.data.todayTotal;
                }
                
                const labsRes = await fetch('api.php?action=getLabs');
                const labsData = await labsRes.json();
                
                if (labsData.success) {
                    document.getElementById('dashboardLabs').innerHTML = labsData.data.map(lab => `
                        <div class="lab-card">
                            <div class="lab-card-header">
                                <i class="fas fa-desktop"></i>
                                <h4>${lab.lab_name}</h4>
                            </div>
                            <div class="lab-card-body">
                                <div class="lab-info-item"><i class="fas fa-user"></i><span>المسؤول: ${lab.lab_supervisor}</span></div>
                                <div class="lab-info-item"><i class="fas fa-users"></i><span>السعة: ${lab.capacity} طالب</span></div>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل الإحصائيات');
            }
        }

        // =============== SECTIONS ===============
        async function loadSections() {
            try {
                const response = await fetch('api.php?action=getSections');
                const data = await response.json();
                if (data.success) {
                    sections = data.data;
                    document.getElementById('sectionsTable').innerHTML = data.data.map((s, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td><span class="badge badge-primary">${s.section_name}</span></td>
                            <td>${s.section_label}</td>
                            <td>
                                <button class="btn btn-primary btn-icon" onclick="editSection(${s.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-icon" onclick="deleteSection(${s.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('');
                    populateFilters();
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل الشعب');
            }
        }

        function showAddSectionModal() {
            document.getElementById('sectionModalTitle').textContent = 'إضافة شعبة';
            document.getElementById('editSectionId').value = '';
            document.getElementById('editSectionName').value = '';
            document.getElementById('editSectionLabel').value = '';
            openModal('sectionModal');
        }

        function editSection(id) {
            const section = sections.find(s => s.id == id);
            if (section) {
                document.getElementById('sectionModalTitle').textContent = 'تعديل الشعبة';
                document.getElementById('editSectionId').value = section.id;
                document.getElementById('editSectionName').value = section.section_name;
                document.getElementById('editSectionLabel').value = section.section_label;
                openModal('sectionModal');
            }
        }

        async function saveSection() {
            const id = document.getElementById('editSectionId').value;
            const formData = new FormData();
            formData.append('action', id ? 'updateSection' : 'addSection');
            if (id) formData.append('id', id);
            formData.append('section_name', document.getElementById('editSectionName').value);
            formData.append('section_label', document.getElementById('editSectionLabel').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('sectionModal');
                    loadSections();
                } else {
                    showToast('error', data.message);
                }
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        async function deleteSection(id) {
            if (!confirm('حذف هذه الشعبة؟')) return;
            const formData = new FormData();
            formData.append('action', 'deleteSection');
            formData.append('id', id);
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    loadSections();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحذف');
            }
        }

        // =============== LABS ===============
        async function loadLabs() {
            try {
                const response = await fetch('api.php?action=getLabs');
                const data = await response.json();
                if (data.success) {
                    labs = data.data;
                    document.getElementById('labsGrid').innerHTML = data.data.map(lab => `
                        <div class="lab-card">
                            <div class="lab-card-header"><i class="fas fa-desktop"></i><h4>${lab.lab_name}</h4></div>
                            <div class="lab-card-body">
                                <div class="lab-info-item"><i class="fas fa-user"></i><span>المسؤول: ${lab.lab_supervisor}</span></div>
                                <div class="lab-info-item"><i class="fas fa-users"></i><span>السعة: ${lab.capacity}</span></div>
                            </div>
                            <div class="lab-card-actions">
                                <button class="btn btn-primary btn-sm" onclick="editLab(${lab.id})"><i class="fas fa-edit"></i> تعديل</button>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل المختبرات');
            }
        }

        function editLab(id) {
            const lab = labs.find(l => l.id == id);
            if (lab) {
                document.getElementById('editLabId').value = lab.id;
                document.getElementById('editLabName').value = lab.lab_name;
                document.getElementById('editLabSupervisor').value = lab.lab_supervisor;
                document.getElementById('editLabCapacity').value = lab.capacity;
                openModal('labModal');
            }
        }

        async function saveLab() {
            const formData = new FormData();
            formData.append('action', 'updateLab');
            formData.append('id', document.getElementById('editLabId').value);
            formData.append('lab_name', document.getElementById('editLabName').value);
            formData.append('lab_supervisor', document.getElementById('editLabSupervisor').value);
            formData.append('capacity', document.getElementById('editLabCapacity').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('labModal');
                    loadLabs();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        // =============== STAGES ===============
        async function loadStages() {
            try {
                const response = await fetch('api.php?action=getStages');
                const data = await response.json();
                if (data.success) {
                    stages = data.data;
                    document.getElementById('stagesTable').innerHTML = data.data.map((s, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${s.stage_name}</td>
                            <td>${s.stage_order}</td>
                            <td>
                                <button class="btn btn-primary btn-icon" onclick="editStage(${s.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-icon" onclick="deleteStage(${s.id})"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `).join('');
                    populateFilters();
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل المراحل');
            }
        }

        function showAddStageModal() {
            document.getElementById('stageModalTitle').textContent = 'إضافة مرحلة';
            document.getElementById('editStageId').value = '';
            document.getElementById('editStageName').value = '';
            document.getElementById('editStageOrder').value = stages.length + 1;
            openModal('stageModal');
        }

        function editStage(id) {
            const stage = stages.find(s => s.id == id);
            if (stage) {
                document.getElementById('stageModalTitle').textContent = 'تعديل المرحلة';
                document.getElementById('editStageId').value = stage.id;
                document.getElementById('editStageName').value = stage.stage_name;
                document.getElementById('editStageOrder').value = stage.stage_order;
                openModal('stageModal');
            }
        }

        async function saveStage() {
            const id = document.getElementById('editStageId').value;
            const formData = new FormData();
            formData.append('action', id ? 'updateStage' : 'addStage');
            if (id) formData.append('id', id);
            formData.append('stage_name', document.getElementById('editStageName').value);
            formData.append('stage_order', document.getElementById('editStageOrder').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('stageModal');
                    loadStages();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        async function deleteStage(id) {
            if (!confirm('حذف هذه المرحلة؟')) return;
            const formData = new FormData();
            formData.append('action', 'deleteStage');
            formData.append('id', id);
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    loadStages();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحذف');
            }
        }

        // =============== SUBJECTS ===============
        async function loadSubjects() {
            const periodId = document.getElementById('subjectPeriodFilter')?.value || '';
            const stageId = document.getElementById('subjectStageFilter')?.value || '';
            const sectionId = document.getElementById('subjectSectionFilter')?.value || '';
            
            try {
                const response = await fetch(`api.php?action=getSubjects&period_id=${periodId}&stage_id=${stageId}&section_id=${sectionId}`);
                const data = await response.json();
                if (data.success) {
                    subjects = data.data;
                    document.getElementById('subjectsTable').innerHTML = data.data.length === 0 
                        ? '<tr><td colspan="8" class="empty-state"><i class="fas fa-book"></i><p>لا توجد مواد</p></td></tr>'
                        : data.data.map((s, i) => `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${s.subject_name}</td>
                                <td>${s.subject_code || '-'}</td>
                                <td><span class="badge badge-info">${s.stage_name}</span></td>
                                <td><span class="badge badge-primary">${s.section_name || '-'}</span></td>
                                <td>${s.lab_name || '-'}</td>
                                <td><span class="badge ${s.study_period_id == 1 ? 'badge-success' : 'badge-warning'}">${s.period_name}</span></td>
                                <td>
                                    <button class="btn btn-primary btn-icon" onclick="editSubject(${s.id})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-icon" onclick="deleteSubject(${s.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل المواد');
            }
        }

        function showAddSubjectModal() {
            document.getElementById('subjectModalTitle').textContent = 'إضافة مادة';
            document.getElementById('editSubjectId').value = '';
            document.getElementById('editSubjectName').value = '';
            document.getElementById('editSubjectCode').value = '';
            document.getElementById('editSubjectPeriod').value = '';
            document.getElementById('editSubjectStage').value = '';
            document.getElementById('editSubjectSection').value = '';
            document.getElementById('editSubjectLab').value = '';
            openModal('subjectModal');
        }

        function editSubject(id) {
            const subject = subjects.find(s => s.id == id);
            if (subject) {
                document.getElementById('subjectModalTitle').textContent = 'تعديل المادة';
                document.getElementById('editSubjectId').value = subject.id;
                document.getElementById('editSubjectName').value = subject.subject_name;
                document.getElementById('editSubjectCode').value = subject.subject_code || '';
                document.getElementById('editSubjectPeriod').value = subject.study_period_id;
                document.getElementById('editSubjectStage').value = subject.stage_id;
                document.getElementById('editSubjectSection').value = subject.section_id || '';
                document.getElementById('editSubjectLab').value = subject.lab_id || '';
                openModal('subjectModal');
            }
        }

        async function saveSubject() {
            const id = document.getElementById('editSubjectId').value;
            const formData = new FormData();
            formData.append('action', id ? 'updateSubject' : 'addSubject');
            if (id) formData.append('id', id);
            formData.append('subject_name', document.getElementById('editSubjectName').value);
            formData.append('subject_code', document.getElementById('editSubjectCode').value);
            formData.append('study_period_id', document.getElementById('editSubjectPeriod').value);
            formData.append('stage_id', document.getElementById('editSubjectStage').value);
            formData.append('section_id', document.getElementById('editSubjectSection').value);
            formData.append('lab_id', document.getElementById('editSubjectLab').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('subjectModal');
                    loadSubjects();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        async function deleteSubject(id) {
            if (!confirm('حذف هذه المادة؟')) return;
            const formData = new FormData();
            formData.append('action', 'deleteSubject');
            formData.append('id', id);
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    loadSubjects();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحذف');
            }
        }

        // =============== STUDENTS ===============
        async function loadStudents() {
            const periodId = document.getElementById('studentPeriodFilter')?.value || '';
            const stageId = document.getElementById('studentStageFilter')?.value || '';
            const sectionId = document.getElementById('studentSectionFilter')?.value || '';
            const search = document.getElementById('studentSearch')?.value || '';
            const sortOrder = document.getElementById('studentSort')?.value || 'ASC';
            
            try {
                const response = await fetch(`api.php?action=getStudents&period_id=${periodId}&stage_id=${stageId}&section_id=${sectionId}&search=${encodeURIComponent(search)}&sort_order=${sortOrder}`);
                const data = await response.json();
                if (data.success) {
                    students = data.data;
                    document.getElementById('studentsTable').innerHTML = data.data.length === 0 
                        ? '<tr><td colspan="8" class="empty-state"><i class="fas fa-user-graduate"></i><p>لا يوجد طلاب</p></td></tr>'
                        : data.data.map((s, i) => `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${s.student_id_number}</td>
                                <td>${s.student_name}</td>
                                <td><span class="badge badge-info">${s.stage_name}</span></td>
                                <td><span class="badge badge-primary">${s.section_name || '-'}</span></td>
                                <td><span class="badge ${s.study_period_id == 1 ? 'badge-success' : 'badge-warning'}">${s.period_name}</span></td>
                                <td>${s.gender}</td>
                                <td>
                                    <button class="btn btn-primary btn-icon" onclick="editStudent(${s.id})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-icon" onclick="deleteStudent(${s.id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل الطلاب');
            }
        }

        function showAddStudentModal() {
            document.getElementById('studentModalTitle').textContent = 'إضافة طالب';
            document.getElementById('editStudentId').value = '';
            document.getElementById('editStudentName').value = '';
            document.getElementById('editStudentPeriod').value = '';
            document.getElementById('editStudentStage').value = '';
            document.getElementById('editStudentSection').value = '';
            document.getElementById('editStudentGender').value = 'ذكر';
            document.getElementById('editStudentPhone').value = '';
            document.getElementById('editStudentEmail').value = '';
            openModal('studentModal');
        }

        function editStudent(id) {
            const student = students.find(s => s.id == id);
            if (student) {
                document.getElementById('studentModalTitle').textContent = 'تعديل الطالب';
                document.getElementById('editStudentId').value = student.id;
                document.getElementById('editStudentName').value = student.student_name;
                document.getElementById('editStudentPeriod').value = student.study_period_id;
                document.getElementById('editStudentStage').value = student.stage_id;
                document.getElementById('editStudentSection').value = student.section_id;
                document.getElementById('editStudentGender').value = student.gender;
                document.getElementById('editStudentPhone').value = student.phone || '';
                document.getElementById('editStudentEmail').value = student.email || '';
                openModal('studentModal');
            }
        }

        async function saveStudent() {
            const id = document.getElementById('editStudentId').value;
            const formData = new FormData();
            formData.append('action', id ? 'updateStudent' : 'addStudent');
            if (id) formData.append('id', id);
            formData.append('student_name', document.getElementById('editStudentName').value);
            formData.append('study_period_id', document.getElementById('editStudentPeriod').value);
            formData.append('stage_id', document.getElementById('editStudentStage').value);
            formData.append('section_id', document.getElementById('editStudentSection').value);
            formData.append('gender', document.getElementById('editStudentGender').value);
            formData.append('phone', document.getElementById('editStudentPhone').value);
            formData.append('email', document.getElementById('editStudentEmail').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('studentModal');
                    loadStudents();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        async function deleteStudent(id) {
            if (!confirm('حذف هذا الطالب؟')) return;
            const formData = new FormData();
            formData.append('action', 'deleteStudent');
            formData.append('id', id);
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    loadStudents();
                }
            } catch (error) {
                showToast('error', 'خطأ في الحذف');
            }
        }

        // =============== IMPORT STUDENTS ===============
        async function importStudents() {
            const periodId = document.getElementById('importPeriod').value;
            const stageId = document.getElementById('importStage').value;
            const sectionId = document.getElementById('importSection').value;
            const text = document.getElementById('importStudentsText').value;
            const gender = document.getElementById('importGender').value;
            
            if (!periodId || !stageId || !sectionId) {
                showToast('error', 'يرجى اختيار الفترة والمرحلة والشعبة');
                return;
            }
            
            const names = text.split('\n').map(n => n.trim()).filter(n => n);
            if (names.length === 0) {
                showToast('error', 'يرجى إدخال أسماء الطلاب');
                return;
            }
            
            const studentsData = names.map(name => ({ name, gender }));
            
            const formData = new FormData();
            formData.append('action', 'importStudents');
            formData.append('study_period_id', periodId);
            formData.append('stage_id', stageId);
            formData.append('section_id', sectionId);
            formData.append('students_data', JSON.stringify(studentsData));
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    clearImportForm();
                }
            } catch (error) {
                showToast('error', 'خطأ في الاستيراد');
            }
        }

        function clearImportForm() {
            document.getElementById('importStudentsText').value = '';
        }

        // =============== ATTENDANCE ===============
        function updateAttendanceFilters() {
            updateAttendanceSubjects();
        }

        async function updateAttendanceSubjects() {
            const periodId = document.getElementById('attendancePeriodFilter').value;
            const stageId = document.getElementById('attendanceStageFilter').value;
            const sectionId = document.getElementById('attendanceSectionFilter').value;
            const subjectSelect = document.getElementById('attendanceSubjectFilter');
            
            if (!periodId || !stageId) {
                subjectSelect.innerHTML = '<option value="">اختر المادة</option>';
                return;
            }
            
            try {
                let url = `api.php?action=getSubjects&period_id=${periodId}&stage_id=${stageId}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    subjectSelect.innerHTML = '<option value="">اختر المادة</option>';
                    data.data.forEach(s => {
                        subjectSelect.innerHTML += `<option value="${s.id}" data-lab="${s.lab_id}">${s.subject_name} ${s.section_name ? '(' + s.section_name + ')' : ''}</option>`;
                    });
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل المواد');
            }
        }

        async function loadAttendance() {
            const periodId = document.getElementById('attendancePeriodFilter').value;
            const stageId = document.getElementById('attendanceStageFilter').value;
            const sectionId = document.getElementById('attendanceSectionFilter').value;
            const subjectId = document.getElementById('attendanceSubjectFilter').value;
            const date = document.getElementById('attendanceDate').value;
            
            if (!periodId || !stageId || !subjectId || !date) {
                document.getElementById('attendanceTable').innerHTML = `
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--gray-500);">
                        <i class="fas fa-filter" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
                        يرجى اختيار جميع الفلاتر
                    </td></tr>`;
                return;
            }
            
            try {
                let url = `api.php?action=getAttendance&period_id=${periodId}&stage_id=${stageId}&subject_id=${subjectId}&date=${date}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('attendanceTable').innerHTML = data.data.length === 0 
                        ? `<tr><td colspan="5" style="text-align: center; padding: 40px;">لا يوجد طلاب</td></tr>`
                        : data.data.map((s, i) => `
                            <tr data-student-id="${s.id}">
                                <td>${i + 1}</td>
                                <td>${s.student_id_number}</td>
                                <td style="text-align: right;">${s.student_name}</td>
                                <td>
                                    <select class="status-select ${s.status === 'حاضر' ? 'present' : (s.status === 'غائب' ? 'absent' : 'leave')}" 
                                            onchange="updateStatusStyle(this)" data-student-id="${s.id}">
                                        <option value="حاضر" ${s.status === 'حاضر' ? 'selected' : ''}>✔ حاضر</option>
                                        <option value="غائب" ${s.status === 'غائب' ? 'selected' : ''}>✖ غائب</option>
                                        <option value="إجازة" ${s.status === 'إجازة' ? 'selected' : ''}>⚬ إجازة</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control" style="padding: 8px; font-size: 13px;" placeholder="ملاحظات..." value="${s.notes || ''}" data-notes-for="${s.id}"></td>
                            </tr>
                        `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ في تحميل الحضور');
            }
        }

        function updateStatusStyle(select) {
            select.classList.remove('present', 'absent', 'leave');
            select.classList.add(select.value === 'حاضر' ? 'present' : (select.value === 'غائب' ? 'absent' : 'leave'));
        }

        async function saveAttendance() {
            const subjectId = document.getElementById('attendanceSubjectFilter').value;
            const subjectOption = document.querySelector(`#attendanceSubjectFilter option[value="${subjectId}"]`);
            const labId = subjectOption?.dataset.lab || 1;
            const date = document.getElementById('attendanceDate').value;
            
            if (!subjectId || !date) {
                showToast('error', 'يرجى اختيار المادة والتاريخ');
                return;
            }
            
            const attendanceData = [];
            document.querySelectorAll('#attendanceTable tr[data-student-id]').forEach(row => {
                const studentId = row.dataset.studentId;
                const status = row.querySelector('.status-select').value;
                const notes = row.querySelector(`input[data-notes-for="${studentId}"]`).value;
                attendanceData.push({ student_id: studentId, status, notes });
            });
            
            if (attendanceData.length === 0) {
                showToast('error', 'لا توجد بيانات');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'saveAttendance');
            formData.append('subject_id', subjectId);
            formData.append('lab_id', labId);
            formData.append('date', date);
            formData.append('attendance_data', JSON.stringify(attendanceData));
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) showToast('success', data.message);
                else showToast('error', data.message);
            } catch (error) {
                showToast('error', 'خطأ في الحفظ');
            }
        }

        // =============== CUMULATIVE ===============
        function updateCumulativeFilters() { updateCumulativeSubjects(); }

        async function updateCumulativeSubjects() {
            const periodId = document.getElementById('cumulativePeriodFilter').value;
            const stageId = document.getElementById('cumulativeStageFilter').value;
            const sectionId = document.getElementById('cumulativeSectionFilter').value;
            const subjectSelect = document.getElementById('cumulativeSubjectFilter');
            
            if (!periodId || !stageId) {
                subjectSelect.innerHTML = '<option value="">اختر</option>';
                return;
            }
            
            try {
                let url = `api.php?action=getSubjects&period_id=${periodId}&stage_id=${stageId}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    subjectSelect.innerHTML = '<option value="">اختر</option>';
                    data.data.forEach(s => {
                        subjectSelect.innerHTML += `<option value="${s.id}">${s.subject_name} ${s.section_name ? '(' + s.section_name + ')' : ''}</option>`;
                    });
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        async function loadAttendanceHistory() {
            const periodId = document.getElementById('cumulativePeriodFilter').value;
            const stageId = document.getElementById('cumulativeStageFilter').value;
            const sectionId = document.getElementById('cumulativeSectionFilter').value;
            const subjectId = document.getElementById('cumulativeSubjectFilter').value;
            const startDate = document.getElementById('cumulativeStartDate').value;
            const endDate = document.getElementById('cumulativeEndDate').value;
            
            if (!periodId || !stageId || !subjectId) {
                showToast('warning', 'يرجى اختيار الفترة والمرحلة والمادة');
                return;
            }
            
            try {
                let url = `api.php?action=getAttendanceHistory&period_id=${periodId}&stage_id=${stageId}&subject_id=${subjectId}&start_date=${startDate}&end_date=${endDate}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    renderAttendanceHistory(data.data);
                    loadCumulativeAttendance();
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        function renderAttendanceHistory(data) {
            const thead = document.getElementById('historyTableHead');
            const tbody = document.getElementById('historyTableBody');
            
            if (data.dates.length === 0) {
                thead.innerHTML = '';
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;">لا توجد سجلات</td></tr>';
                return;
            }
            
            let headerHTML = `<tr>
                <th style="position: sticky; right: 0; z-index: 11; background: var(--primary);">#</th>
                <th style="position: sticky; right: 50px; z-index: 11; background: var(--primary);">الرقم</th>
                <th style="position: sticky; right: 150px; z-index: 11; background: var(--primary); min-width: 150px;">الاسم</th>`;
            
            data.dates.forEach(date => headerHTML += `<th style="min-width: 70px;">${formatDate(date)}</th>`);
            headerHTML += `<th style="background: var(--success);">ح</th><th style="background: var(--danger);">غ</th><th style="background: var(--warning);">ج</th></tr>`;
            thead.innerHTML = headerHTML;
            
            tbody.innerHTML = data.students.map((s, i) => {
                let row = `<tr>
                    <td style="position: sticky; right: 0; background: white;">${i + 1}</td>
                    <td style="position: sticky; right: 50px; background: white;">${s.student_id_number}</td>
                    <td style="position: sticky; right: 150px; background: white; text-align: right;">${s.student_name}</td>`;
                
                data.dates.forEach(date => {
                    const status = s.attendance[date] || 'غائب';
                    const cls = status === 'حاضر' ? 'present' : (status === 'غائب' ? 'absent' : 'leave');
                    const sym = status === 'حاضر' ? '✔' : (status === 'غائب' ? '✖' : '⚬');
                    row += `<td class="${cls}">${sym}</td>`;
                });
                
                row += `<td class="present" style="font-weight: bold;">${s.total_present}</td>
                        <td class="absent" style="font-weight: bold;">${s.total_absent}</td>
                        <td class="leave" style="font-weight: bold;">${s.total_leave}</td></tr>`;
                return row;
            }).join('');
        }

        async function loadCumulativeAttendance() {
            const periodId = document.getElementById('cumulativePeriodFilter').value;
            const stageId = document.getElementById('cumulativeStageFilter').value;
            const sectionId = document.getElementById('cumulativeSectionFilter').value;
            const subjectId = document.getElementById('cumulativeSubjectFilter').value;
            
            try {
                let url = `api.php?action=getCumulativeAttendance&period_id=${periodId}&stage_id=${stageId}&subject_id=${subjectId}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('cumulativeTable').innerHTML = data.data.length === 0 
                        ? '<tr><td colspan="8" style="text-align: center; padding: 40px;">لا توجد بيانات</td></tr>'
                        : data.data.map((s, i) => {
                            const rate = s.attendance_rate || 0;
                            const rateClass = rate >= 75 ? 'success' : (rate >= 50 ? 'warning' : 'danger');
                            return `<tr>
                                <td>${i + 1}</td>
                                <td>${s.student_id_number}</td>
                                <td style="text-align: right;">${s.student_name}</td>
                                <td class="present" style="font-weight: bold;">${s.total_present || 0}</td>
                                <td class="absent" style="font-weight: bold;">${s.total_absent || 0}</td>
                                <td class="leave" style="font-weight: bold;">${s.total_leave || 0}</td>
                                <td>${s.total_days || 0}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="progress-bar" style="flex: 1;">
                                            <div class="progress-fill ${rateClass}" style="width: ${rate}%"></div>
                                        </div>
                                        <span style="font-weight: bold; min-width: 50px;">${rate}%</span>
                                    </div>
                                </td>
                            </tr>`;
                        }).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        function exportToExcel() {
            const periodId = document.getElementById('cumulativePeriodFilter').value;
            const stageId = document.getElementById('cumulativeStageFilter').value;
            const sectionId = document.getElementById('cumulativeSectionFilter').value;
            const subjectId = document.getElementById('cumulativeSubjectFilter').value;
            const startDate = document.getElementById('cumulativeStartDate').value;
            const endDate = document.getElementById('cumulativeEndDate').value;
            
            if (!periodId || !stageId || !subjectId) {
                showToast('error', 'يرجى اختيار الفلاتر');
                return;
            }
            
            let url = `export_excel.php?subject_id=${subjectId}&stage_id=${stageId}&period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
            if (sectionId) url += `&section_id=${sectionId}`;
            window.open(url, '_blank');
        }

        // =============== REPORTS ===============
        function updateReportFilters() { updateReportSubjects(); }

        async function updateReportSubjects() {
            const periodId = document.getElementById('reportPeriodFilter').value;
            const stageId = document.getElementById('reportStageFilter').value;
            const sectionId = document.getElementById('reportSectionFilter').value;
            const subjectSelect = document.getElementById('reportSubjectFilter');
            
            if (!periodId || !stageId) {
                subjectSelect.innerHTML = '<option value="">اختر</option>';
                return;
            }
            
            try {
                let url = `api.php?action=getSubjects&period_id=${periodId}&stage_id=${stageId}`;
                if (sectionId) url += `&section_id=${sectionId}`;
                
                const response = await fetch(url);
                const data = await response.json();
                if (data.success) {
                    subjectSelect.innerHTML = '<option value="">اختر</option>';
                    data.data.forEach(s => {
                        subjectSelect.innerHTML += `<option value="${s.id}">${s.subject_name} ${s.section_name ? '(' + s.section_name + ')' : ''}</option>`;
                    });
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        async function loadRecordedDates() {
            const subjectId = document.getElementById('reportSubjectFilter').value;
            const container = document.getElementById('recordedDatesContainer');
            
            if (!subjectId) {
                container.innerHTML = '<p style="color: var(--gray-500); text-align: center; width: 100%;">اختر المادة لعرض التواريخ</p>';
                return;
            }
            
            try {
                const response = await fetch(`api.php?action=getRecordedDates&subject_id=${subjectId}`);
                const data = await response.json();
                
                if (data.success) {
                    container.innerHTML = data.data.length === 0 
                        ? '<p style="color: var(--gray-500); text-align: center; width: 100%;">لا توجد تواريخ مسجلة</p>'
                        : data.data.map(date => `
                            <label style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 15px; background: var(--gray-100); border-radius: 8px; cursor: pointer; margin: 5px;">
                                <input type="checkbox" value="${date}" onchange="toggleDateSelection(this)">
                                <span>${formatDate(date)}</span>
                            </label>
                        `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        function toggleDateSelection(checkbox) {
            const date = checkbox.value;
            const container = document.getElementById('selectedDatesContainer');
            
            if (checkbox.checked && !selectedDates.includes(date)) {
                selectedDates.push(date);
            } else {
                selectedDates = selectedDates.filter(d => d !== date);
            }
            
            container.innerHTML = selectedDates.length === 0 
                ? '<p style="color: var(--gray-500); text-align: center; width: 100%;" id="noDatesSelected">لم يتم اختيار أي تاريخ</p>'
                : selectedDates.map(d => `
                    <span class="date-chip">
                        ${formatDate(d)}
                        <span class="remove-date" onclick="removeSelectedDate('${d}')">&times;</span>
                    </span>
                `).join('');
        }

        function removeSelectedDate(date) {
            selectedDates = selectedDates.filter(d => d !== date);
            const checkbox = document.querySelector(`#recordedDatesContainer input[value="${date}"]`);
            if (checkbox) checkbox.checked = false;
            
            const container = document.getElementById('selectedDatesContainer');
            container.innerHTML = selectedDates.length === 0 
                ? '<p style="color: var(--gray-500); text-align: center; width: 100%;">لم يتم اختيار أي تاريخ</p>'
                : selectedDates.map(d => `
                    <span class="date-chip">${formatDate(d)}<span class="remove-date" onclick="removeSelectedDate('${d}')">&times;</span></span>
                `).join('');
        }

        function clearSelectedDates() {
            selectedDates = [];
            document.querySelectorAll('#recordedDatesContainer input[type="checkbox"]').forEach(cb => cb.checked = false);
            document.getElementById('selectedDatesContainer').innerHTML = '<p style="color: var(--gray-500); text-align: center; width: 100%;">لم يتم اختيار أي تاريخ</p>';
        }

        function exportToPDF() {
            const periodId = document.getElementById('reportPeriodFilter').value;
            const stageId = document.getElementById('reportStageFilter').value;
            const sectionId = document.getElementById('reportSectionFilter').value;
            const subjectId = document.getElementById('reportSubjectFilter').value;
            
            if (!periodId || !stageId || !subjectId) {
                showToast('error', 'يرجى اختيار الفلاتر');
                return;
            }
            
            if (selectedDates.length === 0) {
                showToast('error', 'يرجى اختيار تاريخ واحد على الأقل');
                return;
            }
            
            let url = `export_pdf.php?subject_id=${subjectId}&stage_id=${stageId}&period_id=${periodId}&dates=${selectedDates.join(',')}`;
            if (sectionId) url += `&section_id=${sectionId}`;
            window.open(url, '_blank');
        }

        // =============== USERS (Admin) ===============
        async function loadUsers() {
            if (userRole !== 'admin') return;
            try {
                const response = await fetch('api.php?action=getUsers');
                const data = await response.json();
                if (data.success) {
                    users = data.data;
                    document.getElementById('usersTable').innerHTML = data.data.map((u, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${u.username}</td>
                            <td>${u.full_name}</td>
                            <td>${u.email || '-'}</td>
                            <td><span class="badge ${u.role === 'admin' ? 'badge-danger' : 'badge-info'}">${u.role}</span></td>
                            <td>${u.last_login || '-'}</td>
                            <td>
                                <button class="btn btn-primary btn-icon" onclick="editUser(${u.id})"><i class="fas fa-edit"></i></button>
                                ${u.id !== 1 ? `<button class="btn btn-danger btn-icon" onclick="deleteUser(${u.id})"><i class="fas fa-trash"></i></button>` : ''}
                            </td>
                        </tr>
                    `).join('');
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        function showAddUserModal() {
            document.getElementById('userModalTitle').textContent = 'إضافة مستخدم';
            document.getElementById('editUserId').value = '';
            document.getElementById('editUsername').value = '';
            document.getElementById('editUserPassword').value = '';
            document.getElementById('editUserFullName').value = '';
            document.getElementById('editUserEmail').value = '';
            document.getElementById('editUserRole').value = 'teacher';
            openModal('userModal');
        }

        function editUser(id) {
            const user = users.find(u => u.id == id);
            if (user) {
                document.getElementById('userModalTitle').textContent = 'تعديل المستخدم';
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editUserPassword').value = '';
                document.getElementById('editUserFullName').value = user.full_name;
                document.getElementById('editUserEmail').value = user.email || '';
                document.getElementById('editUserRole').value = user.role;
                openModal('userModal');
            }
        }

        async function saveUser() {
            const id = document.getElementById('editUserId').value;
            const formData = new FormData();
            formData.append('action', id ? 'updateUser' : 'addUser');
            if (id) formData.append('id', id);
            formData.append('username', document.getElementById('editUsername').value);
            formData.append('password', document.getElementById('editUserPassword').value);
            formData.append('full_name', document.getElementById('editUserFullName').value);
            formData.append('email', document.getElementById('editUserEmail').value);
            formData.append('role', document.getElementById('editUserRole').value);
            
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    closeModal('userModal');
                    loadUsers();
                } else {
                    showToast('error', data.message);
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        async function deleteUser(id) {
            if (!confirm('حذف هذا المستخدم؟')) return;
            const formData = new FormData();
            formData.append('action', 'deleteUser');
            formData.append('id', id);
            try {
                const response = await fetch('api.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    showToast('success', data.message);
                    loadUsers();
                }
            } catch (error) {
                showToast('error', 'خطأ');
            }
        }

        // =============== UTILITIES ===============
        function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }

        function showToast(type, message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <div class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : (type === 'error' ? 'times' : 'info')}"></i></div>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });
    </script>
</body>
</html>
