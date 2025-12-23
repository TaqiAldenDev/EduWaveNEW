<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Registrar User';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['student_id']) && !empty($_POST['new_class_id'])) {
        $student_id = $_POST['student_id'];
        $new_class_id = $_POST['new_class_id'];
        $new_section_id = !empty($_POST['new_section_id']) ? $_POST['new_section_id'] : null;
        
        try {
            // Check section capacity if specified
            if ($new_section_id) {
                $capacity_stmt = $pdo->prepare('SELECT s.max_students, COALESCE(COUNT(sc.student_id), 0) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.id = ? GROUP BY s.id');
                $capacity_stmt->execute([$new_section_id]);
                $capacity = $capacity_stmt->fetch();
                
                if ($capacity && $capacity['enrolled'] >= $capacity['max_students']) {
                    $message = 'Selected section is already at maximum capacity.';
                } else {
                    // Check if student has existing class assignment
                    $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM student_classes WHERE student_id = ?');
                    $check_stmt->execute([$student_id]);
                    
                    if ($check_stmt->fetchColumn() > 0) {
                        // Update existing assignment
                        $stmt = $pdo->prepare('UPDATE student_classes SET class_id = ?, section_id = ?, academic_year = ? WHERE student_id = ?');
                        $stmt->execute([$new_class_id, $new_section_id, date('Y'), $student_id]);
                    } else {
                        // Insert new assignment
                        $stmt = $pdo->prepare('INSERT INTO student_classes (student_id, class_id, section_id, academic_year) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$student_id, $new_class_id, $new_section_id, date('Y')]);
                    }
                    $message = 'Student transferred successfully!';
                }
            } else {
                // Only transfer to class, no section specified
                $stmt = $pdo->prepare('UPDATE student_classes SET class_id = ?, section_id = NULL WHERE student_id = ?');
                $stmt->execute([$new_class_id, $student_id]);
                $message = 'Student transferred successfully!';
            }
        } catch (PDOException $e) {
            $message = 'Error transferring student.';
        }
    }
}

$students_stmt = $pdo->query('SELECT u.id, u.name, c.grade_name as current_class, s.section_name as current_section FROM users u LEFT JOIN student_classes sc ON u.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id LEFT JOIN sections s ON sc.section_id = s.id WHERE u.role = "Student" ORDER BY u.name');
$students = $students_stmt->fetchAll();

$classes_stmt = $pdo->query('SELECT * FROM classes');
$classes = $classes_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Student - EduWave Registrar</title>

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
                                <i class="bi bi-person-plus"></i>
                                <span>Student Enrollment</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="student_enrol.php">Enroll Student</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="student_management.php">Manage Students</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="parent_management.php" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>Parent Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item active">
                            <a href="student_transfer.php" class='sidebar-link'>
                                <i class="bi bi-exchange"></i>
                                <span>Student Transfer</span>
                            </a>
                        </li>

                        <li class="sidebar-item">
                            <a href="certificate_issue.php" class='sidebar-link'>
                                <i class="bi bi-award"></i>
                                <span>Certificate Issuing</span>
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
                <h3>Student Transfer</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Transfer Student</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student</label>
                                        <select class="form-select" id="student_id" name="student_id" required>
                                            <option value="">Select Student</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?= $student['id'] ?>">
                                                    <?= htmlspecialchars($student['name']) ?>
                                                    <?php if ($student['current_class']): ?>
                                                        (Current: <?= htmlspecialchars($student['current_class']) ?><?= $student['current_section'] ? ' - ' . htmlspecialchars($student['current_section']) : '' ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_class_id" class="form-label">New Class</label>
                                        <select class="form-select" id="new_class_id" name="new_class_id" required onchange="loadSections(this.value)">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_section_id" class="form-label">New Section (Optional)</label>
                                        <select class="form-select" id="new_section_id" name="new_section_id">
                                            <option value="">Select Class First</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Transfer Student</button>
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
    
    <script>
        function loadSections(classId) {
            const sectionSelect = document.getElementById('new_section_id');
            
            // Clear existing options
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            
            if (classId) {
                fetch(`get_sections.php?class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            sectionSelect.innerHTML = '<option value="">No sections available</option>';
                        } else {
                            data.forEach(section => {
                                const option = document.createElement('option');
                                option.value = section.id;
                                option.textContent = `${section.section_name} (${section.enrolled}/${section.max_students} students)`;
                                if (section.enrolled >= section.max_students) {
                                    option.disabled = true;
                                    option.textContent += ' - FULL';
                                }
                                sectionSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading sections:', error);
                        sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                    });
            }
        }
    </script>
</body>

</html>
