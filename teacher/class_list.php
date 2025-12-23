<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher User';

// Get teacher's assigned classes and subjects with student count and statistics
try {
    $stmt = $pdo->prepare('
        SELECT
            classes.id as class_id,
            classes.grade_name,
            subjects.id as subject_id,
            subjects.name AS subject_name,
            (SELECT COUNT(sc.student_id) FROM student_classes sc WHERE sc.class_id = classes.id AND sc.academic_year = YEAR(CURDATE())) AS student_count,
            (SELECT COUNT(*) FROM attendance a WHERE a.class_id = classes.id AND a.subject_id = subjects.id AND a.attend_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS attendance_records,
            (SELECT COUNT(*) FROM grades g WHERE g.subject_id = subjects.id) AS grade_records
        FROM teacher_assignments
        JOIN classes ON teacher_assignments.class_id = classes.id
        JOIN subjects ON teacher_assignments.subject_id = subjects.id
        WHERE teacher_assignments.teacher_id = ?
        ORDER BY classes.grade_name, subjects.name
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classes = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - EduWave Teacher</title>

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

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>My Classes</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
                                    <a href="class_list.php">View Classes</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-clipboard"></i>
                                <span>Attendance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="attendance.php">Take Attendance</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-award"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="grades_enter.php">Enter Grades</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Homework</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="homework_add.php">Add Homework</a>
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
                <h3>My Classes</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>My Classes and Subjects</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Class</th>
                                                <th>Subject</th>
                                                <th>Current Students</th>
                                                <th>Attendance Records (30 days)</th>
                                                <th>Grade Records</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($classes as $class): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($class['grade_name']) ?></strong></td>
                                                    <td><?= htmlspecialchars($class['subject_name']) ?></td>
                                                    <td>
                                                        <span class="badge bg-primary"><?= $class['student_count'] ?> students</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= $class['attendance_records'] ?> records</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?= $class['grade_records'] ?> records</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="attendance.php?assignment=<?= $class['class_id'] ?>-<?= $class['subject_id'] ?>" class="btn btn-sm btn-outline-primary" title="Take Attendance">
                                                                <i class="bi bi-clipboard-check"></i> Attendance
                                                            </a>
                                                            <a href="grades_enter.php?assignment=<?= $class['class_id'] ?>-<?= $class['subject_id'] ?>" class="btn btn-sm btn-outline-success" title="Enter Grades">
                                                                <i class="bi bi-award"></i> Grades
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php if (empty($classes)): ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No classes assigned yet. Please contact the administrator.</p>
                                        </div>
                                    <?php endif; ?>
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
