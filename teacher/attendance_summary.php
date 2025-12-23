<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher User';

// Get teacher's assigned classes and subjects
try {
    $stmt = $pdo->prepare('SELECT DISTINCT classes.id as class_id, classes.grade_name, subjects.id as subject_id, subjects.name AS subject_name FROM teacher_assignments JOIN classes ON teacher_assignments.class_id = classes.id JOIN subjects ON teacher_assignments.subject_id = subjects.id WHERE teacher_assignments.teacher_id = ? ORDER BY classes.grade_name, subjects.name');
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assignments = [];
}

$attendance_data = [];
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$selected_subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d', strtotime('-30 days'));
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

if ($selected_class_id && $selected_subject_id) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                u.id as student_id,
                u.name,
                COUNT(a.id) as total_records,
                SUM(CASE WHEN a.status = "Present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = "Absent" THEN 1 ELSE 0 END) as absent_count,
                ROUND((SUM(CASE WHEN a.status = "Present" THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_percentage
            FROM users u
            JOIN student_classes sc ON u.id = sc.student_id
            LEFT JOIN attendance a ON u.id = a.student_id AND a.class_id = ? AND a.subject_id = ? AND a.attend_date BETWEEN ? AND ?
            WHERE sc.class_id = ?
            GROUP BY u.id, u.name
            ORDER BY u.name
        ');
        $stmt->execute([$selected_class_id, $selected_subject_id, $from_date, $to_date, $selected_class_id]);
        $attendance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get daily attendance summary
        $stmt = $pdo->prepare('
            SELECT 
                attend_date,
                COUNT(*) as total_students,
                SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent,
                ROUND((SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
            FROM attendance 
            WHERE class_id = ? AND subject_id = ? AND attend_date BETWEEN ? AND ?
            GROUP BY attend_date
            ORDER BY attend_date DESC
        ');
        $stmt->execute([$selected_class_id, $selected_subject_id, $from_date, $to_date]);
        $daily_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $attendance_data = [];
        $daily_summary = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary - EduWave Teacher</title>

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

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>My Classes</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="class_list.php">View Classes</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-clipboard"></i>
                                <span>Attendance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="attendance.php">Take Attendance</a>
                                </li>
                                <li class="submenu-item active">
                                    <a href="attendance_summary.php">View Summary</a>
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
                                <li class="submenu-item">
                                    <a href="grades_summary.php">View Summary</a>
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
                <h3>Attendance Summary</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Attendance Reports</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="class_id" class="form-label">Class & Subject</label>
                                            <select class="form-select" id="class_id" name="class_id" required>
                                                <option value="">Select Class & Subject</option>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <option value="<?= $assignment['class_id'] ?>-<?= $assignment['subject_id'] ?>" <?= ($selected_class_id . '-' . $selected_subject_id == $assignment['class_id'] . '-' . $assignment['subject_id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($assignment['grade_name'] . ' - ' . $assignment['subject_name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="from_date" class="form-label">From Date</label>
                                            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="to_date" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">Generate Report</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if (!empty($attendance_data)): ?>
                                    <!-- Summary Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card border-primary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-primary">Total Students</h5>
                                                    <h3><?= count($attendance_data) ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-success">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-success">Avg Attendance</h5>
                                                    <h3><?= number_format(array_sum(array_column($attendance_data, 'attendance_percentage')) / count($attendance_data), 1) ?>%</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-info">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-info">Total Records</h5>
                                                    <h3><?= array_sum(array_column($attendance_data, 'total_records')) ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-warning">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-warning">Low Attendance</h5>
                                                    <h3><?= count(array_filter($attendance_data, function($a) { return $a['attendance_percentage'] < 75; })) ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Attendance Table -->
                                    <h5>Individual Student Attendance</h5>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Total Records</th>
                                                    <th>Present</th>
                                                    <th>Absent</th>
                                                    <th>Attendance %</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendance_data as $student): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                                        <td><?= $student['total_records'] ?></td>
                                                        <td><?= $student['present_count'] ?></td>
                                                        <td><?= $student['absent_count'] ?></td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar <?= $student['attendance_percentage'] >= 75 ? 'bg-success' : ($student['attendance_percentage'] >= 60 ? 'bg-warning' : 'bg-danger') ?>" 
                                                                     style="width: <?= $student['attendance_percentage'] ?>%">
                                                                    <?= $student['attendance_percentage'] ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($student['attendance_percentage'] >= 75): ?>
                                                                <span class="badge bg-success">Good</span>
                                                            <?php elseif ($student['attendance_percentage'] >= 60): ?>
                                                                <span class="badge bg-warning">Fair</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Poor</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Daily Summary -->
                                    <?php if (!empty($daily_summary)): ?>
                                        <h5>Daily Attendance Summary</h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Total Students</th>
                                                        <th>Present</th>
                                                        <th>Absent</th>
                                                        <th>Attendance %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($daily_summary as $day): ?>
                                                        <tr>
                                                            <td><?= date('M j, Y', strtotime($day['attend_date'])) ?></td>
                                                            <td><?= $day['total_students'] ?></td>
                                                            <td><?= $day['present'] ?></td>
                                                            <td><?= $day['absent'] ?></td>
                                                            <td>
                                                                <span class="badge <?= $day['percentage'] >= 75 ? 'bg-success' : ($day['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                                                    <?= $day['percentage'] ?>%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>

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
    <script src="../dashboardassets/js/main.js"></script>

    <script>
        document.getElementById('class_id').addEventListener('change', function() {
            var value = this.value;
            if (value) {
                var parts = value.split('-');
                var class_id = parts[0];
                var subject_id = parts[1];
                
                // Update hidden fields
                var form = this.closest('form');
                
                // If we want to auto-submit when selection changes
                // form.submit();
            }
        });
    </script>
</body>

</html>