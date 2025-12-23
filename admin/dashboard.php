<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

// Get comprehensive counts for statistics cards with additional dynamic data
try {
    // Basic counts
    $users_count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $classes_count = $pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();
    $library_count = $pdo->query('SELECT COUNT(*) FROM library_books')->fetchColumn();
    $events_count = $pdo->query('SELECT COUNT(*) FROM calendar_events')->fetchColumn();

    // Role-based counts
    $students_count = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "Student"')->fetchColumn();
    $teachers_count = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "Teacher"')->fetchColumn();
    $parents_count = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "Parent"')->fetchColumn();
    $registrars_count = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "Registrar"')->fetchColumn();

    // Academic statistics
    $sections_count = $pdo->query('SELECT COUNT(*) FROM sections')->fetchColumn();
    $subjects_count = $pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
    $assignments_count = $pdo->query('SELECT COUNT(*) FROM assignments')->fetchColumn();
    $grades_count = $pdo->query('SELECT COUNT(*) FROM grades')->fetchColumn();

    // Attendance statistics
    $attendance_today_count = $pdo->query('SELECT COUNT(*) FROM attendance WHERE attend_date = CURDATE() AND status = "Present"')->fetchColumn();
    $total_attendance_records = $pdo->query('SELECT COUNT(*) FROM attendance')->fetchColumn();

    // Recent activity counts
    $recent_users_count = $pdo->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
    $recent_assignments_count = $pdo->query('SELECT COUNT(*) FROM assignments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
    $recent_grades_count = $pdo->query('SELECT COUNT(*) FROM grades WHERE date_given >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();

    // Capacity utilization
    $total_capacity = $pdo->query('SELECT SUM(max_students) FROM sections')->fetchColumn();
    $total_enrolled = $pdo->query('SELECT COUNT(*) FROM student_classes')->fetchColumn();
    $capacity_utilization = $total_capacity > 0 ? round(($total_enrolled / $total_capacity) * 100, 1) : 0;

} catch (PDOException $e) {
    // Initialize all variables to prevent errors
    $users_count = $classes_count = $library_count = $events_count = 0;
    $students_count = $teachers_count = $parents_count = $registrars_count = 0;
    $sections_count = $subjects_count = $assignments_count = $grades_count = 0;
    $attendance_today_count = $total_attendance_records = 0;
    $recent_users_count = $recent_assignments_count = $recent_grades_count = 0;
    $total_capacity = $total_enrolled = 0;
    $capacity_utilization = 0;
}

// Get comprehensive recent activities
try {
    $recent_activities = [];

    // Get recent user registrations
    $stmt = $pdo->prepare("SELECT name, role, created_at FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_users as $user) {
        $recent_activities[] = [
            'activity' => 'New user registered',
            'details' => $user['name'] . ' registered as ' . $user['role'],
            'time' => $user['created_at'],
            'icon' => 'person-plus',
            'color' => 'success'
        ];
    }

    // Get recent homework assignments
    $stmt = $pdo->prepare("SELECT a.title, s.name as subject_name, u.name as teacher_name FROM assignments a JOIN subjects s ON a.subject_id = s.id JOIN users u ON a.teacher_id = u.id WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY a.created_at DESC LIMIT 3");
    $stmt->execute();
    $recent_homework = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_homework as $hw) {
        $recent_activities[] = [
            'activity' => 'New homework assigned',
            'details' => $hw['teacher_name'] . ' assigned "' . $hw['title'] . '" in ' . $hw['subject_name'],
            'time' => $hw['created_at'],
            'icon' => 'book',
            'color' => 'warning'
        ];
    }

    // Get recent grade entries
    $stmt = $pdo->prepare("SELECT g.score, s.name as subject_name, u.name as student_name, g.exam_type FROM grades g JOIN subjects s ON g.subject_id = s.id JOIN users u ON g.student_id = u.id WHERE g.date_given >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY g.date_given DESC LIMIT 2");
    $stmt->execute();
    $recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_grades as $grade) {
        $recent_activities[] = [
            'activity' => 'Grade recorded',
            'details' => $grade['student_name'] . ' scored ' . $grade['score'] . ' in ' . $grade['subject_name'] . ' (' . $grade['exam_type'] . ')',
            'time' => $grade['date_given'],
            'icon' => 'award',
            'color' => 'success'
        ];
    }

    // Get recent user registrations (limit to 2 to avoid overcrowding)
    $stmt = $pdo->prepare("SELECT name, role, created_at FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent_users as $user) {
        $recent_activities[] = [
            'activity' => 'New user registered',
            'details' => $user['name'] . ' registered as ' . $user['role'],
            'time' => $user['created_at'],
            'icon' => 'person-plus',
            'color' => 'primary'
        ];
    }

    // Sort by time descending and limit to 5 most recent
    usort($recent_activities, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    $recent_activities = array_slice($recent_activities, 0, 5);

} catch (PDOException $e) {
    $recent_activities = [];
}

// Get chart data for user growth
try {
    $stmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
    $stmt->execute();
    $user_growth = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chart_labels = [];
    $chart_data = [];

    foreach ($user_growth as $data) {
        $chart_labels[] = date('M j', strtotime($data['date']));
        $chart_data[] = $data['count'];
    }

    $chart_labels_json = json_encode($chart_labels);
    $chart_data_json = json_encode($chart_data);
} catch (PDOException $e) {
    $chart_labels_json = json_encode([]);
    $chart_data_json = json_encode([]);
}

// Get activity data for activity chart
try {
    $activity_data = [];
    $activity_labels = ['New Users', 'Assignments', 'Grades', 'Events'];
    
    $activity_data[] = $recent_users_count;
    $activity_data[] = $recent_assignments_count;
    $activity_data[] = $recent_grades_count;
    $activity_data[] = $events_count;
    
    $activity_labels_json = json_encode($activity_labels);
    $activity_data_json = json_encode($activity_data);
} catch (PDOException $e) {
    $activity_labels_json = json_encode([]);
    $activity_data_json = json_encode([]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduWave</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">

    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../dashboardassets/images/logo.svg" type="image/x-icon">

    <style>
        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid var(--primary);
        }

        .stats-icon {
            width: 4rem;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .orange { background-color: #ffa726; }
        .teal { background-color: #26c6da; }
        .indigo { background-color: #5c6bc0; }
        .pink { background-color: #ec407a; }
        .purple { background-color: #8e44ad; }
        .text-purple { color: #8e44ad; }
        .text-orange { color: #ffa726; }
        .text-teal { color: #26c6da; }
        .text-indigo { color: #5c6bc0; }
    </style>
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

                        <li class="sidebar-item">
                            <a href="users_list.php" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>User Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>Classes & Subjects</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item ">
                                    <a href="classes.php">Classes</a>
                                </li>
                                <li class="submenu-item ">
                                    <a href="subject_edit.php">Subjects</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="library_add.php" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Library Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="event_add.php" class='sidebar-link'>
                                <i class="bi bi-calendar-check"></i>
                                <span>Calendar Events</span>
                            </a>
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
                <h3>Admin Dashboard</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <!-- First Row of Cards -->
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
                                                <h6 class="text-muted font-semibold">Total Users</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($users_count) ?></h6>
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
                                                    <i class="bi bi-person-badge"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Students</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($students_count) ?></h6>
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
                                                    <i class="bi bi-person-check"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Teachers</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($teachers_count) ?></h6>
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
                                                <div class="stats-icon orange">
                                                    <i class="bi bi-journal-bookmark"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Classes</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($classes_count) ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Row of Cards -->
                        <div class="row mt-3">
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon teal">
                                                    <i class="bi bi-grid-3x3"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Sections</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($sections_count) ?></h6>
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
                                                <div class="stats-icon indigo">
                                                    <i class="bi bi-book"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Subjects</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($subjects_count) ?></h6>
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
                                                <div class="stats-icon pink">
                                                    <i class="bi bi-clipboard-check"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Assignments</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($assignments_count) ?></h6>
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
                                                    <i class="bi bi-people-fill"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Parents</h6>
                                                <h6 class="font-extrabold mb-0"><?= number_format($parents_count) ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>User Registrations - Last 7 Days</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="userRegistrationChart" width="400" height="200"></canvas>
                                        <p class="mt-3 text-muted">Showing new user registrations over the past week.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Recent Activity Overview</h4>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="activityChart" width="400" height="200"></canvas>
                                        <p class="mt-3 text-muted">Activity summary for the past 7 days.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>System Overview</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-warning">Avg. Class Size</h5>
                                                    <h3 class="font-extrabold mb-0">
                                                        <?php
                                                        $avg_class_size = 0;
                                                        if ($classes_count > 0) {
                                                            $stmt = $pdo->query('SELECT AVG(student_count) as avg_size FROM (SELECT class_id, COUNT(student_id) as student_count FROM student_classes GROUP BY class_id) as class_sizes');
                                                            $avg_class_size = $stmt->fetchColumn();
                                                            $avg_class_size = number_format($avg_class_size, 1);
                                                        }
                                                        echo $avg_class_size;
                                                        ?>
                                                    </h3>
                                                    <p class="text-muted">Students per class</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-info">Capacity Usage</h5>
                                                    <h3 class="font-extrabold mb-0"><?= $capacity_utilization ?>%</h3>
                                                    <p class="text-muted">Section capacity used</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-success">Today's Attendance</h5>
                                                    <h3 class="font-extrabold mb-0"><?= number_format($attendance_today_count) ?></h3>
                                                    <p class="text-muted">Students present</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-primary">Total Grades</h5>
                                                    <h3 class="font-extrabold mb-0"><?= number_format($grades_count) ?></h3>
                                                    <p class="text-muted">Grade records</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-purple">Avg. Registration</h5>
                                                    <h3 class="font-extrabold mb-0">
                                                        <?php
                                                        $avg_reg = $recent_users_count > 0 ? number_format($recent_users_count / 7, 1) : 0;
                                                        echo $avg_reg;
                                                        ?>
                                                    </h3>
                                                    <p class="text-muted">Per day (last 7 days)</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-orange">Library Books</h5>
                                                    <h3 class="font-extrabold mb-0"><?= number_format($library_count) ?></h3>
                                                    <p class="text-muted">Total books</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-teal">Upcoming Events</h5>
                                                    <h3 class="font-extrabold mb-0">
                                                        <?php
                                                        $events_upcoming = $pdo->query('SELECT COUNT(*) FROM calendar_events WHERE start_date >= CURDATE()')->fetchColumn();
                                                        echo number_format($events_upcoming);
                                                        ?>
                                                    </h3>
                                                    <p class="text-muted">Scheduled</p>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="stat-card p-3">
                                                    <h5 class="text-indigo">Registrars</h5>
                                                    <h3 class="font-extrabold mb-0"><?= number_format($registrars_count) ?></h3>
                                                    <p class="text-muted">Total registrars</p>
                                                </div>
                                            </div>
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
                                        <img src="../dashboardassets/images/faces/1.jpg" alt="Admin">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold"><?=$username?></h5>
                                        <h6 class="text-muted mb-0">Admin</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>Recent Activity</h4>
                            </div>
                            <div class="card-content pb-4">
                                <?php if (!empty($recent_activities)): ?>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <div class="recent-message d-flex px-4 py-3 border-bottom">
                                            <div class="avatar bg-<?= $activity['color'] ?>-light">
                                                <i class="bi bi-<?= $activity['icon'] ?> text-<?= $activity['color'] ?> mx-auto"></i>
                                            </div>
                                            <div class="name ms-4">
                                                <h5 class="mb-1"><?= htmlspecialchars($activity['activity']) ?></h5>
                                                <p class="text-muted mb-0"><?= htmlspecialchars($activity['details']) ?></p>
                                                <small class="text-muted"><?= date('M j, g:i A', strtotime($activity['time'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="px-4 py-3">
                                        <div class="name text-center">
                                            <i class="bi bi-activity text-muted" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 text-muted">No recent activity</h5>
                                            <p class="text-muted">No recent activity to display</p>
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

    <!-- Load Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare chart data from PHP variables
            const chartLabels = <?php echo $chart_labels_json; ?>;
            const chartData = <?php echo $chart_data_json; ?>;

            // Get activity data from PHP variables
            const activityLabels = <?php echo $activity_labels_json; ?>;
            const activityData = <?php echo $activity_data_json; ?>;

            // User Registration Chart
            if (document.getElementById('userRegistrationChart')) {
                const userCtx = document.getElementById('userRegistrationChart').getContext('2d');

                new Chart(userCtx, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: 'New Users',
                            data: chartData,
                            borderColor: '#435ebe',
                            backgroundColor: 'rgba(67, 94, 190, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'User Registrations - Last 7 Days'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Activity Chart
            if (document.getElementById('activityChart')) {
                const activityCtx = document.getElementById('activityChart').getContext('2d');

                new Chart(activityCtx, {
                    type: 'doughnut',
                    data: {
                        labels: activityLabels,
                        datasets: [{
                            data: activityData,
                            backgroundColor: [
                                '#435ebe', // blue
                                '#ffa726', // orange
                                '#26c6da', // teal
                                '#ec407a'  // pink
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Recent Activity (Last 7 Days)'
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

        });
    </script>
</body>

</html>
