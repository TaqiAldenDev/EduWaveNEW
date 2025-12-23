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

// Define exam types
$exam_types = ['Quiz', 'Test', 'Midterm', 'Final', 'Assignment', 'Project', 'Participation', 'Lab', 'Homework'];

$grade_data = [];
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$selected_subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';

if ($selected_class_id && $selected_subject_id) {
    try {
        $stmt = $pdo->prepare('
            SELECT 
                u.id as student_id,
                u.name,
                g.exam_type,
                g.score,
                g.date_given
            FROM users u
            JOIN student_classes sc ON u.id = sc.student_id
            LEFT JOIN grades g ON u.id = g.student_id AND g.subject_id = ?
            WHERE sc.class_id = ?
            ORDER BY u.name, g.exam_type, g.date_given
        ');
        $stmt->execute([$selected_subject_id, $selected_class_id]);
        $all_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group grades by student
        $students = [];
        foreach ($all_grades as $grade) {
            if (!isset($students[$grade['student_id']])) {
                $students[$grade['student_id']] = [
                    'name' => $grade['name'],
                    'grades' => []
                ];
            }
            if ($grade['exam_type']) {
                $students[$grade['student_id']]['grades'][] = $grade;
            }
        }
        
        // Get grade statistics by exam type
        $stmt = $pdo->prepare('
            SELECT 
                exam_type,
                COUNT(*) as count,
                AVG(score) as average,
                MIN(score) as minimum,
                MAX(score) as maximum,
                SUM(CASE WHEN score >= 60 THEN 1 ELSE 0 END) as passing,
                ROUND((SUM(CASE WHEN score >= 60 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as pass_rate
            FROM grades 
            WHERE subject_id = ?
            GROUP BY exam_type
            ORDER BY exam_type
        ');
        $stmt->execute([$selected_subject_id]);
        $exam_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get overall class statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_grades,
                AVG(score) as class_average,
                MIN(score) as class_min,
                MAX(score) as class_max,
                SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as a_count,
                SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) as b_count,
                SUM(CASE WHEN score >= 70 AND score < 80 THEN 1 ELSE 0 END) as c_count,
                SUM(CASE WHEN score >= 60 AND score < 70 THEN 1 ELSE 0 END) as d_count,
                SUM(CASE WHEN score < 60 THEN 1 ELSE 0 END) as f_count
            FROM grades 
            WHERE subject_id = ?
        ');
        $stmt->execute([$selected_subject_id]);
        $class_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $students = [];
        $exam_stats = [];
        $class_stats = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Summary - EduWave Teacher</title>

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

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-clipboard"></i>
                                <span>Attendance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="attendance.php">Take Attendance</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="attendance_summary.php">View Summary</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-award"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="grades_enter.php">Enter Grades</a>
                                </li>
                                <li class="submenu-item active">
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
                <h3>Grades Summary</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Grade Reports</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-4">
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
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">Generate Report</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if (!empty($students)): ?>
                                    <!-- Overall Statistics -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card border-primary">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-primary">Class Average</h5>
                                                    <h3><?= number_format($class_stats['class_average'], 1) ?>%</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-success">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-success">Pass Rate</h5>
                                                    <h3><?= number_format((($class_stats['a_count'] + $class_stats['b_count'] + $class_stats['c_count'] + $class_stats['d_count']) / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-info">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-info">Total Grades</h5>
                                                    <h3><?= $class_stats['total_grades'] ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-warning">
                                                <div class="card-body text-center">
                                                    <h5 class="card-title text-warning">Grade Range</h5>
                                                    <h3><?= number_format($class_stats['class_min'], 1) ?> - <?= number_format($class_stats['class_max'], 1) ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grade Distribution -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5>Grade Distribution</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Grade</th>
                                                            <th>Count</th>
                                                            <th>Percentage</th>
                                                            <th>Visual</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><span class="badge bg-success">A</span> (90-100%)</td>
                                                            <td><?= $class_stats['a_count'] ?></td>
                                                            <td><?= number_format(($class_stats['a_count'] / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-success" style="width: <?= ($class_stats['a_count'] / max($class_stats['total_grades'], 1)) * 100 ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="badge bg-info">B</span> (80-89%)</td>
                                                            <td><?= $class_stats['b_count'] ?></td>
                                                            <td><?= number_format(($class_stats['b_count'] / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-info" style="width: <?= ($class_stats['b_count'] / max($class_stats['total_grades'], 1)) * 100 ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="badge bg-warning">C</span> (70-79%)</td>
                                                            <td><?= $class_stats['c_count'] ?></td>
                                                            <td><?= number_format(($class_stats['c_count'] / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-warning" style="width: <?= ($class_stats['c_count'] / max($class_stats['total_grades'], 1)) * 100 ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="badge bg-secondary">D</span> (60-69%)</td>
                                                            <td><?= $class_stats['d_count'] ?></td>
                                                            <td><?= number_format(($class_stats['d_count'] / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-secondary" style="width: <?= ($class_stats['d_count'] / max($class_stats['total_grades'], 1)) * 100 ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="badge bg-danger">F</span> (0-59%)</td>
                                                            <td><?= $class_stats['f_count'] ?></td>
                                                            <td><?= number_format(($class_stats['f_count'] / max($class_stats['total_grades'], 1)) * 100, 1) ?>%</td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-danger" style="width: <?= ($class_stats['f_count'] / max($class_stats['total_grades'], 1)) * 100 ?>%"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h5>Exam Type Statistics</h5>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Exam Type</th>
                                                            <th>Count</th>
                                                            <th>Average</th>
                                                            <th>Pass Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($exam_stats as $stat): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($stat['exam_type']) ?></td>
                                                                <td><?= $stat['count'] ?></td>
                                                                <td><?= number_format($stat['average'], 1) ?>%</td>
                                                                <td>
                                                                    <span class="badge <?= $stat['pass_rate'] >= 75 ? 'bg-success' : ($stat['pass_rate'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                                                        <?= $stat['pass_rate'] ?>%
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Individual Student Grades -->
                                    <h5>Individual Student Performance</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <?php foreach ($exam_types as $exam_type): ?>
                                                        <th><?= htmlspecialchars($exam_type) ?></th>
                                                    <?php endforeach; ?>
                                                    <th>Average</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student_id => $student): ?>
                                                    <?php
                                                    $student_grades = [];
                                                    foreach ($student['grades'] as $grade) {
                                                        $student_grades[$grade['exam_type']] = $grade['score'];
                                                    }
                                                    
                                                    $average = count($student_grades) > 0 ? array_sum($student_grades) / count($student_grades) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                                                        <?php foreach ($exam_types as $exam_type): ?>
                                                            <td>
                                                                <?php if (isset($student_grades[$exam_type])): ?>
                                                                    <?= number_format($student_grades[$exam_type], 1) ?>%
                                                                    <?php
                                                                    $score = $student_grades[$exam_type];
                                                                    if ($score >= 90) $badge = 'success';
                                                                    elseif ($score >= 80) $badge = 'info';
                                                                    elseif ($score >= 70) $badge = 'warning';
                                                                    elseif ($score >= 60) $badge = 'secondary';
                                                                    else $badge = 'danger';
                                                                    ?>
                                                                    <span class="badge bg-<?= $badge ?> ms-1"><?= getLetterGrade($score) ?></span>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                        <td>
                                                            <strong><?= number_format($average, 1) ?>%</strong>
                                                            <?php
                                                            if ($average >= 90) $status_badge = 'success';
                                                            elseif ($average >= 80) $status_badge = 'info';
                                                            elseif ($average >= 70) $status_badge = 'warning';
                                                            elseif ($average >= 60) $status_badge = 'secondary';
                                                            else $status_badge = 'danger';
                                                            ?>
                                                            <span class="badge bg-<?= $status_badge ?> ms-1"><?= getLetterGrade($average) ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if ($average >= 70): ?>
                                                                <span class="badge bg-success">Passing</span>
                                                            <?php elseif ($average >= 0): ?>
                                                                <span class="badge bg-danger">At Risk</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">No Grades</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
    <script src="../dashboardassets/js/main.js"></script>

    <?php
    // Helper function defined in PHP
    function getLetterGrade($score) {
        if ($score >= 90) return 'A';
        elseif ($score >= 80) return 'B';
        elseif ($score >= 70) return 'C';
        elseif ($score >= 60) return 'D';
        else return 'F';
    }
    ?>
</body>

</html>