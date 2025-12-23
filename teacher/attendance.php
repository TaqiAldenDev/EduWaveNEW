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
    if (!empty($_POST['class_id']) && !empty($_POST['subject_id']) && !empty($_POST['attend_date'])) {
        $class_id = $_POST['class_id'];
        $subject_id = $_POST['subject_id'];
        $attend_date = $_POST['attend_date'];
        $attendance_data = $_POST['attendance'];

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO attendance (student_id, subject_id, class_id, attend_date, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
            foreach ($attendance_data as $student_id => $status) {
                $stmt->execute([$student_id, $subject_id, $class_id, $attend_date, $status]);
            }
            $pdo->commit();
            $message = 'Attendance saved successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error saving attendance.';
        }
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

$students = [];
$selected_class_id = null;
$selected_subject_id = null;
$attend_date = date('Y-m-d');

// Handle GET request for initial page load with selection
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['assignment'])) {
        $assignment_parts = explode('-', $_GET['assignment']);
        if (count($assignment_parts) === 2) {
            $selected_class_id = $assignment_parts[0];
            $selected_subject_id = $assignment_parts[1];
            $attend_date = isset($_GET['attend_date']) ? $_GET['attend_date'] : date('Y-m-d');
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_class_id = $_POST['class_id'];
    $selected_subject_id = $_POST['subject_id'];
    $attend_date = $_POST['attend_date'];
}

// Load students if we have both class and subject IDs
if ($selected_class_id && $selected_subject_id) {
    try {
        $stmt = $pdo->prepare('SELECT users.id, users.name FROM users JOIN student_classes ON users.id = student_classes.student_id WHERE student_classes.class_id = ? ORDER BY users.name');
        $stmt->execute([$selected_class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get existing attendance for all students on this date
        $attendance_records = [];
        $stmt = $pdo->prepare('SELECT student_id, status FROM attendance WHERE subject_id = ? AND class_id = ? AND attend_date = ?');
        $stmt->execute([$selected_subject_id, $selected_class_id, $attend_date]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $record) {
            $attendance_records[$record['student_id']] = $record['status'];
        }
    } catch (PDOException $e) {
        $students = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - EduWave Teacher</title>

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

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-clipboard"></i>
                                <span>Attendance</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
                                    <a href="attendance.php">Take Attendance</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="attendance_summary.php">View Summary</a>
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
                                <li class="submenu-item">
                                    <a href="grades_summary.php">View Summary</a>
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
                <h3>Take Attendance</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Attendance Taking</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="GET">
                                    <div class="row mb-3">
                                        <div class="col-md-5">
                                            <label for="assignment" class="form-label">Class and Subject</label>
                                            <select class="form-select" id="assignment" name="assignment" onchange="this.form.submit()">
                                                <option value="">Select Class and Subject</option>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <option value="<?= $assignment['class_id'] ?>-<?= $assignment['subject_id'] ?>" <?= (isset($_GET['assignment']) && $_GET['assignment'] == $assignment['class_id'] . '-' . $assignment['subject_id']) ? 'selected' : '' ?>><?= htmlspecialchars($assignment['grade_name'] . ' - ' . $assignment['subject_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="attend_date" class="form-label">Date</label>
                                            <input type="date" class="form-control" id="attend_date" name="attend_date" value="<?= htmlspecialchars($attend_date) ?>" onchange="if(document.getElementById('assignment').value) this.form.submit()">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <?php if ($selected_class_id && $selected_subject_id): ?>
                                                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                                                        <i class="bi bi-x-circle"></i> Clear Selection
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if ($selected_class_id && $selected_subject_id): ?>
                                    <?php if (!empty($students)): ?>
                                        <form method="POST">
                                            <input type="hidden" name="class_id" value="<?= htmlspecialchars($selected_class_id) ?>">
                                            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">
                                            <input type="hidden" name="attend_date" value="<?= htmlspecialchars($attend_date) ?>">
                                            
                                            <!-- Bulk Actions -->
                                            <div class="mb-3">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="selectAllPresent()">Mark All Present</button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="selectAllAbsent()">Mark All Absent</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleSelection()">Toggle Selection</button>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="resetToDefault()">Reset to Default</button>
                                                </div>
                                                <small class="text-muted ms-2">Quick actions for bulk attendance marking</small>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">
                                                                <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                                                            </th>
                                                            <th>Student Name</th>
                                                            <th width="25%">Status</th>
                                                            <th width="15%">Notes</th>
                                                            <th width="10%">Previous</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $present_count = 0;
                                                        $absent_count = 0;
                                                        foreach ($students as $student): 
                                                            // Get existing attendance status or default to Present
                                                            $existing_status = isset($attendance_records[$student['id']]) ? $attendance_records[$student['id']] : 'Present';
                                                            $is_present = $existing_status === 'Present';
                                                            if ($is_present) $present_count++; else $absent_count++;
                                                            
                                                            // Get previous attendance records for context
                                                            try {
                                                                $stmt = $pdo->prepare('SELECT status, attend_date FROM attendance WHERE student_id = ? AND subject_id = ? AND attend_date < ? ORDER BY attend_date DESC LIMIT 3');
                                                                $stmt->execute([$student['id'], $selected_subject_id, $attend_date]);
                                                                $previous_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                            } catch (PDOException $e) {
                                                                $previous_records = [];
                                                            }
                                                        ?>
                                                            <tr class="<?= !$is_present ? 'table-warning' : '' ?>">
                                                                <td>
                                                                    <input type="checkbox" class="student-checkbox" name="attendance[<?= $student['id'] ?>]" value="Present" <?= $is_present ? 'checked' : '' ?> data-student="<?= htmlspecialchars($student['name']) ?>">
                                                                </td>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($student['name']) ?></strong>
                                                                    <?php if (!$is_present): ?>
                                                                        <span class="badge bg-warning ms-2">Absent</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm d-flex" role="group">
                                                                        <input type="radio" class="btn-check" name="attendance_status[<?= $student['id'] ?>]" id="present_<?= $student['id'] ?>" value="Present" <?= $is_present ? 'checked' : '' ?> onchange="updateCheckbox(<?= $student['id'] ?>, true)">
                                                                        <label class="btn btn-outline-success" for="present_<?= $student['id'] ?>">
                                                                            <i class="bi bi-check-circle"></i> Present
                                                                        </label>
                                                                        
                                                                        <input type="radio" class="btn-check" name="attendance_status[<?= $student['id'] ?>]" id="absent_<?= $student['id'] ?>" value="Absent" <?= !$is_present ? 'checked' : '' ?> onchange="updateCheckbox(<?= $student['id'] ?>, false)">
                                                                        <label class="btn btn-outline-danger" for="absent_<?= $student['id'] ?>">
                                                                            <i class="bi bi-x-circle"></i> Absent
                                                                        </label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm" name="notes[<?= $student['id'] ?>]" placeholder="Add notes..." value="<?= isset($_POST['notes'][$student['id']]) ? htmlspecialchars($_POST['notes'][$student['id']]) : '' ?>">
                                                                </td>
                                                                <td>
                                                                    <?php if (!empty($previous_records)): ?>
                                                                        <div class="small">
                                                                            <?php foreach ($previous_records as $prev): ?>
                                                                                <span class="badge <?= $prev['status'] === 'Present' ? 'bg-success' : 'bg-danger' ?> me-1" title="<?= date('M j', strtotime($prev['attend_date'])) ?>">
                                                                                    <?= $prev['status'] === 'Present' ? 'P' : 'A' ?>
                                                                                </span>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <span class="text-muted small">No records</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Summary Statistics -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <div class="alert alert-info">
                                                        <strong>Summary:</strong> 
                                                        <span class="badge bg-success"><?= $present_count ?> Present</span> / 
                                                        <span class="badge bg-danger"><?= $absent_count ?> Absent</span> / 
                                                        Total: <?= count($students) ?> students
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="bi bi-save"></i> Save Attendance (<?= count($students) ?> students)
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> No students found in this class.
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Please select a class and subject to take attendance.
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
        function clearSelection() {
            window.location.href = 'attendance.php';
        }

        // Checkbox and bulk selection functions
        function toggleAllCheckboxes() {
            var selectAll = document.getElementById('selectAll');
            var checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
                updateRadioButtons(checkbox);
            });
            updateSummary();
        }

        function updateCheckbox(studentId, isPresent) {
            var checkbox = document.querySelector('input[name="attendance[' + studentId + ']"]');
            if (checkbox) {
                checkbox.checked = isPresent;
            }
            updateRowStyling(studentId, isPresent);
            updateSummary();
        }

        function updateRadioButtons(checkbox) {
            var studentId = checkbox.name.match(/\[(\d+)\]/)[1];
            var isPresent = checkbox.checked;
            var radio = document.querySelector('input[name="attendance_status[' + studentId + ']"]:checked');
            if (!radio || (radio.value === 'Present') !== isPresent) {
                var targetRadio = document.querySelector('input[name="attendance_status[' + studentId + ']"][value="' + (isPresent ? 'Present' : 'Absent') + '"]');
                if (targetRadio) targetRadio.checked = true;
            }
            updateRowStyling(studentId, isPresent);
        }

        function updateRowStyling(studentId, isPresent) {
            var row = document.querySelector('tr:has(input[name="attendance[' + studentId + ']"])');
            if (row) {
                if (isPresent) {
                    row.classList.remove('table-warning');
                } else {
                    row.classList.add('table-warning');
                }
            }
        }

        function selectAllPresent() {
            document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
                checkbox.checked = true;
                updateRadioButtons(checkbox);
            });
            document.getElementById('selectAll').checked = true;
            updateSummary();
        }

        function selectAllAbsent() {
            document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
                updateRadioButtons(checkbox);
            });
            document.getElementById('selectAll').checked = false;
            updateSummary();
        }

        function toggleSelection() {
            document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
                checkbox.checked = !checkbox.checked;
                updateRadioButtons(checkbox);
            });
            updateSelectAllCheckbox();
            updateSummary();
        }

        function resetToDefault() {
            selectAllPresent(); // Default to present
        }

        function updateSelectAllCheckbox() {
            var checkboxes = document.querySelectorAll('.student-checkbox');
            var checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            document.getElementById('selectAll').checked = (checkedCount === checkboxes.length);
            document.getElementById('selectAll').indeterminate = (checkedCount > 0 && checkedCount < checkboxes.length);
        }

        function updateSummary() {
            var checkboxes = document.querySelectorAll('.student-checkbox');
            var checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
            var presentCount = checkedCount;
            var absentCount = checkboxes.length - checkedCount;
            
            console.log('Present: ' + presentCount + ', Absent: ' + absentCount);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectAllCheckbox();
            updateSummary();
        });

        // Handle checkbox changes manually
        document.querySelectorAll('.student-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateRadioButtons(this);
                updateSelectAllCheckbox();
                updateSummary();
            });
        });

        // Prevent weekends from being selected
        document.getElementById('attend_date').addEventListener('change', function() {
            var date = new Date(this.value);
            var day = date.getDay();
            if (day === 0 || day === 6) { // Sunday or Saturday
                alert('Please select a weekday (Monday to Friday)');
                this.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>