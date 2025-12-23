<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student User';

$student_id = $_SESSION['user_id'];

// Get all grades with more details
try {
    $stmt = $pdo->prepare('
        SELECT
            subjects.name as subject_name,
            grades.exam_type,
            grades.score,
            grades.date_given
        FROM grades
        JOIN subjects ON grades.subject_id = subjects.id
        WHERE grades.student_id = ?
        ORDER BY grades.date_given DESC
    ');
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics
    $total_grades = count($grades);
    $avg_score = 0;
    $highest_score = 0;
    $lowest_score = 100;
    $subject_averages = [];

    if ($total_grades > 0) {
        $total_score = 0;
        foreach ($grades as $grade) {
            $score = (float)$grade['score'];
            $total_score += $score;
            if ($score > $highest_score) $highest_score = $score;
            if ($score < $lowest_score) $lowest_score = $score;

            // Calculate averages per subject
            if (!isset($subject_averages[$grade['subject_name']])) {
                $subject_averages[$grade['subject_name']] = ['total' => 0, 'count' => 0];
            }
            $subject_averages[$grade['subject_name']]['total'] += $score;
            $subject_averages[$grade['subject_name']]['count']++;
        }
        $avg_score = round($total_score / $total_grades, 1);
    } else {
        $highest_score = 0;
        $lowest_score = 0;
    }

    // Calculate subject averages
    foreach ($subject_averages as $subject => $data) {
        $subject_averages[$subject]['avg'] = round($data['total'] / $data['count'], 1);
    }

} catch (PDOException $e) {
    $grades = [];
    $total_grades = 0;
    $avg_score = 0;
    $highest_score = 0;
    $lowest_score = 0;
    $subject_averages = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - EduWave Student</title>

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
                                <i class="bi bi-calendar-check"></i>
                                <span>Timetable</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="timetable.php">View Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-graph-up"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
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
                                <li class="submenu-item">
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
                                <li class="submenu-item">
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
                                <li class="submenu-item">
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
                <h3>My Grades</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon purple">
                                            <i class="bi bi-journal-bookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Total Grades</h6>
                                        <h6 class="font-extrabold mb-0"><?= $total_grades ?></h6>
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
                                        <h6 class="text-muted font-semibold">Average</h6>
                                        <h6 class="font-extrabold mb-0"><?= $avg_score ?>%</h6>
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
                                            <i class="bi bi-trophy"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Highest</h6>
                                        <h6 class="font-extrabold mb-0"><?= $highest_score ?>%</h6>
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
                                            <i class="bi bi-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Lowest</h6>
                                        <h6 class="font-extrabold mb-0"><?= $lowest_score ?>%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <?php if (!empty($subject_averages)): ?>
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Average by Subject</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($subject_averages as $subject => $data): ?>
                                        <div class="col-12 col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="text-muted"><?= htmlspecialchars($subject) ?></h6>
                                                    <h4 class="font-extrabold mb-0"><?= $data['avg'] ?>%</h4>
                                                    <small class="text-muted"><?= $data['count'] ?> grades</small>
                                                    <div class="progress mt-2" style="height: 6px;">
                                                        <?php
                                                        $progress_class = 'bg-danger';
                                                        if ($data['avg'] >= 90) $progress_class = 'bg-success';
                                                        elseif ($data['avg'] >= 80) $progress_class = 'bg-primary';
                                                        elseif ($data['avg'] >= 70) $progress_class = 'bg-info';
                                                        elseif ($data['avg'] >= 60) $progress_class = 'bg-warning';
                                                        ?>
                                                        <div class="progress-bar <?= $progress_class ?>" style="width: <?= $data['avg'] ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Grade Report</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Exam Type</th>
                                                <th>Score</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($grades)): ?>
                                                <?php foreach ($grades as $grade): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                                                        <td><?= htmlspecialchars($grade['exam_type']) ?></td>
                                                        <td><?= htmlspecialchars($grade['score']) ?>%</td>
                                                        <td><?= date('M j, Y', strtotime($grade['date_given'])) ?></td>
                                                        <td>
                                                            <?php
                                                            $score = (float)$grade['score'];
                                                            if ($score >= 90) {
                                                                $status = 'Excellent';
                                                                $badge_class = 'success';
                                                            } elseif ($score >= 80) {
                                                                $status = 'Good';
                                                                $badge_class = 'primary';
                                                            } elseif ($score >= 70) {
                                                                $status = 'Satisfactory';
                                                                $badge_class = 'info';
                                                            } elseif ($score >= 60) {
                                                                $status = 'Needs Improvement';
                                                                $badge_class = 'warning';
                                                            } else {
                                                                $status = 'Fail';
                                                                $badge_class = 'danger';
                                                            }
                                                            ?>
                                                            <span class="badge bg-<?= $badge_class ?>"><?= $status ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No grades available yet</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
