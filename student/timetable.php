<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student User';

$student_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT schedule.*, subjects.name as subject_name, users.name as teacher_name FROM schedule JOIN subjects ON schedule.subject_id = subjects.id JOIN users ON schedule.teacher_id = users.id WHERE schedule.class_id = (SELECT class_id FROM student_classes WHERE student_id = ?)');
$stmt->execute([$student_id]);
$schedule = $stmt->fetchAll();

$timetable = [];
foreach ($schedule as $item) {
    $timetable[$item['day_of_week']][] = $item;
}

$days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$times = ['09:00' => '9:00 AM', '10:00' => '10:00 AM', '11:00' => '11:00 AM', '12:00' => '12:00 PM', '13:00' => '1:00 PM', '14:00' => '2:00 PM'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - EduWave Student</title>

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
                                <i class="bi bi-calendar-check"></i>
                                <span>Timetable</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
                                    <a href="timetable.php">My Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-graph-up"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
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
                <h3>My Timetable</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Class Schedule</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <?php foreach ($days as $day): ?>
                                                    <th><?= $day ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($times as $time_code => $time_display): ?>
                                                <tr>
                                                    <td><?= $time_display ?></td>
                                                    <?php foreach ($days as $day): ?>
                                                        <td>
                                                            <?php
                                                            if (isset($timetable[$day])): ?>
                                                                <?php foreach ($timetable[$day] as $item): ?>
                                                                    <?php if (substr($item['start_time'], 0, 5) == $time_code): ?>
                                                                        <div class="border p-2 mb-1 bg-light rounded">
                                                                            <strong><?= htmlspecialchars($item['subject_name']) ?></strong><br>
                                                                            <small class="text-muted"><?= htmlspecialchars($item['teacher_name']) ?></small><br>
                                                                            <small class="text-muted"><?= date('g:i A', strtotime($item['start_time'])) ?> - <?= date('g:i A', strtotime($item['end_time'])) ?></small>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
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
