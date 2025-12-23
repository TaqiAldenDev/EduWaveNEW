<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher User';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['title']) && !empty($_POST['description']) && !empty($_POST['due_date']) && !empty($_POST['assignment'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        list($class_id, $subject_id) = explode('-', $_POST['assignment']);

        try {
            $stmt = $pdo->prepare('INSERT INTO assignments (teacher_id, subject_id, class_id, title, description, due_date) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $subject_id, $class_id, $title, $description, $due_date]);

            // Get all students in the class to notify them
            $stmt = $pdo->prepare('SELECT student_id FROM student_classes WHERE class_id = ?');
            $stmt->execute([$class_id]);
            $students = $stmt->fetchAll();

            /* foreach ($students as $student) {
                //create_notification($student['student_id'], "New homework added: $title");
            } */

            $message = 'Homework added successfully!';
        } catch (PDOException $e) {
            $message = 'Error adding homework.';
        }
    } else {
        $message = 'Please fill in all required fields.';
    }
}

// Get teacher's assigned classes and subjects
try {
    $stmt = $pdo->prepare('SELECT DISTINCT classes.id as class_id, classes.grade_name, subjects.id as subject_id, subjects.name AS subject_name FROM teacher_assignments JOIN classes ON teacher_assignments.class_id = classes.id JOIN subjects ON teacher_assignments.subject_id = subjects.id WHERE teacher_assignments.teacher_id = ? ORDER BY classes.grade_name, subjects.name');
    $stmt->execute([$_SESSION['user_id']]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $assignments = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['title']) && !empty($_POST['description']) && !empty($_POST['due_date']) && !empty($_POST['assignment'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        list($class_id, $subject_id) = explode('-', $_POST['assignment']);

        try {
            $stmt = $pdo->prepare('INSERT INTO assignments (teacher_id, subject_id, class_id, title, description, due_date) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$_SESSION['user_id'], $subject_id, $class_id, $title, $description, $due_date]);

            // Notify all students in the class
            $stmt = $pdo->prepare('SELECT student_id FROM student_classes WHERE class_id = ?');
            $stmt->execute([$class_id]);
            $students = $stmt->fetchAll();

            foreach ($students as $student) {
                // Assuming create_notification function exists elsewhere
                // create_notification($student['student_id'], "New homework added: $title");
            }

            $message = 'Homework added successfully!';
        } catch (PDOException $e) {
            $message = 'Error adding homework.';
        }
    } else {
        $message = 'Please fill in all required fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Homework - EduWave Teacher</title>

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

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Homework</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
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
                <h3>Add Homework</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Homework Assignment Form</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="assignment" class="form-label">Class and Subject</label>
                                        <select class="form-select" id="assignment" name="assignment" required>
                                            <option value="">Select Class and Subject</option>
                                            <?php foreach ($assignments as $assignment): ?>
                                                <option value="<?= $assignment['class_id'] ?>-<?= $assignment['subject_id'] ?>"><?= htmlspecialchars($assignment['grade_name'] . ' - ' . $assignment['subject_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="date" class="form-control" id="due_date" name="due_date" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Homework</button>
                                </form>
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
