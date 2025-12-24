<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Registrar User';

$message = '';

// Handle student deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete from student_classes (may not exist)
        $stmt = $pdo->prepare('DELETE FROM student_classes WHERE student_id = ?');
        $stmt->execute([$student_id]);
        
        // Delete from parent_student
        $stmt = $pdo->prepare('DELETE FROM parent_student WHERE student_id = ?');
        $stmt->execute([$student_id]);
        
        // Delete from grades (may not exist)
        $stmt = $pdo->prepare('DELETE FROM grades WHERE student_id = ?');
        $stmt->execute([$student_id]);
        
        // Delete from attendance (may not exist)
        $stmt = $pdo->prepare('DELETE FROM attendance WHERE student_id = ?');
        $stmt->execute([$student_id]);
        
        // Delete from assignments submissions (may not exist)
        $stmt = $pdo->prepare('DELETE FROM submissions WHERE student_id = ?');
        $stmt->execute([$student_id]);
        
        // Finally delete the user
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "Student"');
        $stmt->execute([$student_id]);
        
        $pdo->commit();
        $message = 'Student deleted successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = 'Error deleting student.';
    }
}

// Handle student update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $class_id = $_POST['class_id'];
    $section_id = !empty($_POST['section_id']) ? $_POST['section_id'] : null;
    
    try {
        $pdo->beginTransaction();
        
        // Update user info
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ? AND role = "Student"');
        $stmt->execute([$name, $email, $student_id]);
        
        // Check section capacity if specified
        if ($section_id) {
            $capacity_stmt = $pdo->prepare('SELECT s.max_students, COALESCE(COUNT(sc.student_id), 0) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.id = ? AND s.id != COALESCE((SELECT section_id FROM student_classes WHERE student_id = ?), 0) GROUP BY s.id');
            $capacity_stmt->execute([$section_id, $student_id]);
            $capacity = $capacity_stmt->fetch();
            
            if ($capacity && $capacity['enrolled'] >= $capacity['max_students']) {
                throw new Exception('Selected section is already at maximum capacity.');
            }
        }
        
        // Check if student already has a class assignment
        $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM student_classes WHERE student_id = ?');
        $check_stmt->execute([$student_id]);
        
        if ($check_stmt->fetchColumn() > 0) {
            // Update existing class assignment
            $stmt = $pdo->prepare('UPDATE student_classes SET class_id = ?, section_id = ?, academic_year = ? WHERE student_id = ?');
            $stmt->execute([$class_id, $section_id, date('Y'), $student_id]);
        } else {
            // Insert new class assignment
            $stmt = $pdo->prepare('INSERT INTO student_classes (student_id, class_id, section_id, academic_year) VALUES (?, ?, ?, ?)');
            $stmt->execute([$student_id, $class_id, $section_id, date('Y')]);
        }
        
        $pdo->commit();
        $message = 'Student updated successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($e->errorInfo[1] == 1062) {
            $message = 'Email already exists.';
        } else {
            $message = 'Error updating student.';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
    }
}

// Get all students with their class information (including unenrolled students)
$students_stmt = $pdo->query('SELECT u.id, u.name, u.email, c.grade_name, s.section_name, sc.class_id, sc.section_id FROM users u LEFT JOIN student_classes sc ON u.id = sc.student_id LEFT JOIN classes c ON sc.class_id = c.id LEFT JOIN sections s ON sc.section_id = s.id WHERE u.role = "Student" ORDER BY u.name');
$students = $students_stmt->fetchAll();

// Get classes for dropdown
$classes_stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
$classes = $classes_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - EduWave Registrar</title>

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
                                <li class="submenu-item active">
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
                <h3>Student Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4>Manage Students</h4>
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('xlsx')">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('csv')">
                                                <i class="bi bi-file-earmark-spreadsheet me-2 text-warning"></i>Export CSV
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
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-striped" id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                                    <td><?= htmlspecialchars($student['grade_name'] ?? 'Not enrolled') ?></td>
                                                    <td><?= htmlspecialchars($student['section_name'] ?? 'Not assigned') ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editStudent(<?= htmlspecialchars(json_encode($student)) ?>)">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['name']) ?>')">
                                                            <i class="bi bi-trash"></i> Delete
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

            <!-- Edit Student Modal -->
            <div class="modal fade" id="editModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Student</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="student_id" id="edit_student_id">
                                
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_class_id" class="form-label">Class</label>
                                    <select class="form-select" id="edit_class_id" name="class_id" required onchange="loadEditSections(this.value)">
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_section_id" class="form-label">Section</label>
                                    <select class="form-select" id="edit_section_id" name="section_id">
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <p>Are you sure you want to delete <strong id="delete_student_name"></strong>? This action cannot be undone and will remove all associated records.</p>
                                <input type="hidden" name="student_id" id="delete_student_id">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_student" class="btn btn-danger">Delete Student</button>
                            </div>
                        </form>
                    </div>
                </div>
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
    
    <script>
        function exportTable(type) {
            const fileName = 'students_management_' + new Date().toISOString().slice(0, 10);
            
            const options = {
                tableName: 'Students Management',
                worksheetName: 'Students',
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
                $('#studentsTable').tableExport(options);
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
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'csv';
            form.appendChild(input);

            const dataInput = document.createElement('input');
            dataInput.type = 'hidden';
            dataInput.name = 'data_type';
            dataInput.value = 'students';
            form.appendChild(dataInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToTXT() {
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'txt';
            form.appendChild(input);

            const dataInput = document.createElement('input');
            dataInput.type = 'hidden';
            dataInput.name = 'data_type';
            dataInput.value = 'students';
            form.appendChild(dataInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function exportToPDF() {
            // Open in same window to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_pdf.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'students';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }

        function editStudent(student) {
            document.getElementById('edit_student_id').value = student.id;
            document.getElementById('edit_name').value = student.name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_class_id').value = student.class_id || '';
            
            loadEditSections(student.class_id || '', student.section_id || '');
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function deleteStudent(studentId, studentName) {
            document.getElementById('delete_student_id').value = studentId;
            document.getElementById('delete_student_name').textContent = studentName;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function loadEditSections(classId, selectedSectionId = null) {
            const sectionSelect = document.getElementById('edit_section_id');
            
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
                                if (section.id == selectedSectionId) {
                                    option.selected = true;
                                }
                                if (section.enrolled >= section.max_students && section.id != selectedSectionId) {
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