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
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['class_id'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $class_id = $_POST['class_id'];
        $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : null;
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $academic_year = date('Y');
        $role = 'Student';
        $color_theme = 'purple';
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            // Check section capacity if specified
            if ($section_id) {
                $capacity_stmt = $pdo->prepare('SELECT s.max_students, COALESCE(COUNT(sc.student_id), 0) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.id = ? GROUP BY s.id');
                $capacity_stmt->execute([$section_id]);
                $capacity = $capacity_stmt->fetch();
                
                if ($capacity && $capacity['enrolled'] >= $capacity['max_students']) {
                    throw new Exception('Selected section is already at maximum capacity.');
                }
            }

            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, color_theme) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $password_hash, $role, $color_theme]);
            $student_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO student_classes (student_id, class_id, section_id, academic_year) VALUES (?, ?, ?, ?)');
            $stmt->execute([$student_id, $class_id, $section_id, $academic_year]);

            // Link parent if specified
            if ($parent_id) {
                $relation = $_POST['parent_relation'] ?? 'Guardian';
                $stmt = $pdo->prepare('INSERT INTO parent_student (parent_id, student_id, relation) VALUES (?, ?, ?)');
                $stmt->execute([$parent_id, $student_id, $relation]);
            }

            $pdo->commit();
            $message = 'Student enrolled successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $message = 'Email already exists.';
            } else {
                $message = 'Error enrolling student.';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = $e->getMessage();
        }
    }
}

$classes_stmt = $pdo->query('SELECT * FROM classes');
$classes = $classes_stmt->fetchAll();

// Get parents for dropdown
$parents_stmt = $pdo->query('SELECT id, name FROM users WHERE role = "Parent" ORDER BY name');
$parents = $parents_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrol Student - EduWave Registrar</title>

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
                                <i class="bi bi-person-plus"></i>
                                <span>Student Enrollment</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
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

                        <li class="sidebar-item">
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
                <h3>Student Enrollment</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Enroll New Student</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class</label>
                                        <select class="form-select" id="class_id" name="class_id" required onchange="loadSections(this.value)">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="section_id" class="form-label">Section (Optional)</label>
                                        <select class="form-select" id="section_id" name="section_id">
                                            <option value="">Select Class First</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Parent/Guardian (Optional)</label>
                                        <select class="form-select" id="parent_id" name="parent_id" onchange="toggleParentRelation()">
                                            <option value="">No Parent</option>
                                            <?php foreach ($parents as $parent): ?>
                                                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3" id="parent_relation_div" style="display: none;">
                                        <label for="parent_relation" class="form-label">Relationship</label>
                                        <select class="form-select" id="parent_relation" name="parent_relation">
                                            <option value="Father">Father</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Guardian">Guardian</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Enrol Student</button>
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
            const sectionSelect = document.getElementById('section_id');
            
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
            } else {
                sectionSelect.innerHTML = '<option value="">Select Class First</option>';
            }
        }
        
        function toggleParentRelation() {
            const parentSelect = document.getElementById('parent_id');
            const relationDiv = document.getElementById('parent_relation_div');
            
            if (parentSelect.value) {
                relationDiv.style.display = 'block';
            } else {
                relationDiv.style.display = 'none';
            }
        }
    </script>
</body>

</html>
