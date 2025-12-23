<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Registrar User';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_parent'])) {
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = 'Parent';
        $color_theme = 'yellow';
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, color_theme) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $password_hash, $role, $color_theme]);
            $message = 'Parent added successfully!';
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = 'Email already exists.';
            } else {
                $message = 'Error adding parent.';
            }
        }
    }
}

// Handle parent-student linking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_student'])) {
    $parent_id = $_POST['parent_id'];
    $student_id = $_POST['student_id'];
    $relation = $_POST['relation'];
    
    try {
        // Validate that parent and student exist
        $parent_check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = ? AND role = "Parent"');
        $parent_check->execute([$parent_id]);
        if ($parent_check->fetchColumn() == 0) {
            $message = 'Invalid parent selected.';
        } else {
            $student_check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE id = ? AND role = "Student"');
            $student_check->execute([$student_id]);
            if ($student_check->fetchColumn() == 0) {
                $message = 'Invalid student selected.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO parent_student (parent_id, student_id, relation) VALUES (?, ?, ?)');
                $stmt->execute([$parent_id, $student_id, $relation]);
                $message = 'Student linked to parent successfully!';
            }
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $message = 'This student is already linked to this parent.';
        } else {
            $message = 'Error linking student: ' . $e->getMessage();
        }
    }
}

// Handle unlinking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlink_student'])) {
    $link_id = $_POST['link_id'];
    try {
        $stmt = $pdo->prepare('DELETE FROM parent_student WHERE id = ?');
        $stmt->execute([$link_id]);
        $message = 'Student unlinked from parent successfully!';
    } catch (PDOException $e) {
        $message = 'Error unlinking student.';
    }
}

// Get parents with additional info
$parents_stmt = $pdo->query('SELECT id, name, email, created_at FROM users WHERE role = "Parent" ORDER BY name');
$parents = $parents_stmt->fetchAll();

// Get students for dropdown with class info (including unenrolled students)
$students_stmt = $pdo->query('SELECT u.id, u.name, c.grade_name, sc.class_id FROM users u LEFT JOIN student_classes sc ON u.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id WHERE u.role = "Student" ORDER BY u.name');
$students = $students_stmt->fetchAll();

// Get parent-student relationships with detailed info
$relationships_stmt = $pdo->query('SELECT ps.id as link_id, ps.parent_id, ps.student_id, ps.relation, p.name as parent_name, p.email as parent_email, s.name as student_name, c.grade_name as class_name FROM parent_student ps JOIN users p ON ps.parent_id = p.id JOIN users s ON ps.student_id = s.id LEFT JOIN student_classes sc ON s.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id ORDER BY p.name, s.name');
$relationships = $relationships_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Management - EduWave Registrar</title>

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

                        <li class="sidebar-item active">
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
                <h3>Parent Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Add New Parent</h4>
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
                                    <button type="submit" name="add_parent" class="btn btn-primary">Add Parent</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Link Student to Parent</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Parent</label>
                                        <select class="form-select" id="parent_id" name="parent_id" required>
                                            <?php foreach ($parents as $parent): ?>
                                                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?> (<?= htmlspecialchars($parent['email']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student</label>
                                        <select class="form-select" id="student_id" name="student_id" required>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?><?= $student['grade_name'] ? ' (' . htmlspecialchars($student['grade_name']) . ')' : ' (Not enrolled)' ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="relation" class="form-label">Relationship</label>
                                        <select class="form-select" id="relation" name="relation" required>
                                            <option value="Father">Father</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Guardian">Guardian</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="link_student" class="btn btn-primary">Link Student</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Existing Parents and Their Children</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="parentSearch" placeholder="Search parents..." onkeyup="filterParents()">
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select" id="classFilter" onchange="filterParents()">
                                            <option value="">All Classes</option>
                                            <?php
                                            $classes_filter = $pdo->query('SELECT DISTINCT c.id, c.grade_name FROM classes c LEFT JOIN student_classes sc ON c.id = sc.class_id ORDER BY c.grade_name')->fetchAll();
                                            foreach ($classes_filter as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="parentsTable">
                                        <thead>
                                            <tr>
                                                <th>Parent Name</th>
                                                <th>Email</th>
                                                <th>Registration Date</th>
                                                <th>Linked Students</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($parents as $parent): 
                                                $children_stmt = $pdo->prepare('SELECT ps.id as link_id, u.name as student_name, c.grade_name, ps.relation FROM parent_student ps JOIN users u ON ps.student_id = u.id LEFT JOIN student_classes sc ON u.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id WHERE ps.parent_id = ? ORDER BY u.name');
                                                $children_stmt->execute([$parent['id']]);
                                                $children = $children_stmt->fetchAll();
                                            ?>
                                                <tr data-parent-name="<?= strtolower(htmlspecialchars($parent['name'])) ?>" data-class-ids="<?= htmlspecialchars(implode(',', array_unique(array_column($children, 'class_id')))) ?>">
                                                    <td>
                                                        <strong><?= htmlspecialchars($parent['name']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($parent['email']) ?></td>
                                                    <td><?= date('M j, Y', strtotime($parent['created_at'])) ?></td>
                                                    <td>
                                                        <?php if (empty($children)): ?>
                                                            <span class="badge bg-secondary">No students</span>
                                                        <?php else: ?>
                                                            <?php foreach ($children as $child): ?>
                                                                <div class="mb-1">
                                                                    <span class="badge bg-primary me-2">
                                                                        <?= htmlspecialchars($child['student_name']) ?> 
                                                                        <small>(<?= htmlspecialchars($child['grade_name'] ?? 'Not enrolled') ?>)</small>
                                                                    </span>
                                                                    <small class="text-muted"><?= htmlspecialchars($child['relation']) ?></small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewParentDetails(<?= $parent['id'] ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </td>
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

    <!-- Parent Details Modal -->
    <div class="modal fade" id="parentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Parent Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Parent Information</h6>
                            <div id="parentInfo">
                                <p class="text-muted">Loading...</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Linked Children</h6>
                            <div id="childrenInfo">
                                <p class="text-muted">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../dashboardassets/js/bootstrap.bundle.min.js"></script>
    <script src="../dashboardassets/js/main.js"></script>
    
    <script>
        function filterParents() {
            const searchTerm = document.getElementById('parentSearch').value.toLowerCase();
            const classFilter = document.getElementById('classFilter').value;
            const rows = document.querySelectorAll('#parentsTable tbody tr');
            
            rows.forEach(row => {
                const parentName = row.getAttribute('data-parent-name');
                const classIds = row.getAttribute('data-class-ids');
                const matchesSearch = parentName.includes(searchTerm);
                const matchesClass = !classFilter || (classIds && classIds.split(',').includes(classFilter));
                
                row.style.display = matchesSearch && matchesClass ? '' : 'none';
            });
        }
        
        function viewParentDetails(parentId) {
            // Fetch parent details via AJAX
            fetch(`get_parent_details.php?parent_id=${parentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    // Update parent info
                    const parentInfo = document.getElementById('parentInfo');
                    parentInfo.innerHTML = `
                        <p><strong>Name:</strong> ${data.parent.name}</p>
                        <p><strong>Email:</strong> ${data.parent.email}</p>
                        <p><strong>Registered:</strong> ${new Date(data.parent.created_at).toLocaleDateString()}</p>
                    `;
                    
                    // Update children info
                    const childrenInfo = document.getElementById('childrenInfo');
                    if (data.children.length === 0) {
                        childrenInfo.innerHTML = '<p class="text-muted">No children linked</p>';
                    } else {
                        const childrenHtml = data.children.map(child => `
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${child.student_name}</strong>
                                        <br>
                                        <small class="text-muted">${child.grade_name || 'Not enrolled'} - ${child.relation}</small>
                                    </div>
                                    <span class="badge bg-success">Linked</span>
                                </div>
                            </div>
                        `).join('');
                        childrenInfo.innerHTML = childrenHtml;
                    }
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('parentDetailsModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading parent details');
                });
        }
    </script>
</body>

</html>