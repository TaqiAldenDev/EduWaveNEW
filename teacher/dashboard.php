<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher';

// Get counts for statistics cards
try {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT ta.class_id) FROM teacher_assignments ta WHERE ta.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_classes = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT sc.student_id) FROM student_classes sc JOIN teacher_assignments ta ON sc.class_id = ta.class_id WHERE ta.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_students = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT a.id) FROM assignments a JOIN teacher_assignments ta ON a.subject_id = ta.subject_id AND a.class_id = ta.class_id WHERE ta.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_assignments = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT ta.subject_id) FROM teacher_assignments ta WHERE ta.teacher_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_subjects = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_classes = 0;
    $total_students = 0;
    $total_assignments = 0;
    $total_subjects = 0;
}

// Get upcoming assignments
try {
    $stmt = $pdo->prepare("
        SELECT
            assignments.title as title,
            assignments.description as description,
            assignments.due_date as due_date,
            classes.grade_name as class_name,
            subjects.name as subject_name
        FROM assignments
        JOIN teacher_assignments ON assignments.subject_id = teacher_assignments.subject_id AND assignments.class_id = teacher_assignments.class_id
        JOIN classes ON assignments.class_id = classes.id
        JOIN subjects ON assignments.subject_id = subjects.id
        WHERE teacher_assignments.teacher_id = ?
        AND assignments.due_date >= CURDATE()
        ORDER BY assignments.due_date ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $upcoming_assignments = [];
}

// Get today's schedule
$today_schedule = [];
$today_name = date('D');
try {
    $stmt = $pdo->prepare("
        SELECT
            subjects.name as subject_name,
            classes.grade_name as class_name,
            schedule.start_time,
            schedule.end_time
        FROM schedule
        JOIN subjects ON schedule.subject_id = subjects.id
        JOIN classes ON schedule.class_id = classes.id
        WHERE schedule.teacher_id = ?
        AND schedule.day_of_week = ?
        ORDER BY schedule.start_time
    ");
    $stmt->execute([$_SESSION['user_id'], $today_name]);
    $today_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $today_schedule = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EduWave</title>

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
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>My Classes</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
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
                                <li class="submenu-item ">
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
                                <li class="submenu-item ">
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
                                <li class="submenu-item ">
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
                <h3>Teacher Dashboard</h3>
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
                                                    <i class="bi bi-journal-bookmark-fill"></i>
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
                                                    <i class="bi bi-people"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Students</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_students ?></h6>
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
                                                    <i class="bi bi-clipboard"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Assignments</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_assignments ?></h6>
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
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Subjects</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_subjects ?></h6>
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
                                        <h4>Today's Schedule - <?= date('l, F j, Y') ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-lg">
                                                <tbody>
                                                    <?php if (!empty($today_schedule)): ?>
                                                        <?php foreach ($today_schedule as $schedule): ?>
                                                            <tr>
                                                                <td class="col-3">
                                                                    <div class="fw-bold"><?= date('h:i A', strtotime($schedule['start_time'])) ?> - <?= date('h:i A', strtotime($schedule['end_time'])) ?></div>
                                                                </td>
                                                                <td class="col-9">
                                                                    <div class="fw-bold"><?= htmlspecialchars($schedule['subject_name']) ?></div>
                                                                    <small class="text-muted"><?= htmlspecialchars($schedule['class_name']) ?></small>
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
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Upcoming Tasks</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-lg">
                                                <tbody>
                                                    <?php if (!empty($upcoming_assignments)): ?>
                                                        <?php foreach ($upcoming_assignments as $assignment): ?>
                                                            <tr>
                                                                <td class="col-10">
                                                                    <div class="fw-bold"><?= htmlspecialchars($assignment['title']) ?></div>
                                                                    <small class="text-muted"><?= htmlspecialchars($assignment['subject_name']) ?> - <?= htmlspecialchars($assignment['class_name']) ?></small>
                                                                    <?php if (!empty($assignment['description'])): ?>
                                                                        <br><small class="text-muted"><?= htmlspecialchars(substr($assignment['description'], 0, 50)) ?><?= strlen($assignment['description']) > 50 ? '...' : '' ?></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="col-2">
                                                                    <?php
                                                                    try {
                                                                        $due_date = new DateTime($assignment['due_date']);
                                                                        $today = new DateTime();
                                                                        $today->setTime(0, 0, 0);
                                                                        $due_date->setTime(0, 0, 0);
                                                                        $diff = $today->diff($due_date);
                                                                        $days_diff = $diff->days;
                                                                        $inverted = $diff->invert;

                                                                        if ($inverted == 1) {
                                                                            $badge_class = 'bg-danger';
                                                                            $date_text = 'Overdue';
                                                                        } elseif ($days_diff == 0) {
                                                                            $badge_class = 'bg-primary';
                                                                            $date_text = 'Today';
                                                                        } elseif ($days_diff == 1) {
                                                                            $badge_class = 'bg-warning';
                                                                            $date_text = 'Tomorrow';
                                                                        } else {
                                                                            $badge_class = 'bg-info';
                                                                            $date_text = $due_date->format('M j');
                                                                        }
                                                                    } catch (Exception $e) {
                                                                        $badge_class = 'bg-secondary';
                                                                        $date_text = 'N/A';
                                                                    }
                                                                    ?>
                                                                    <span class="badge <?= $badge_class ?>"><?= $date_text ?></span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="2" class="text-center text-muted">No upcoming assignments</td>
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
                                        <img src="../dashboardassets/images/faces/3.jpg" alt="Teacher">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold"><?=$username?></h5>
                                        <h6 class="text-muted mb-0">Teacher</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>My Classes</h4>
                            </div>
                            <div class="card-content pb-4">
                                <?php
                                // Get teacher's assigned classes and subjects with student counts
                                try {
                                    $stmt = $pdo->prepare('
                                        SELECT DISTINCT
                                            classes.id as class_id,
                                            classes.grade_name,
                                            subjects.id as subject_id,
                                            subjects.name AS subject_name,
                                            (
                                                SELECT COUNT(DISTINCT sc.student_id)
                                                FROM student_classes sc
                                                WHERE sc.class_id = classes.id
                                            ) as student_count
                                        FROM teacher_assignments
                                        JOIN classes ON teacher_assignments.class_id = classes.id
                                        JOIN subjects ON teacher_assignments.subject_id = subjects.id
                                        WHERE teacher_assignments.teacher_id = ?
                                        ORDER BY classes.grade_name, subjects.name
                                    ');
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $teacher_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } catch (PDOException $e) {
                                    $teacher_classes = [];
                                }

                                if (!empty($teacher_classes)) {
                                    foreach ($teacher_classes as $class) {
                                        ?>
                                        <div class="recent-message d-flex px-4 py-3">
                                            <div class="name ms-4">
                                                <h5 class="mb-1"><?= htmlspecialchars($class['subject_name']) ?> - <?= htmlspecialchars($class['grade_name']) ?></h5>
                                                <h6 class="text-muted mb-0"><?= (int)$class['student_count'] ?> Students</h6>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <div class="px-4 py-3">
                                        <p class="text-muted mb-0">No classes assigned</p>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="col-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Activity</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Class</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Get recent activities
                                            $recent_activities = [];

                                            // Get recent assignments created
                                            try {
                                                $stmt = $pdo->prepare("
                                                    SELECT
                                                        'Assignment' as activity_type,
                                                        assignments.title as activity_name,
                                                        classes.grade_name as class_name,
                                                        subjects.name as subject_name,
                                                        assignments.created_at as activity_date,
                                                        assignments.due_date as due_date
                                                    FROM assignments
                                                    JOIN teacher_assignments ON assignments.subject_id = teacher_assignments.subject_id AND assignments.class_id = teacher_assignments.class_id
                                                    JOIN classes ON assignments.class_id = classes.id
                                                    JOIN subjects ON assignments.subject_id = subjects.id
                                                    WHERE teacher_assignments.teacher_id = ?
                                                    ORDER BY assignments.created_at DESC
                                                    LIMIT 5
                                                ");
                                                $stmt->execute([$_SESSION['user_id']]);
                                                $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($assignments as $assignment) {
                                                    try {
                                                        $due_date = new DateTime($assignment['due_date']);
                                                        $today = new DateTime();
                                                        $today->setTime(0, 0, 0);
                                                        $due_date->setTime(0, 0, 0);
                                                        $is_overdue = $due_date < $today;
                                                    } catch (Exception $e) {
                                                        $is_overdue = false;
                                                    }

                                                    $recent_activities[] = [
                                                        'type' => 'Assignment',
                                                        'name' => $assignment['activity_name'],
                                                        'class' => $assignment['class_name'] . ' (' . $assignment['subject_name'] . ')',
                                                        'date' => $assignment['activity_date'],
                                                        'status' => $is_overdue ? 'Overdue' : 'Active'
                                                    ];
                                                }
                                            } catch (PDOException $e) {
                                                // Handle error
                                            }

                                            // Get recent grades entered
                                            try {
                                                $stmt = $pdo->prepare("
                                                    SELECT
                                                        'Grade' as activity_type,
                                                        subjects.name as activity_name,
                                                        classes.grade_name as class_name,
                                                        grades.date_given as activity_date,
                                                        COUNT(grades.id) as grade_count
                                                    FROM grades
                                                    JOIN teacher_assignments ON grades.subject_id = teacher_assignments.subject_id
                                                    JOIN subjects ON grades.subject_id = subjects.id
                                                    JOIN classes ON teacher_assignments.class_id = classes.id
                                                    WHERE teacher_assignments.teacher_id = ?
                                                    GROUP BY subjects.id, classes.id, grades.date_given
                                                    ORDER BY grades.date_given DESC
                                                    LIMIT 5
                                                ");
                                                $stmt->execute([$_SESSION['user_id']]);
                                                $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($grades as $grade) {
                                                    $recent_activities[] = [
                                                        'type' => 'Grade Entry',
                                                        'name' => $grade['activity_name'] . ' (' . $grade['grade_count'] . ' students)',
                                                        'class' => $grade['class_name'],
                                                        'date' => $grade['activity_date'],
                                                        'status' => 'Recorded'
                                                    ];
                                                }
                                            } catch (PDOException $e) {
                                                // Handle error
                                            }

                                            // Sort activities by date (most recent first)
                                            usort($recent_activities, function($a, $b) {
                                                return strtotime($b['date']) - strtotime($a['date']);
                                            });

                                            // Take only the first 5
                                            $recent_activities = array_slice($recent_activities, 0, 5);

                                            if (!empty($recent_activities)) {
                                                foreach ($recent_activities as $activity) {
                                                    ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($activity['type']) . ': ' . htmlspecialchars($activity['name']) ?></td>
                                                        <td><?= htmlspecialchars($activity['class']) ?></td>
                                                        <td><?= date('M j, Y', strtotime($activity['date'])) ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = '';
                                                            switch($activity['status']) {
                                                                case 'Overdue':
                                                                    $status_class = 'bg-danger';
                                                                    break;
                                                                case 'Active':
                                                                    $status_class = 'bg-success';
                                                                    break;
                                                                case 'Recorded':
                                                                    $status_class = 'bg-info';
                                                                    break;
                                                                default:
                                                                    $status_class = 'bg-secondary';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $status_class ?>"><?= $activity['status'] ?></span>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No recent activities</td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
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

    <script src="../dashboardassets/vendors/apexcharts/apexcharts.js"></script>
    <script src="../dashboardassets/js/pages/dashboard.js"></script>

    <!-- Charts Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Class Distribution Chart
            <?php
            // Get class distribution data
            try {
                $stmt = $pdo->prepare('
                    SELECT
                        classes.grade_name,
                        subjects.name as subject_name,
                        COUNT(DISTINCT sc.student_id) as student_count
                    FROM teacher_assignments ta
                    JOIN classes ON ta.class_id = classes.id
                    JOIN subjects ON ta.subject_id = subjects.id
                    LEFT JOIN student_classes sc ON classes.id = sc.class_id
                    WHERE ta.teacher_id = ?
                    GROUP BY classes.id, subjects.id
                    ORDER BY classes.grade_name, subjects.name
                ');
                $stmt->execute([$_SESSION['user_id']]);
                $class_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $class_labels = [];
                $class_counts = [];
                foreach ($class_data as $class) {
                    $class_labels[] = htmlspecialchars($class['grade_name'] . ' - ' . $class['subject_name']);
                    $class_counts[] = (int)$class['student_count'];
                }
            } catch (PDOException $e) {
                $class_labels = [];
                $class_counts = [];
            }
            ?>

            var classChartOptions = {
                chart: {
                    type: 'bar',
                    height: 300
                },
                series: [{
                    name: 'Students',
                    data: <?php echo json_encode($class_counts); ?>
                }],
                xaxis: {
                    categories: <?php echo json_encode($class_labels); ?>
                },
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " students"
                        }
                    }
                }
            }

            if (<?php echo count($class_labels); ?> > 0) {
                var classChart = new ApexCharts(document.querySelector("#classChart"), classChartOptions);
                classChart.render();
            } else {
                document.getElementById('classChart').innerHTML = '<p class="text-center text-muted">No class data available</p>';
            }

            // Recent Grades Chart
            <?php
            // Get recent grades data
            try {
                $stmt = $pdo->prepare('
                    SELECT
                        subjects.name as subject_name,
                        classes.grade_name,
                        AVG(grades.score) as avg_score
                    FROM grades
                    JOIN subjects ON grades.subject_id = subjects.id
                    JOIN teacher_assignments ON grades.subject_id = teacher_assignments.subject_id
                    JOIN classes ON teacher_assignments.class_id = classes.id
                    WHERE teacher_assignments.teacher_id = ?
                    GROUP BY subjects.id, classes.id
                    ORDER BY subjects.name, classes.grade_name
                    LIMIT 8
                ');
                $stmt->execute([$_SESSION['user_id']]);
                $grade_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $grade_labels = [];
                $grade_averages = [];
                foreach ($grade_data as $grade) {
                    $grade_labels[] = htmlspecialchars($grade['subject_name'] . ' (' . $grade['grade_name'] . ')');
                    $grade_averages[] = round((float)$grade['avg_score'], 1);
                }
            } catch (PDOException $e) {
                $grade_labels = [];
                $grade_averages = [];
            }
            ?>

            var gradeChartOptions = {
                series: [{
                    name: 'Average Score',
                    data: <?php echo json_encode($grade_averages); ?>
                }],
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    categories: <?php echo json_encode($grade_labels); ?>
                },
                stroke: {
                    curve: 'smooth'
                },
                markers: {
                    size: 6
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " points"
                        }
                    }
                }
            };

            if (<?php echo count($grade_labels); ?> > 0) {
                var gradeChart = new ApexCharts(document.querySelector("#gradeChart"), gradeChartOptions);
                gradeChart.render();
            } else {
                document.getElementById('gradeChart').innerHTML = '<p class="text-center text-muted">No grade data available</p>';
            }
        });
    </script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>
