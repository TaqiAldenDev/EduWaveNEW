<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Parent') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Parent';

if (!isset($_GET['child_id'])) {
    header('Location: my_children.php');
    exit();
}

// Set page title variable
$page_title = 'Child Dashboard';

require_once __DIR__ . '/../includes/auth.php';

$child_id = $_GET['child_id'];

// Get child details
$stmt = $pdo->prepare('SELECT u.id, u.name FROM users u WHERE u.id = ? AND u.role = "Student"');
$stmt->execute([$child_id]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    header('Location: my_children.php');
    exit();
}

// Get child's class and grade
$class_name = 'N/A';
$grade_name = 'N/A';
try {
    $stmt = $pdo->prepare('
        SELECT c.grade_name
        FROM student_classes sc
        JOIN classes c ON sc.class_id = c.id
        WHERE sc.student_id = ?
        ORDER BY sc.academic_year DESC
        LIMIT 1
    ');
    $stmt->execute([$child_id]);
    $class_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($class_info) {
        $grade_name = $class_info['grade_name'];
    }
} catch (PDOException $e) {
    $grade_name = 'N/A';
}

// Get child's average grade
$avg_grade = 0;
$total_assignments = 0;
try {
    $stmt = $pdo->prepare('SELECT AVG(score) as avg_score, COUNT(*) as count FROM grades WHERE student_id = ?');
    $stmt->execute([$child_id]);
    $grade_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($grade_info && $grade_info['avg_score']) {
        $avg_grade = round($grade_info['avg_score'], 1);
        $total_assignments = $grade_info['count'];
    }
} catch (PDOException $e) {
    $avg_grade = 0;
    $total_assignments = 0;
}

// Get child's attendance percentage
$attendance_percent = 0;
$total_attendance_days = 0;
try {
    $stmt = $pdo->prepare('
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present
        FROM attendance
        WHERE student_id = ?
    ');
    $stmt->execute([$child_id]);
    $attendance_info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($attendance_info && $attendance_info['total'] > 0) {
        $attendance_percent = round(($attendance_info['present'] / $attendance_info['total']) * 100, 1);
        $total_attendance_days = $attendance_info['total'];
    }
} catch (PDOException $e) {
    $attendance_percent = 0;
    $total_attendance_days = 0;
}

// Get recent grades
$recent_grades = [];
try {
    $stmt = $pdo->prepare('
        SELECT g.score, g.exam_type, g.date_given, s.name as subject_name
        FROM grades g
        JOIN subjects s ON g.subject_id = s.id
        WHERE g.student_id = ?
        ORDER BY g.date_given DESC
        LIMIT 5
    ');
    $stmt->execute([$child_id]);
    $recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_grades = [];
}

// Get recent attendance
$recent_attendance = [];
try {
    $stmt = $pdo->prepare('
        SELECT a.attend_date, a.status, s.name as subject_name
        FROM attendance a
        JOIN subjects s ON a.subject_id = s.id
        WHERE a.student_id = ?
        ORDER BY a.attend_date DESC
        LIMIT 5
    ');
    $stmt->execute([$child_id]);
    $recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_attendance = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Parent Dashboard - EduWave</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../assets/logo.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between">
                        <div class="logo">
                            <a href="dashboard.php"><img src="../assets/logo.svg" alt="Logo" srcset="" width="40"></a>
                        </div>
                        <div class="toggler">
                            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    <ul class="menu">
                        <li class="sidebar-item">
                            <a href="dashboard.php" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>My Children</span>
                            </a>
                            <ul class="submenu active">
                                <li class="submenu-item active">
                                    <a href="my_children.php">View Children</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-mortarboard"></i>
                                <span>Academic Performance</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="child_grades.php?child_id=<?= htmlspecialchars($child_id) ?>">Grades</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_attendance.php?child_id=<?= htmlspecialchars($child_id) ?>">Attendance</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_timetable.php?child_id=<?= htmlspecialchars($child_id) ?>">Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-text"></i>
                                <span>Reports</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="weekly_summary.php?child_id=<?= htmlspecialchars($child_id) ?>">Weekly Summary</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="../logout.php" class='sidebar-link'>
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </li>

                    </ul>
                </div>
                <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
            </div>
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3><?= htmlspecialchars($child['name']) ?>'s Dashboard</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon purple">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Grade</h6>
                                        <h6 class="font-extrabold mb-0"><?= htmlspecialchars($grade_name) ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon blue">
                                            <i class="bi bi-graph-up"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Avg. Score</h6>
                                        <h6 class="font-extrabold mb-0"><?= $avg_grade ?>%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon green">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Attendance</h6>
                                        <h6 class="font-extrabold mb-0"><?= $attendance_percent ?>%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon red">
                                            <i class="bi bi-journal-bookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Total Grades</h6>
                                        <h6 class="font-extrabold mb-0"><?= $total_assignments ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="row">
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Grades</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Exam Type</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_grades)): ?>
                                                <?php foreach ($recent_grades as $grade): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                                                        <td><?= htmlspecialchars($grade['exam_type']) ?></td>
                                                        <td>
                                                            <?php
                                                            $score = $grade['score'];
                                                            $badge_class = 'bg-secondary';
                                                            if ($score >= 90) $badge_class = 'bg-success';
                                                            elseif ($score >= 80) $badge_class = 'bg-primary';
                                                            elseif ($score >= 70) $badge_class = 'bg-info';
                                                            elseif ($score >= 60) $badge_class = 'bg-warning';
                                                            else $badge_class = 'bg-danger';
                                                            ?>
                                                            <span class="badge <?= $badge_class ?>"><?= $score ?>%</span>
                                                        </td>
                                                        <td><?= date('M j, Y', strtotime($grade['date_given'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No grades available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Attendance</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_attendance)): ?>
                                                <?php foreach ($recent_attendance as $att): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($att['subject_name']) ?></td>
                                                        <td><?= date('M j, Y', strtotime($att['attend_date'])) ?></td>
                                                        <td>
                                                            <?php
                                                            $status = $att['status'];
                                                            $badge_class = $status == 'Present' ? 'bg-success' : 'bg-danger';
                                                            ?>
                                                            <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No attendance records available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-3 mb-3">
                                        <a href="child_grades.php?child_id=<?= $child_id ?>" class="btn btn-primary w-100">
                                            <i class="bi bi-graph-up me-2"></i> View All Grades
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3">
                                        <a href="child_attendance.php?child_id=<?= $child_id ?>" class="btn btn-success w-100">
                                            <i class="bi bi-clipboard-check me-2"></i> View Attendance
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3">
                                        <a href="child_timetable.php?child_id=<?= $child_id ?>" class="btn btn-info w-100">
                                            <i class="bi bi-calendar-week me-2"></i> View Timetable
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-3 mb-3">
                                        <a href="weekly_summary.php?child_id=<?= $child_id ?>" class="btn btn-warning w-100">
                                            <i class="bi bi-file-text me-2"></i> Weekly Summary
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p>2025 &copy; EduWave</p>
                    </div>
                    <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by EduWave Team</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../dashboardassets/js/bootstrap.bundle.min.js"></script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>