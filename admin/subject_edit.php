<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

if (!isset($_GET['id'])) {
    header('Location: subjects.php');
    exit();
}

$subject_id = $_GET['id'];
$message = '';

// Get all classes for the dropdown
$classes_stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
$classes = $classes_stmt->fetchAll();

// Get subject details with class name
$stmt = $pdo->prepare('
    SELECT s.*, c.grade_name as class_name
    FROM subjects s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.id = ?
');
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

// Get related information
$teachers_stmt = $pdo->prepare('
    SELECT u.name, u.id
    FROM users u
    JOIN teacher_assignments ta ON u.id = ta.teacher_id
    WHERE ta.subject_id = ?
');
$teachers_stmt->execute([$subject_id]);
$assigned_teachers = $teachers_stmt->fetchAll();

$students_stmt = $pdo->prepare('
    SELECT COUNT(sc.student_id) as student_count
    FROM student_classes sc
    JOIN classes c ON sc.class_id = c.id
    JOIN subjects s ON c.id = s.class_id
    WHERE s.id = ?
');
$students_stmt->execute([$subject_id]);
$student_count = $students_stmt->fetchColumn();

$grade_stats_stmt = $pdo->prepare('
    SELECT
        COUNT(*) as total_grades,
        AVG(score) as avg_score
    FROM grades
    WHERE subject_id = ?
');
$grade_stats_stmt->execute([$subject_id]);
$grade_stats = $grade_stats_stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['name']) && !empty($_POST['class_id'])) {
        $name = $_POST['name'];
        $class_id = $_POST['class_id'];

        try {
            $stmt = $pdo->prepare('UPDATE subjects SET name = ?, class_id = ? WHERE id = ?');
            $stmt->execute([$name, $class_id, $subject_id]);
            $message = 'Subject updated successfully!';

            $stmt = $pdo->prepare('SELECT * FROM subjects WHERE id = ?');
            $stmt->execute([$subject_id]);
            $subject = $stmt->fetch();

        } catch (PDOException $e) {
            $message = 'Error updating subject.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - EduWave Admin</title>

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

                        <li class="sidebar-item">
                            <a href="users_list.php" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>User Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>Classes & Subjects</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="classes.php">Classes</a>
                                </li>
                                <li class="submenu-item active">
                                    <a href="subjects.php">Subjects</a>
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
                <h3>Classes & Subjects</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Edit Subject</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-info' ?>"><?= $message ?></div>
                                    <?php if (strpos($message, 'successfully') !== false): ?>
                                        <script>
                                            // If this page is loaded in a modal and operation was successful, close modal and refresh parent
                                            if (window.parent !== window && typeof window.parent.closeModal === 'function') {
                                                setTimeout(function() {
                                                    window.parent.closeModal();
                                                    if (typeof window.parent.location.reload === 'function') {
                                                        window.parent.location.reload();
                                                    }
                                                }, 1500); // Wait 1.5 seconds to show success message before closing
                                            }
                                        </script>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="name" class="form-label">Subject Name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($subject['name']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="class_id" class="form-label">Class</label>
                                            <select class="form-select" id="class_id" name="class_id" required>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>" <?= ($subject['class_id'] == $class['id']) ? 'selected' : '' ?>><?= htmlspecialchars($class['grade_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Subject</button>
                                </form>

                                <!-- Subject Statistics -->
                                <hr class="my-4">
                                <h5>Subject Information</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-light p-3 text-center">
                                            <h6 class="text-muted">Assigned Teachers</h6>
                                            <h4 class="font-extrabold"><?= count($assigned_teachers) ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light p-3 text-center">
                                            <h6 class="text-muted">Students Enrolled</h6>
                                            <h4 class="font-extrabold"><?= $student_count ?: 0 ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light p-3 text-center">
                                            <h6 class="text-muted">Total Grades</h6>
                                            <h4 class="font-extrabold"><?= $grade_stats['total_grades'] ?: 0 ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light p-3 text-center">
                                            <h6 class="text-muted">Average Score</h6>
                                            <h4 class="font-extrabold"><?= $grade_stats['avg_score'] ? number_format($grade_stats['avg_score'], 1) : 'N/A' ?></h4>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assigned Teachers -->
                                <?php if ($assigned_teachers): ?>
                                <h5 class="mt-4">Assigned Teachers</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Teacher Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assigned_teachers as $teacher): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($teacher['name']) ?></td>
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
</body>

</html>
