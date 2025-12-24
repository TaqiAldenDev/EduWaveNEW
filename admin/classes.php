<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

// Handle section management
$section_message = '';
$class_message = '';

// Handle class editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class'])) {
    if (!empty($_POST['class_id']) && !empty($_POST['grade_name'])) {
        $class_id = $_POST['class_id'];
        $grade_name = $_POST['grade_name'];
        $notes = $_POST['notes'];

        try {
            $stmt = $pdo->prepare('UPDATE classes SET grade_name = ?, notes = ? WHERE id = ?');
            $stmt->execute([$grade_name, $notes, $class_id]);
            $class_message = 'Class updated successfully!';
        } catch (PDOException $e) {
            $class_message = 'Error updating class.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_section'])) {
    $action = $_POST['action'] ?? '';
    $section_message = '';
    
    try {
        if ($action === 'add') {
            $class_id = $_POST['class_id'] ?? '';
            $section_name = $_POST['section_name'] ?? '';
            $max_students = $_POST['max_students'] ?? 30;
            
            if (empty($class_id) || empty($section_name) || !isset($_POST['max_students'])) {
                $section_message = 'Please fill in all required fields.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO sections (class_id, section_name, max_students) VALUES (?, ?, ?)');
                $stmt->execute([$class_id, $section_name, $max_students]);
                $section_message = 'Section added successfully!';
            }
        } elseif ($action === 'edit') {
            $class_id = $_POST['class_id'] ?? '';
            $section_name = $_POST['section_name'] ?? '';
            $max_students = $_POST['max_students'] ?? 30;
            $section_id = $_POST['section_id'] ?? '';
            
            if (empty($class_id) || empty($section_name) || !isset($_POST['max_students']) || !isset($_POST['section_id'])) {
                $section_message = 'Please fill in all required fields.';
            } else {
                $stmt = $pdo->prepare('UPDATE sections SET section_name = ?, max_students = ? WHERE id = ?');
                $stmt->execute([$section_name, $max_students, $section_id]);
                $section_message = 'Section updated successfully!';
            }
        } elseif ($action === 'delete') {
            $section_id = $_POST['section_id'] ?? '';
            
            if (!isset($_POST['section_id']) || empty($_POST['section_id'])) {
                $section_message = 'Please provide a section to delete.';
            } else {
                $check_stmt = $pdo->prepare('SELECT COUNT(*) as count FROM student_classes WHERE section_id = ?');
                $check_stmt->execute([$section_id]);
                $student_count = $check_stmt->fetch()['count'];

                if ($student_count > 0) {
                    $section_message = 'Cannot delete section with enrolled students. Please move students first.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM sections WHERE id = ?');
                    $stmt->execute([$section_id]);
                    $section_message = 'Section deleted successfully!';
                }
            }
        } else {
            $section_message = 'Invalid action specified.';
        }
    } catch (PDOException $e) {
        $section_message = 'Error managing section.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes & Subjects - EduWave Admin</title>

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
                                <li class="submenu-item active">
                                    <a href="classes.php">Classes</a>
                                </li>
                                <li class="submenu-item ">
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
                <h3>Classes Management</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Manage Classes</h4>
                            </div>
                            <div class="card-body">
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
                                    if (!empty($_POST['grade_name'])) {
                                        try {
                                            $stmt = $pdo->prepare('INSERT INTO classes (grade_name, notes) VALUES (?, ?)');
                                            $stmt->execute([$_POST['grade_name'], $_POST['notes']]);
                                            $class_message = 'Class added successfully!';
                                        } catch (PDOException $e) {
                                            $class_message = 'Error adding class.';
                                        }
                                    }
                                }

                                if ($class_message): ?>
                                    <div class="alert alert-<?= strpos($class_message, 'success') !== false ? 'success' : 'danger' ?>"><?= $class_message ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="grade_name" class="form-label">Grade Name</label>
                                        <input type="text" class="form-control" id="grade_name" name="grade_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes"></textarea>
                                    </div>
                                    <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
                                </form>

                                <h5 class="mt-4">Existing Classes and Sections</h5>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-download me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                            <li><a class="dropdown-item" href="#" onclick="exportTable('xlsx')">
                                                <i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel
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
                                    <table class="table table-striped" id="classesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Grade Name</th>
                                                <th>Notes</th>
                                                <th>Total Students</th>
                                                <th>Sections</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $classes_stmt = $pdo->query('SELECT c.id, c.grade_name, c.notes, COUNT(sc.student_id) as student_count FROM classes c LEFT JOIN student_classes sc ON c.id = sc.class_id GROUP BY c.id ORDER BY c.grade_name');
                                            $classes = $classes_stmt->fetchAll();
                                            foreach ($classes as $class): 
                                                $sections_stmt = $pdo->prepare('SELECT s.id, s.section_name, s.max_students, COUNT(sc.student_id) as enrolled FROM sections s LEFT JOIN student_classes sc ON s.id = sc.section_id WHERE s.class_id = ? GROUP BY s.id ORDER BY s.section_name');
                                                $sections_stmt->execute([$class['id']]);
                                                $sections = $sections_stmt->fetchAll();
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($class['id']) ?></td>
                                                    <td><?= htmlspecialchars($class['grade_name']) ?></td>
                                                    <td><?= htmlspecialchars($class['notes'] ?? '') ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?= ($class['student_count'] ?? 0) ?> students</span>
                                                    </td>
                                                    <td>
                                                        <?php foreach ($sections as $section): ?>
                                                            <div class="mb-1">
                                                                <strong><?= htmlspecialchars($section['section_name'] ?? 'Unknown') ?></strong>: 
                                                                <span class="badge bg-<?= ($section['enrolled'] ?? 0) >= ($section['max_students'] ?? 30) ? 'danger' : 'success' ?>">
                                                                    <?= ($section['enrolled'] ?? 0) ?>/<?= ($section['max_students'] ?? 30) ?>
                                                                </span>
                                                                <button class="btn btn-sm btn-info ms-1 text-white" onclick="viewSection(<?= $section['id'] ?>, <?= $class['id'] ?>)" title="View Section Details">
                                                                    <i class="bi bi-eye-fill"></i>
                                                                </button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                        <button class="btn btn-sm btn-outline-success mt-2" onclick="addSection(<?= $class['id'] ?>)">
                                                            <i class="bi bi-plus"></i> Add Section
                                                        </button>
                                                    </td>
                                                     <td>
                                                         <a href="javascript:void(0)" onclick="editClass(<?= htmlspecialchars(json_encode($class)) ?>)" class="me-2 text-warning" title="Edit" style="text-decoration: none; font-size: 1.2rem;">
                                                              <i class="bi bi-pencil"></i>
                                                          </a>
                                                          <a href="class_delete.php?id=<?= $class['id'] ?>" class="text-danger" title="Delete" style="text-decoration: none; font-size: 1.2rem;"
                                                             onclick="return confirm('Are you sure you want to delete this class? This action cannot be undone.');">
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

                <!-- Section Management Modal -->
                <div class="modal fade" id="sectionModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="sectionModalTitle">Manage Section</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" id="sectionForm">
                                <div class="modal-body">
                                    <input type="hidden" name="section_id" id="section_id">
                                    <input type="hidden" name="class_id" id="class_id">
                                    <input type="hidden" name="action" id="section_action">
                                    
                                    <div class="mb-3">
                                        <label for="section_name" class="form-label">Section Name</label>
                                        <input type="text" class="form-control" id="section_name" name="section_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="max_students" class="form-label">Maximum Students</label>
                                        <input type="number" class="form-control" id="max_students" name="max_students" min="1" max="100" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="manage_section" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- View Section Modal -->
                <div class="modal fade" id="viewSectionModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewSectionModalTitle">Section Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6>Section Information</h6>
                                        <p><strong>Name:</strong> <span id="view_section_name"></span></p>
                                        <p><strong>Capacity:</strong> <span id="view_section_capacity"></span></p>
                                        <p><strong>Enrolled:</strong> <span id="view_section_enrolled"></span></p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-outline-primary" onclick="editSectionFromView()">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteSectionFromView()">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h6>Students in this Section</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Enrollment Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="view_section_students">
                                            <tr>
                                                <td colspan="3" class="text-center">Loading students...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
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

    <!-- Edit Class Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_class_id" name="class_id">
                        <div class="mb-3">
                            <label for="edit_grade_name" class="form-label">Grade Name</label>
                            <input type="text" class="form-control" id="edit_grade_name" name="grade_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_class" class="btn btn-primary">Update Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentSectionId = null;
        let currentClassId = null;

        function editClass(classData) {
            document.getElementById('edit_class_id').value = classData.id;
            document.getElementById('edit_grade_name').value = classData.grade_name;
            document.getElementById('edit_notes').value = classData.notes || '';

            new bootstrap.Modal(document.getElementById('editClassModal')).show();
        }

        function editSection(sectionId, sectionName, maxStudents) {
            currentSectionId = sectionId;
            document.getElementById('sectionModalTitle').textContent = 'Edit Section';
            document.getElementById('section_id').value = sectionId;
            document.getElementById('class_id').value = '';
            document.getElementById('section_action').value = 'edit';
            document.getElementById('section_name').value = sectionName;
            document.getElementById('max_students').value = maxStudents;

            new bootstrap.Modal(document.getElementById('sectionModal')).show();
        }

        function addSection(classId) {
            currentSectionId = null;
            document.getElementById('sectionModalTitle').textContent = 'Add New Section';
            document.getElementById('section_id').value = '';
            document.getElementById('class_id').value = classId;
            document.getElementById('section_action').value = 'add';
            document.getElementById('section_name').value = '';
            document.getElementById('max_students').value = 30;

            new bootstrap.Modal(document.getElementById('sectionModal')).show();
        }

        function viewSection(sectionId, classId) {
            currentSectionId = sectionId;
            currentClassId = classId;

            fetch(`get_section_students.php?section_id=${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    const section = data.section;
                    const students = data.students;

                    // Update section info
                    document.getElementById('view_section_name').textContent = section.section_name;
                    document.getElementById('view_section_capacity').textContent = section.max_students;
                    document.getElementById('view_section_enrolled').textContent = section.enrolled;
                    
                    // Update students table
                    const studentsTable = document.getElementById('view_section_students');
                    if (students.length === 0) {
                        studentsTable.innerHTML = '<tr><td colspan="3" class="text-center">No students enrolled in this section</td></tr>';
                    } else {
                        studentsTable.innerHTML = students.map(student => `
                            <tr>
                                <td>${student.name}</td>
                                <td>${student.email}</td>
                                <td>${student.academic_year}</td>
                            </tr>
                        `).join('');
                    }
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('viewSectionModal')).show();
                })
                .catch(error => {
                    console.error('Error loading section data:', error);
                    alert('Error loading section data');
                });
        }
        
        function editSectionFromView() {
            if (currentSectionId) {
                const sectionName = document.getElementById('view_section_name').textContent;
                const maxStudents = parseInt(document.getElementById('view_section_capacity').textContent);
                
                // Close view modal
                bootstrap.Modal.getInstance(document.getElementById('viewSectionModal')).hide();
                
                // Open edit modal
                setTimeout(() => {
                    editSection(currentSectionId, sectionName, maxStudents);
                }, 300);
            }
        }
        
        function deleteSectionFromView() {
            if (currentSectionId && confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const inputs = [
                    { name: 'section_id', value: currentSectionId },
                    { name: 'action', value: 'delete' },
                    { name: 'manage_section', value: '1' }
                ];
                
                inputs.forEach(input => {
                    const inputElement = document.createElement('input');
                    inputElement.type = 'hidden';
                    inputElement.name = input.name;
                    inputElement.value = input.value;
                    form.appendChild(inputElement);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function exportTable(type) {
            const fileName = 'classes_sections_' + new Date().toISOString().slice(0, 10);
            
            const options = {
                tableName: 'Classes and Sections',
                worksheetName: 'Classes',
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
                    $('#classesTable').tableExport(options);
                    break;
                case 'txt':
                    options.type = 'txt';
                    $('#classesTable').tableExport(options);
                    break;
                case 'pdf':
                    exportToPDF();
                    break;
            }
        }

        function exportToPDF() {
            // Use POST to preserve session
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_pdf.php';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'type';
            input.value = 'classes';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }

        <?php if ($section_message): ?>
            alert('<?= $section_message ?>');
            window.location.reload();
        <?php endif; ?>
    </script>
</body>

</html>