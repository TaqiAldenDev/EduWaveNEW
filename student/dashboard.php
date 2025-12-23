<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student';

// Get counts for statistics cards
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_classes WHERE student_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_classes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE student_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_grades = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ? AND status = 'Present' AND attend_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_attendance_present = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE due_date >= CURDATE() AND class_id IN (SELECT class_id FROM student_classes WHERE student_id = ?)");
    $stmt->execute([$_SESSION['user_id']]);
    $pending_assignments = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_classes = 0;
    $total_grades = 0;
    $recent_attendance_present = 0;
    $pending_assignments = 0;
}

// Get upcoming assignments
try {
    $stmt = $pdo->prepare("
        SELECT
            a.title,
            a.description,
            a.due_date,
            s.name as subject_name,
            CASE
                WHEN sub.id IS NOT NULL THEN 'Submitted'
                ELSE 'Pending'
            END as submission_status
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ?
        WHERE a.due_date >= CURDATE()
        AND a.class_id IN (SELECT class_id FROM student_classes WHERE student_id = ?)
        ORDER BY a.due_date ASC
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $upcoming_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $upcoming_assignments = [];
}

// Get today's schedule for the student
try {
    $stmt = $pdo->prepare("
        SELECT
            s.start_time,
            s.end_time,
            sub.name as subject_name,
            u.name as teacher_name
        FROM schedule s
        JOIN subjects sub ON s.subject_id = sub.id
        JOIN users u ON s.teacher_id = u.id
        WHERE s.day_of_week = DATE_FORMAT(CURDATE(), '%a')
        AND s.class_id IN (SELECT class_id FROM student_classes WHERE student_id = ?)
        ORDER BY s.start_time
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $todays_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todays_schedule = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EduWave</title>

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

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-calendar"></i>
                                <span>Timetable</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="timetable.php">View Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-graph-up"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="my_grades.php">My Grades</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-clipboard-check"></i>
                                <span>Attendance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="my_attendance.php">My Attendance</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Homework</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="homework_upload.php">Upload Homework</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Library</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="library_browse.php">Browse Books</a>
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
                <h3>Student Dashboard</h3>
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
                                                    <i class="bi bi-journal-bookmark"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Classes</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_classes ?></h6>
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
                                                <h6 class="text-muted font-semibold">Grades</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_grades ?></h6>
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
                                                <h6 class="font-extrabold mb-0"><?= $recent_attendance_present ?> days</h6>
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
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Assignments</h6>
                                                <h6 class="font-extrabold mb-0"><?= $pending_assignments ?></h6>
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
                                        <h4>Today's Schedule</h4>
                                        <span class="badge bg-primary"><?= date('l, F j') ?></span>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-lg">
                                                <tbody>
                                                    <?php if (!empty($todays_schedule)): ?>
                                                        <?php foreach ($todays_schedule as $class): ?>
                                                            <tr>
                                                                <td class="col-10">
                                                                    <div class="fw-bold"><?= htmlspecialchars($class['subject_name']) ?></div>
                                                                    <small class="text-muted"><?= $class['start_time'] ?> - <?= $class['end_time'] ?></small>
                                                                </td>
                                                                <td class="col-2">
                                                                    <?php
                                                                    $current_time = date('H:i:s');
                                                                    $start_time = $class['start_time'];
                                                                    $end_time = $class['end_time'];

                                                                    if ($current_time > $end_time) {
                                                                        $badge_class = 'bg-success';
                                                                        $status = 'Completed';
                                                                    } elseif ($current_time >= $start_time && $current_time <= $end_time) {
                                                                        $badge_class = 'bg-primary';
                                                                        $status = 'In Progress';
                                                                    } else {
                                                                        $badge_class = 'bg-warning';
                                                                        $status = 'Pending';
                                                                    }
                                                                    ?>
                                                                    <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">No classes scheduled for today</td>
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
                                        <img src="../dashboardassets/images/faces/4.jpg" alt="Student">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold"><?=$username?></h5>
                                        <h6 class="text-muted mb-0">Student</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>Upcoming Assignments</h4>
                            </div>
                            <div class="card-content pb-4">
                                <?php if (!empty($upcoming_assignments)): ?>
                                    <?php foreach ($upcoming_assignments as $assignment): ?>
                                        <div class="recent-message d-flex px-4 py-3">
                                            <div class="name ms-4">
                                                <h5 class="mb-1"><?= htmlspecialchars($assignment['subject_name']) ?>: <?= htmlspecialchars($assignment['title']) ?></h5>
                                                <h6 class="text-muted mb-0"><?= htmlspecialchars($assignment['description']) ?></h6>
                                                <small class="text-muted">Due: <?= date('M j', strtotime($assignment['due_date'])) ?></small>
                                                <div>
                                                    <?php if ($assignment['submission_status'] === 'Submitted'): ?>
                                                        <small class="text-success">Submitted</small>
                                                    <?php else: ?>
                                                        <small class="text-danger">Pending</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="px-4 py-3">
                                        <div class="name ms-4">
                                            <h5 class="mb-1 text-muted">No upcoming assignments</h5>
                                            <h6 class="text-muted mb-0">No assignments due in the near future</h6>
                                        </div>
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
