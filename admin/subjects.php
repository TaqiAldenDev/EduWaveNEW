<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

// Handle teacher assignment
$assignment_message = '';
$subject_message = '';

// Handle subject editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_subject'])) {
    if (!empty($_POST['subject_id']) && !empty($_POST['subject_name']) && !empty($_POST['class_id'])) {
        $subject_id = $_POST['subject_id'];
        $subject_name = $_POST['subject_name'];
        $class_id = $_POST['class_id'];

        try {
            $stmt = $pdo->prepare('UPDATE subjects SET name = ?, class_id = ? WHERE id = ?');
            $stmt->execute([$subject_name, $class_id, $subject_id]);
            $subject_message = 'Subject updated successfully!';
        } catch (PDOException $e) {
            $subject_message = 'Error updating subject.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];

    try {
        $stmt = $pdo->prepare('INSERT INTO teacher_assignments (teacher_id, class_id, subject_id) VALUES (?, ?, ?)');
        $stmt->execute([$teacher_id, $class_id, $subject_id]);
        $assignment_message = 'Teacher assigned successfully!';
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            $assignment_message = 'This teacher is already assigned to this subject and class.';
        } else {
            $assignment_message = 'Error assigning teacher.';
        }
    }
}

// Handle assignment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    try {
        $stmt = $pdo->prepare('DELETE FROM teacher_assignments WHERE id = ?');
        $stmt->execute([$assignment_id]);
        $assignment_message = 'Teacher assignment removed successfully!';
    } catch (PDOException $e) {
        $assignment_message = 'Error removing teacher assignment.';
    }
}

// Get teachers for assignment dropdown
$teachers_stmt = $pdo->query('SELECT id, name FROM users WHERE role = "Teacher" ORDER BY name');
$teachers = $teachers_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects Management - EduWave Admin</title>

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
                <h3>Subjects Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Manage Subjects</h4>
                            </div>
                             <div class="card-body">
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
                                    if (!empty($_POST['subject_name']) && !empty($_POST['class_id'])) {
                                        try {
                                            $stmt = $pdo->prepare('INSERT INTO subjects (name, class_id) VALUES (?, ?)');
                                            $stmt->execute([$_POST['subject_name'], $_POST['class_id']]);
                                            $subject_message = 'Subject added successfully!';
                                        } catch (PDOException $e) {
                                            $subject_message = 'Error adding subject.';
                                        }
                                    }
                                }

                                if ($subject_message): ?>
                                    <div class="alert alert-<?= strpos($subject_message, 'success') !== false ? 'success' : 'danger' ?>"><?= $subject_message ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="subject_name" class="form-label">Subject Name</label>
                                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class</label>
                                        <select class="form-select" id="class_id" name="class_id" required>
                                            <?php
                                            $classes_stmt = $pdo->query('SELECT * FROM classes');
                                            $classes = $classes_stmt->fetchAll();
                                            foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                                </form>

                                <h5 class="mt-4">Teacher Assignment</h5>
                                <?php if ($assignment_message): ?>
                                    <div class="alert alert-info"><?= $assignment_message ?></div>
                                <?php endif; ?>
                                <form method="POST" class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label for="teacher_id" class="form-label">Teacher</label>
                                        <select class="form-select" id="teacher_id" name="teacher_id" required>
                                            <option value="">Select Teacher</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="assign_class_id" class="form-label">Class</label>
                                        <select class="form-select" id="assign_class_id" name="class_id" required>
                                            <option value="">Select Class</option>
                                            <?php
                                            $classes_stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
                                            $all_classes = $classes_stmt->fetchAll();
                                            foreach ($all_classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="assign_subject_id" class="form-label">Subject</label>
                                        <select class="form-select" id="assign_subject_id" name="subject_id" required>
                                            <option value="">Select Class First</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="submit" name="assign_teacher" class="btn btn-primary">Assign Teacher</button>
                                        </div>
                                    </div>
                                </form>

                                <h5 class="mt-4">Existing Subjects</h5>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
<li><a class="dropdown-item" href="#" onclick="exportTable('xlsx')">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('csv')">
                                                <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Export CSV
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('txt')">
                                                <i class="bi bi-file-earmark-text me-2 text-info"></i>Export Text
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('pdf')">
                                                <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export PDF
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="subjectsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Subject Name</th>
                                                <th>Class</th>
                                                <th>Assigned Teachers</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $subjects_stmt = $pdo->query('SELECT s.id, s.name, s.class_id, c.grade_name, COUNT(ta.id) as assigned_teachers FROM subjects s LEFT JOIN classes c ON s.class_id = c.id LEFT JOIN teacher_assignments ta ON s.id = ta.subject_id GROUP BY s.id ORDER BY c.grade_name, s.name');
                                            $subjects = $subjects_stmt->fetchAll();
                                            foreach ($subjects as $subject): 
                                                $assignments_stmt = $pdo->prepare('SELECT ta.id, u.name as teacher_name FROM teacher_assignments ta JOIN users u ON ta.teacher_id = u.id WHERE ta.subject_id = ? AND ta.class_id = ?');
                                                $assignments_stmt->execute([$subject['id'], $subject['class_id']]);
                                                $assignments = $assignments_stmt->fetchAll();
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($subject['id']) ?></td>
                                                    <td><?= htmlspecialchars($subject['name']) ?></td>
                                                    <td><?= htmlspecialchars($subject['grade_name']) ?></td>
                                                    <td>
                                                        <?php foreach ($assignments as $assignment): ?>
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="badge bg-success me-2"><?= htmlspecialchars($assignment['teacher_name']) ?></span>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                                                    <button type="submit" name="remove_assignment" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this teacher assignment?')">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        <?php endforeach; ?>
                                                        <?php if (empty($assignments)): ?>
                                                            <span class="text-muted">No teachers assigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                     <td>
                                                         <a href="javascript:void(0)" onclick="editSubject(<?= htmlspecialchars(json_encode($subject)) ?>)" class="me-2 text-warning" title="Edit" style="text-decoration: none; font-size: 1.2rem;">
                                                             <i class="bi bi-pencil"></i>
                                                         </a>
                                                         <a href="subject_delete.php?id=<?= $subject['id'] ?>" class="text-danger" title="Delete" style="text-decoration: none; font-size: 1.2rem;"
                                                            onclick="return confirm('Are you sure you want to delete this subject? This action cannot be undone.');">
                                                             <i class="bi bi-trash"></i>
                                                         </a>
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

    <script src="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../dashboardassets/js/bootstrap.bundle.min.js"></script>
    <script src="../dashboardassets/js/main.js"></script>
    
    <!-- TableExport Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="../includes/tableExport.jquery.plugin/tableExport.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_subject_id" name="subject_id">
                        <div class="mb-3">
                            <label for="edit_subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="edit_subject_name" name="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_subject_class_id" class="form-label">Class</label>
                            <select class="form-select" id="edit_subject_class_id" name="class_id" required>
                                <?php
                                $classes_stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
                                $all_classes = $classes_stmt->fetchAll();
                                foreach ($all_classes as $class): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_subject" class="btn btn-primary">Update Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editSubject(subjectData) {
            document.getElementById('edit_subject_id').value = subjectData.id;
            document.getElementById('edit_subject_name').value = subjectData.name;
            document.getElementById('edit_subject_class_id').value = subjectData.class_id;

            new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
        }

function exportTable(type) {
            const fileName = 'subjects_' + new Date().toISOString().slice(0, 10);
            
            const options = {
                tableName: 'Subjects',
                worksheetName: 'Subjects',
                fileName: fileName,
                excelstyles: ['border-bottom', 'border-top', 'border-left', 'border-right'],
                onMsoNumberFormat: function(cell, row, col) {
                    if (!isNaN(cell.innerHTML) && cell.innerHTML !== '') {
                        return '\\@';
                    }
                }
            };

        switch(type) {
            case 'xlsx':
                options.type = 'xlsx';
                $('#subjectsTable').tableExport(options);
                break;
            case 'csv':
                exportToCSV();
                break;
            case 'txt':
                exportToTXT();
                break;
            case 'pdf':
                exportToPDF();
                break;
        }
        }

        function exportToCSV() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'subjects';
            form.appendChild(typeInput);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = 'csv';
            form.appendChild(formatInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToTXT() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = 'subjects';
            form.appendChild(typeInput);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = 'txt';
            form.appendChild(formatInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToPDF() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_pdf.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'subjects';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }

        document.getElementById('assign_class_id').addEventListener('change', function() {
            const classId = this.value;
            const subjectSelect = document.getElementById('assign_subject_id');

            // Clear existing options
            subjectSelect.innerHTML = '<option value="">Loading...</option>';

            if (classId) {
                fetch(`get_subjects.php?class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                        data.forEach(subject => {
                            const option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.name;
                            subjectSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading subjects:', error);
                        subjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                    });
            } else {
                subjectSelect.innerHTML = '<option value="">Select Class First</option>';
            }
        });
    </script>
</body>

</html>