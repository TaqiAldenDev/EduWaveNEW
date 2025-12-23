<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Parent') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Parent';

// Fetch dynamic data for the dashboard
$parent_id = $_SESSION['user_id'];

try {
    // Get parent's children
    $stmt = $pdo->prepare('SELECT users.id, users.name FROM users JOIN parent_student ON users.id = parent_student.student_id WHERE parent_student.parent_id = ?');
    $stmt->execute([$parent_id]);
    $children = $stmt->fetchAll();

    // Get total number of children
    $total_children = count($children);

    // Get grades and attendance data for each child
    $total_grades = 0;
    $total_attendance = 0;
    $child_count_for_stats = 0;

    foreach ($children as $child) {
        // Get latest grades for the child
        $grade_stmt = $pdo->prepare('SELECT score FROM grades WHERE student_id = ? ORDER BY date_given DESC LIMIT 5');
        $grade_stmt->execute([$child['id']]);
        $grades = $grade_stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($grades)) {
            $avg_grade = array_sum($grades) / count($grades);
            $total_grades += $avg_grade;
            $child_count_for_stats++;
        }

        // Get attendance percentage for the child
        $attendance_stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?');
        $attendance_stmt->execute([$child['id']]);
        $attendance = $attendance_stmt->fetch();

        if ($attendance && $attendance['total'] > 0) {
            $attendance_percent = ($attendance['present'] / $attendance['total']) * 100;
            $total_attendance += $attendance_percent;
        }
    }

    $avg_grade = $child_count_for_stats > 0 ? round($total_grades / $child_count_for_stats, 1) : 0;
    $avg_attendance = $child_count_for_stats > 0 ? round($total_attendance / $child_count_for_stats, 1) : 0;

    // Get assignments count
    $assignment_count = 0;
    foreach ($children as $child) {
        // Check if homework_submissions table exists first
        $table_check = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homework_submissions'");
        $table_check->execute();
        if ($table_check->rowCount() > 0) {
            $assignment_stmt = $pdo->prepare('SELECT COUNT(*) FROM homework_submissions WHERE student_id = ? AND status = "pending"');
            $assignment_stmt->execute([$child['id']]);
            $assignment_count += $assignment_stmt->fetchColumn();
        }
    }

    // Get recent updates for all children
    $updates_stmt = $pdo->prepare('
        SELECT
            n.message,
            n.created_at,
            u.name as student_name,
            n.type
        FROM notifications n
        JOIN users u ON n.user_id = u.id
        WHERE n.user_id IN (
            SELECT student_id FROM parent_student WHERE parent_id = ?
        )
        ORDER BY n.created_at DESC
        LIMIT 4
    ');
    $updates_stmt->execute([$parent_id]);
    $recent_updates = $updates_stmt->fetchAll();
} catch (Exception $e) {
    // Handle any database errors gracefully
    $total_children = 0;
    $avg_grade = 0;
    $avg_attendance = 0;
    $assignment_count = 0;
    $children = [];
    $recent_updates = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - EduWave</title>

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
                        <li class="sidebar-item active ">
                            <a href="dashboard.php" class='sidebar-link'>
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>My Children</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="my_children.php">View Children</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-mortarboard"></i>
                                <span>Academic Performance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="child_grades.php">Grades</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_attendance.php">Attendance</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_timetable.php">Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-text"></i>
                                <span>Reports</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="weekly_summary.php">Weekly Summary</a>
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
                <h3>Parent Dashboard</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon purple">
                                                    <i class="bi bi-people"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">My Children</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_children ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon blue">
                                                    <i class="bi bi-graph-up"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Avg. Grades</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($avg_grade, 1) ?>%</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
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
                                                <h6 class="font-extrabold mb-0"><?= number_format($avg_attendance, 1) ?>%</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon red">
                                                    <i class="bi bi-journal-bookmark"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Pending Assignments</h6>
                                                <h6 class="font-extrabold mb-0"><?= $assignment_count ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>My Children</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-lg">
                                                <thead>
                                                    <tr>
                                                        <th>Child Name</th>
                                                        <th>Grade</th>
                                                        <th>Avg. Grade</th>
                                                        <th>Attendance</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($children as $child): ?>
                                                    <?php
                                                        // Get child's class
                                                        try {
                                                            $class_stmt = $pdo->prepare('SELECT classes.name FROM classes JOIN student_classes ON classes.id = student_classes.class_id WHERE student_classes.student_id = ?');
                                                            $class_stmt->execute([$child['id']]);
                                                            $class = $class_stmt->fetch();
                                                        } catch (Exception $e) {
                                                            $class = null;
                                                        }

                                                        // Get child's average grade
                                                        try {
                                                            $grade_stmt = $pdo->prepare('SELECT AVG(score) as avg_score FROM grades WHERE student_id = ?');
                                                            $grade_stmt->execute([$child['id']]);
                                                            $avg_score = $grade_stmt->fetch();
                                                        } catch (Exception $e) {
                                                            $avg_score = ['avg_score' => null];
                                                        }

                                                        // Get child's attendance
                                                        try {
                                                            $attendance_stmt = $pdo->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?');
                                                            $attendance_stmt->execute([$child['id']]);
                                                            $attendance = $attendance_stmt->fetch();

                                                            $attendance_percent = $attendance && $attendance['total'] > 0 ? ($attendance['present'] / $attendance['total']) * 100 : 0;
                                                        } catch (Exception $e) {
                                                            $attendance_percent = 0;
                                                        }

                                                        // Format grade
                                                        $grade_display = $avg_score && $avg_score['avg_score'] ? round($avg_score['avg_score'], 1) . '%' : 'N/A';
                                                        $grade_badge = 'bg-secondary';
                                                        if ($avg_score && $avg_score['avg_score']) {
                                                            if ($avg_score['avg_score'] >= 90) {
                                                                $grade_badge = 'bg-success';
                                                            } elseif ($avg_score['avg_score'] >= 80) {
                                                                $grade_badge = 'bg-primary';
                                                            } elseif ($avg_score['avg_score'] >= 70) {
                                                                $grade_badge = 'bg-info';
                                                            } elseif ($avg_score['avg_score'] >= 60) {
                                                                $grade_badge = 'bg-warning';
                                                            } else {
                                                                $grade_badge = 'bg-danger';
                                                            }
                                                        } else {
                                                            $grade_display = 'N/A';
                                                        }

                                                        // Format attendance
                                                        $attendance_badge = 'bg-secondary';
                                                        if ($attendance_percent >= 95) {
                                                            $attendance_badge = 'bg-success';
                                                        } elseif ($attendance_percent >= 85) {
                                                            $attendance_badge = 'bg-primary';
                                                        } elseif ($attendance_percent >= 75) {
                                                            $attendance_badge = 'bg-warning';
                                                        } elseif ($attendance_percent >= 0) {
                                                            $attendance_badge = 'bg-danger';
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td class="col-3">
                                                            <h5 class="mb-0"><?= htmlspecialchars($child['name']) ?></h5>
                                                        </td>
                                                        <td class="col-2">
                                                            <p class=" mb-0"><?= $class ? htmlspecialchars($class['name']) : 'N/A' ?></p>
                                                        </td>
                                                        <td class="col-2">
                                                            <span class="badge <?= $grade_badge ?>"><?= $grade_display ?></span>
                                                        </td>
                                                        <td class="col-2">
                                                            <span class="badge <?= $attendance_badge ?>"><?= number_format($attendance_percent, 1) ?>%</span>
                                                        </td>
                                                        <td class="col-3">
                                                            <a href="child_grades.php?child_id=<?= $child['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($children)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">
                                                            <i class="bi bi-people fs-1"></i>
                                                            <p>No children found in your account</p>
                                                        </td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4 px-5">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xl">
                                        <img src="../dashboardassets/images/faces/5.jpg" alt="Parent">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold"><?=$username?></h5>
                                        <h6 class="text-muted mb-0">Parent</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Updates</h4>
                            </div>
                            <div class="card-content pb-4">
                                <?php if (!empty($recent_updates)): ?>
                                    <?php foreach ($recent_updates as $update): ?>
                                        <div class="recent-message d-flex px-4 py-3">
                                            <div class="name ms-4">
                                                <h5 class="mb-1"><?= htmlspecialchars($update['message']) ?></h5>
                                                <h6 class="text-muted mb-0">for <?= htmlspecialchars($update['student_name']) ?></h6>
                                                <small class="text-muted"><?= date('M j, Y', strtotime($update['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-bell fs-1 text-muted"></i>
                                        <p class="text-muted">No recent updates</p>
                                    </div>
                                <?php endif; ?>
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

    <script src="../dashboardassets/vendors/apexcharts/apexcharts.js"></script>
    <script src="../dashboardassets/js/pages/dashboard.js"></script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>