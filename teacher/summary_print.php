<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../includes/config.php';
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher User';

if (isset($_GET['print']) && $_GET['print'] === 'pdf') {
    require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';

    $class_id = $_GET['class_id'];
    $subject_id = $_GET['subject_id'];

    $grades_stmt = $pdo->prepare('
        SELECT
            users.name,
            grades.exam_type,
            grades.score,
            grades.date_given
        FROM grades
        JOIN users ON grades.student_id = users.id
        WHERE grades.subject_id = ? AND users.id IN (SELECT student_id FROM student_classes WHERE class_id = ?)
        ORDER BY grades.date_given DESC
    ');
    $grades_stmt->execute([$subject_id, $class_id]);
    $grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);

    $attendance_stmt = $pdo->prepare('
        SELECT
            users.name,
            attendance.attend_date,
            attendance.status
        FROM attendance
        JOIN users ON attendance.student_id = users.id
        WHERE attendance.subject_id = ? AND attendance.class_id = ?
        ORDER BY attendance.attend_date DESC
    ');
    $attendance_stmt->execute([$subject_id, $class_id]);
    $attendance = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('EduWave');
    $pdf->SetTitle('Class Summary');
    $pdf->AddPage();

    // Get class and subject names for the header
    $class_stmt = $pdo->prepare('SELECT grade_name FROM classes WHERE id = ?');
    $class_stmt->execute([$class_id]);
    $class_name = $class_stmt->fetchColumn();

    $subject_stmt = $pdo->prepare('SELECT name FROM subjects WHERE id = ?');
    $subject_stmt->execute([$subject_id]);
    $subject_name = $subject_stmt->fetchColumn();

    $html = '<h1>Class Summary - ' . htmlspecialchars($class_name . ' - ' . $subject_name) . '</h1>';

    if (!empty($grades)) {
        $html .= '<h2>Grades</h2><table border="1" cellpadding="4" style="width: 100%;"><thead><tr><th>Student Name</th><th>Exam Type</th><th>Score</th><th>Date</th></tr></thead><tbody>';
        foreach ($grades as $grade) {
            $html .= '<tr><td>' . htmlspecialchars($grade['name']) . '</td><td>' . htmlspecialchars($grade['exam_type']) . '</td><td>' . htmlspecialchars($grade['score']) . '</td><td>' . htmlspecialchars($grade['date_given']) . '</td></tr>';
        }
        $html .= '</tbody></table>';
    }

    if (!empty($attendance)) {
        $html .= '<h2>Attendance</h2><table border="1" cellpadding="4" style="width: 100%;"><thead><tr><th>Student Name</th><th>Date</th><th>Status</th></tr></thead><tbody>';
        foreach ($attendance as $record) {
            $html .= '<tr><td>' . htmlspecialchars($record['name']) . '</td><td>' . htmlspecialchars($record['attend_date']) . '</td><td>' . htmlspecialchars($record['status']) . '</td></tr>';
        }
        $html .= '</tbody></table>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('summary.pdf', 'I');
    exit();
}

// Get teacher's assigned classes and subjects
try {
    $stmt = $pdo->prepare('SELECT DISTINCT classes.id as class_id, classes.grade_name, subjects.id as subject_id, subjects.name AS subject_name FROM teacher_assignments JOIN classes ON teacher_assignments.class_id = classes.id JOIN subjects ON teacher_assignments.subject_id = subjects.id WHERE teacher_assignments.teacher_id = ? ORDER BY classes.grade_name, subjects.name');
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assignments = [];
}

$grades = [];
$attendance = [];
$selected_class_id = null;
$selected_subject_id = null;

// Handle GET request to load data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['assignment'])) {
    $assignment_parts = explode('-', $_GET['assignment']);
    if (count($assignment_parts) === 2) {
        $selected_class_id = $assignment_parts[0];
        $selected_subject_id = $assignment_parts[1];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['class_id']) && isset($_GET['subject_id'])) {
    $selected_class_id = $_GET['class_id'];
    $selected_subject_id = $_GET['subject_id'];
}

if ($selected_class_id && $selected_subject_id) {
    try {
        // Get grades for this class and subject
        $grades_stmt = $pdo->prepare('
            SELECT
                users.name,
                grades.exam_type,
                grades.score,
                grades.date_given
            FROM grades
            JOIN users ON grades.student_id = users.id
            WHERE grades.subject_id = ? AND users.id IN (SELECT student_id FROM student_classes WHERE class_id = ?)
            ORDER BY grades.date_given DESC
        ');
        $grades_stmt->execute([$selected_subject_id, $selected_class_id]);
        $grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $grades = [];
    }

    try {
        // Get attendance for this class and subject
        $attendance_stmt = $pdo->prepare('
            SELECT
                users.name,
                attendance.attend_date,
                attendance.status
            FROM attendance
            JOIN users ON attendance.student_id = users.id
            WHERE attendance.subject_id = ? AND attendance.class_id = ?
            ORDER BY attendance.attend_date DESC
        ');
        $attendance_stmt->execute([$selected_subject_id, $selected_class_id]);
        $attendance = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $attendance = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Summary - EduWave Teacher</title>

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
                <h3>Class Summary</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Summary Report</h4>
                            </div>
                            <div class="card-body">
                                <form method="GET">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="assignment" class="form-label">Class and Subject</label>
                                            <select class="form-select" id="assignment" name="assignment">
                                                <option value="">Select Class and Subject</option>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <option value="<?= $assignment['class_id'] ?>-<?= $assignment['subject_id'] ?>" <?= ($selected_class_id == $assignment['class_id'] && $selected_subject_id == $assignment['subject_id']) ? 'selected' : '' ?>><?= htmlspecialchars($assignment['grade_name'] . ' - ' . $assignment['subject_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">&nbsp;</label>
                                            <?php if ($selected_class_id && $selected_subject_id): ?>
                                                <a href="?<?= http_build_query(array_merge($_GET, ['print' => 'pdf', 'class_id' => $selected_class_id, 'subject_id' => $selected_subject_id])) ?>" class="btn btn-primary form-control">Print to PDF</a>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-primary form-control" disabled>Print to PDF</button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-outline-secondary form-control">Load Data</button>
                                        </div>
                                    </div>
                                </form>

                                <?php if (!empty($grades)): ?>
                                    <div class="table-responsive">
                                        <h5 class="mt-4">Grades</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Exam Type</th>
                                                    <th>Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($grades as $grade): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($grade['name']) ?></td>
                                                        <td><?= htmlspecialchars($grade['exam_type']) ?></td>
                                                        <td><?= htmlspecialchars($grade['score']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($attendance)): ?>
                                    <div class="table-responsive">
                                        <h5 class="mt-4">Attendance</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendance as $record): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($record['name']) ?></td>
                                                        <td><?= htmlspecialchars($record['attend_date']) ?></td>
                                                        <td><?= htmlspecialchars($record['status']) ?></td>
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

    <script>
        document.getElementById('assignment').addEventListener('change', function() {
            var value = this.value;
            if (value) {
                var parts = value.split('-');
                var class_id = parts[0];
                var subject_id = parts[1];
                window.location.href = 'summary_print.php?assignment=' + value;
            }
        });
    </script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>
