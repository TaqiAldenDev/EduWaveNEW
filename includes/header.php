<?php
require_once __DIR__.'/auth.php';
require_once __DIR__.'/notifications.php';

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
$unread_count = count($notifications);

// Get current page name to highlight active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>EduWave - <?=$role?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="/eduwave/assets/css/bootstrap.min.css" rel="stylesheet">
<link href="/eduwave/assets/css/custom.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="/eduwave/assets/js/main.js"></script>
<style>
:root {
    --role-colour: <?=$colour?>;
    --primary-navy: #141e26;
    --accent-sky: #9ac5d3;
    --soft-white: #f5f7fa;
    --light-sky: #c0d6df;
}

body {
    background-color: var(--soft-white);
    padding-top: 56px;
    font-family: 'Poppins', sans-serif;
}

.sidebar {
    position: fixed;
    top: 56px;
    left: 0;
    bottom: 0;
    width: 250px;
    background: white;
    color: var(--primary-navy);
    overflow-y: auto;
    z-index: 100;
    transition: all 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    border-right: 1px solid var(--light-sky);
}

.sidebar .nav-link {
    color: #6c757d;
    padding: 12px 20px;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    font-weight: 500;
    border-radius: 0 8px 8px 0;
    margin: 2px 10px;
}

.sidebar .nav-link:hover {
    color: var(--primary-navy);
    background: rgba(154, 197, 211, 0.1);
    border-left-color: var(--accent-sky);
}

.sidebar .nav-link.active {
    color: var(--primary-navy);
    background: rgba(154, 197, 211, 0.15);
    border-left-color: var(--accent-sky);
    font-weight: 600;
}

.sidebar .nav-link i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    color: var(--accent-sky);
}

.sidebar .sidebar-header {
    padding: 20px;
    background: var(--soft-white);
    border-bottom: 1px solid var(--light-sky);
    color: var(--primary-navy);
    font-weight: 600;
}

.sidebar .sidebar-header h5 {
    margin: 0;
    color: var(--primary-navy);
    display: flex;
    align-items: center;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
    transition: all 0.3s ease;
    background: var(--soft-white);
    min-height: calc(100vh - 56px);
}

.navbar-top {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    color: var(--primary-navy);
    padding: 0.5rem 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-bottom: 1px solid var(--light-sky);
}

.navbar-top .navbar-brand {
    color: var(--primary-navy) !important;
    font-weight: 600;
}

.user-info {
    display: flex;
    align-items: center;
}

.user-info .dropdown-toggle::after {
    display: none;
}

.role-badge {
    background: var(--role-colour);
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-left: 10px;
}

.page-title {
    font-weight: 600;
    color: var(--primary-navy);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.page-title i {
    margin-right: 10px;
    color: var(--accent-sky);
}
</style>
</head><body>
<nav class="navbar navbar-top navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="/eduwave/assets/logo.svg" width="30" height="30" class="d-inline-block align-top me-2" alt="">
            EduWave - <?=$role?> Panel <span class="role-badge"><?=$role?></span>
        </a>
        <div class="d-flex align-items-center">
            <div class="dropdown user-info">
                <a href="#" class="text-dark dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i>Account
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/eduwave/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="sidebar">
    <div class="sidebar-header">
        <h5><i class="fas fa-school me-2"></i>Navigation</h5>
    </div>
    <ul class="nav flex-column">
        <?php if ($role === 'Admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/eduwave/admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'users') !== false ? 'active' : '' ?>" href="/eduwave/admin/users_list.php">
                    <i class="fas fa-users"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'class') !== false || strpos($current_page, 'subject') !== false ? 'active' : '' ?>" href="/eduwave/admin/classes.php">
                    <i class="fas fa-chalkboard-teacher"></i> Classes & Subjects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'library') !== false ? 'active' : '' ?>" href="/eduwave/admin/library_add.php">
                    <i class="fas fa-book"></i> Library Management
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'event') !== false ? 'active' : '' ?>" href="/eduwave/admin/event_add.php">
                    <i class="fas fa-calendar-alt"></i> Calendar Events
                </a>
            </li>
        <?php elseif ($role === 'Registrar'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/eduwave/registrar/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'student_enrol') !== false ? 'active' : '' ?>" href="/eduwave/registrar/student_enrol.php">
                    <i class="fas fa-user-plus"></i> Enroll Student
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'student_transfer') !== false ? 'active' : '' ?>" href="/eduwave/registrar/student_transfer.php">
                    <i class="fas fa-exchange-alt"></i> Transfer Student
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'certificate') !== false ? 'active' : '' ?>" href="/eduwave/registrar/certificate_issue.php">
                    <i class="fas fa-certificate"></i> Issue Certificate
                </a>
            </li>
        <?php elseif ($role === 'Teacher'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/eduwave/teacher/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'class_list') !== false ? 'active' : '' ?>" href="/eduwave/teacher/class_list.php">
                    <i class="fas fa-chalkboard"></i> My Classes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'attendance') !== false ? 'active' : '' ?>" href="/eduwave/teacher/attendance.php">
                    <i class="fas fa-clipboard-list"></i> Take Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'grades') !== false ? 'active' : '' ?>" href="/eduwave/teacher/grades_enter.php">
                    <i class="fas fa-graduation-cap"></i> Enter Grades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'homework') !== false ? 'active' : '' ?>" href="/eduwave/teacher/homework_add.php">
                    <i class="fas fa-book-open"></i> Add Homework
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'summary') !== false ? 'active' : '' ?>" href="/eduwave/teacher/summary_print.php">
                    <i class="fas fa-print"></i> Print Summary
                </a>
            </li>
        <?php elseif ($role === 'Student'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/eduwave/student/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'timetable') !== false ? 'active' : '' ?>" href="/eduwave/student/timetable.php">
                    <i class="fas fa-calendar"></i> My Timetable
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'my_grades') !== false ? 'active' : '' ?>" href="/eduwave/student/my_grades.php">
                    <i class="fas fa-chart-bar"></i> My Grades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'my_attendance') !== false ? 'active' : '' ?>" href="/eduwave/student/my_attendance.php">
                    <i class="fas fa-check-circle"></i> My Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'homework_upload') !== false ? 'active' : '' ?>" href="/eduwave/student/homework_upload.php">
                    <i class="fas fa-file-upload"></i> Upload Homework
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'library') !== false ? 'active' : '' ?>" href="/eduwave/student/library_browse.php">
                    <i class="fas fa-book"></i> Library
                </a>
            </li>
        <?php elseif ($role === 'Parent'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="/eduwave/parent/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'child_grades') !== false ? 'active' : '' ?>" href="/eduwave/parent/child_grades.php">
                    <i class="fas fa-chart-bar"></i> Child Grades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'child_attendance') !== false ? 'active' : '' ?>" href="/eduwave/parent/child_attendance.php">
                    <i class="fas fa-check-circle"></i> Child Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'child_timetable') !== false ? 'active' : '' ?>" href="/eduwave/parent/child_timetable.php">
                    <i class="fas fa-calendar"></i> Child Timetable
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($current_page, 'weekly') !== false ? 'active' : '' ?>" href="/eduwave/parent/weekly_summary.php">
                    <i class="fas fa-file-alt"></i> Weekly Summary
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="p-3 mt-auto" style="background: var(--soft-white);">
        <div class="text-center text-muted">
            <small>&copy; 2025 EduWave</small>
        </div>
    </div>
</div>

<div class="main-content">
