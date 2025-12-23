<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student User';

$student_id = $_SESSION['user_id'];
$message = '';

$stmt = $pdo->prepare('SELECT assignments.*, subjects.name as subject_name FROM assignments JOIN subjects ON assignments.subject_id = subjects.id WHERE assignments.class_id = (SELECT class_id FROM student_classes WHERE student_id = ? AND academic_year = ?) ORDER BY assignments.due_date ASC');
$stmt->execute([$student_id, date('Y')]);
$assignments = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['assignment_id']) && !empty($_FILES['submission_file'])) {
        $assignment_id = $_POST['assignment_id'];
        $file = $_FILES['submission_file'];

        // File upload handling
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/homework/';
            $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-rar-compressed', 'image/jpeg', 'image/png'];
            $max_size = 10 * 1024 * 1024; // 10 MB

            if ($file['size'] > $max_size) {
                $message = 'File is too large. Max size is 10 MB.';
            } elseif (!in_array($file['type'], $allowed_types)) {
                $message = 'Invalid file type. Allowed types: PDF, DOCX, ZIP, RAR, JPG, PNG.';
            } else {
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid('homework_', true) . '.' . $file_extension;
                $file_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    try {
                        $stmt = $pdo->prepare('INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)');
                        $stmt->execute([$assignment_id, $student_id, 'uploads/homework/' . $new_filename]);
                        $message = 'Homework submitted successfully!';
                    } catch (PDOException $e) {
                        $message = 'Error submitting homework.';
                    }
                } else {
                    $message = 'Error uploading file.';
                }
            }
        } else {
            $message = 'Error uploading file.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Homework - EduWave Student</title>

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

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Homework</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
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
                <h3>Upload Homework</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Homework Submissions</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignments as $assignment): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                                                    <td><?= htmlspecialchars($assignment['title']) ?></td>
                                                    <td><?= htmlspecialchars($assignment['description']) ?></td>
                                                    <td><?= htmlspecialchars($assignment['due_date']) ?></td>
                                                    <td>
                                                        <?php
                                                        $due_date = new DateTime($assignment['due_date']);
                                                        $current_date = new DateTime();
                                                        if ($due_date < $current_date) {
                                                            echo '<span class="badge bg-danger">Overdue</span>';
                                                        } else {
                                                            echo '<span class="badge bg-success">Active</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <form method="POST" enctype="multipart/form-data" class="d-flex">
                                                            <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                                            <input type="file" class="form-control me-2" name="submission_file" required>
                                                            <button type="submit" class="btn btn-primary">Upload</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($assignments)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No homework assignments available</td>
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
