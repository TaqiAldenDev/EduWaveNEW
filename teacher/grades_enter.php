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
    if (!empty($_POST['class_id']) && !empty($_POST['subject_id']) && !empty($_POST['exam_type']) && !empty($_POST['grades'])) {
        $class_id = $_POST['class_id'];
        $subject_id = $_POST['subject_id'];
        $exam_type = $_POST['exam_type'];
        $grades_data = $_POST['grades'];
        $date_given = isset($_POST['exam_date']) ? $_POST['exam_date'] : date('Y-m-d');

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO grades (student_id, subject_id, exam_type, score, date_given) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE score = VALUES(score), date_given = VALUES(date_given)');
            foreach ($grades_data as $student_id => $score) {
                if (trim($score) !== '') {
                    $stmt->execute([$student_id, $subject_id, $exam_type, $score, $date_given]);
                }
            }
            $pdo->commit();
            $message = 'Grades saved successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = 'Error saving grades.';
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

// Define common exam types
$exam_types = [
    'Quiz' => 'Quiz',
    'Test' => 'Test', 
    'Midterm' => 'Midterm Exam',
    'Final' => 'Final Exam',
    'Assignment' => 'Assignment',
    'Project' => 'Project',
    'Participation' => 'Class Participation',
    'Lab' => 'Lab Work',
    'Homework' => 'Homework'
];

$students = [];
$selected_class_id = null;
$selected_subject_id = null;
$selected_exam_type = null;
$exam_date = date('Y-m-d');

// Handle GET request for initial page load with selection
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['assignment'])) {
        $assignment_parts = explode('-', $_GET['assignment']);
        if (count($assignment_parts) === 2) {
            $selected_class_id = $assignment_parts[0];
            $selected_subject_id = $assignment_parts[1];
            $selected_exam_type = isset($_GET['exam_type']) ? $_GET['exam_type'] : '';
            $exam_date = isset($_GET['exam_date']) ? $_GET['exam_date'] : date('Y-m-d');
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_class_id = $_POST['class_id'];
    $selected_subject_id = $_POST['subject_id'];
    $selected_exam_type = $_POST['exam_type'];
    $exam_date = $_POST['exam_date'];
}

// Load students if we have both class and subject IDs
if ($selected_class_id && $selected_subject_id) {
    try {
        $stmt = $pdo->prepare('SELECT users.id, users.name FROM users JOIN student_classes ON users.id = student_classes.student_id WHERE student_classes.class_id = ? ORDER BY users.name');
        $stmt->execute([$selected_class_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get existing grades for this subject and exam type
        $existing_grades = [];
        if ($selected_exam_type) {
            $stmt = $pdo->prepare('SELECT student_id, score FROM grades WHERE subject_id = ? AND exam_type = ?');
            $stmt->execute([$selected_subject_id, $selected_exam_type]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($records as $record) {
                $existing_grades[$record['student_id']] = $record['score'];
            }
        }
        
        // Get grade statistics for this subject
        $grade_stats = [];
        $stmt = $pdo->prepare('SELECT exam_type, AVG(score) as avg_score, COUNT(*) as count FROM grades WHERE subject_id = ? GROUP BY exam_type');
        $stmt->execute([$selected_subject_id]);
        $grade_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Enter Grades - EduWave Teacher</title>

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
                                <li class="submenu-item">
                                    <a href="attendance_summary.php">View Summary</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-award"></i>
                                <span>Grades</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
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
                <h3>Enter Grades</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Grade Entry Form</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="GET">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="assignment" class="form-label">Class and Subject</label>
                                            <select class="form-select" id="assignment" name="assignment" onchange="this.form.submit()">
                                                <option value="">Select Class and Subject</option>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <option value="<?= $assignment['class_id'] ?>-<?= $assignment['subject_id'] ?>" <?= (isset($_GET['assignment']) && $_GET['assignment'] == $assignment['class_id'] . '-' . $assignment['subject_id']) ? 'selected' : '' ?>><?= htmlspecialchars($assignment['grade_name'] . ' - ' . $assignment['subject_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="exam_type" class="form-label">Exam Type</label>
                                            <select class="form-select" id="exam_type" name="exam_type" onchange="this.form.submit()">
                                                <option value="">Select Exam Type</option>
                                                <?php foreach ($exam_types as $key => $value): ?>
                                                    <option value="<?= htmlspecialchars($key) ?>" <?= (isset($_GET['exam_type']) && $_GET['exam_type'] == $key) ? 'selected' : '' ?>><?= htmlspecialchars($value) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="exam_date" class="form-label">Exam Date</label>
                                            <input type="date" class="form-control" id="exam_date" name="exam_date" value="<?= htmlspecialchars($exam_date) ?>" onchange="if(document.getElementById('assignment').value && document.getElementById('exam_type').value) this.form.submit()">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <?php if ($selected_class_id && $selected_subject_id): ?>
                                                    <button type="button" class="btn btn-secondary" onclick="clearSelection()">
                                                        <i class="bi bi-x-circle"></i> Clear
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if ($selected_class_id && $selected_subject_id && $selected_exam_type): ?>
                                    <?php if (!empty($students)): ?>
                                        <?php if (!empty($grade_stats)): ?>
                                            <div class="alert alert-info">
                                                <h6>Grade Statistics:</h6>
                                                <?php foreach ($grade_stats as $stat): ?>
                                                    <div><?= htmlspecialchars($stat['exam_type']) ?>: <?= number_format($stat['avg_score'], 2) ?>% average (<?= $stat['count'] ?> students)</div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <form method="POST">
                                            <input type="hidden" name="class_id" value="<?= htmlspecialchars($selected_class_id) ?>">
                                            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($selected_subject_id) ?>">
                                            <input type="hidden" name="exam_type" value="<?= htmlspecialchars($selected_exam_type) ?>">
                                            <input type="hidden" name="exam_date" value="<?= htmlspecialchars($exam_date) ?>">
                                            
                                            <!-- Bulk Actions -->
                                            <div class="mb-3">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="setBulkGrade(100)">Set All A+</button>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="setBulkGrade(90)">Set All A</button>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="setBulkGrade(80)">Set All B</button>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="setBulkGrade(70)">Set All C</button>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="setBulkGrade(60)">Set All D</button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="setBulkGrade(50)">Set All F</button>
                                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="generateRandomGrades()">Random Grades</button>
                                                </div>
                                                <small class="text-muted ms-2">Quick grade templates for testing</small>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th width="5%">#</th>
                                                            <th>Student Name</th>
                                                            <th width="15%">Grade (0-100)</th>
                                                            <th width="15%">Letter Grade</th>
                                                            <th width="10%">Status</th>
                                                            <th width="20%">Comments</th>
                                                            <th width="10%">Previous Avg</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $student_number = 1;
                                                        foreach ($students as $student): 
                                                            $student_grade = isset($existing_grades[$student['id']]) ? $existing_grades[$student['id']] : '';
                                                            
                                                            // Calculate letter grade
                                                            $letter_grade = '';
                                                            $grade_class = 'bg-secondary';
                                                            if ($student_grade !== '') {
                                                                $score = floatval($student_grade);
                                                                if ($score >= 90) { $letter_grade = 'A'; $grade_class = 'bg-success'; }
                                                                elseif ($score >= 80) { $letter_grade = 'B'; $grade_class = 'bg-info'; }
                                                                elseif ($score >= 70) { $letter_grade = 'C'; $grade_class = 'bg-warning'; }
                                                                elseif ($score >= 60) { $letter_grade = 'D'; $grade_class = 'bg-secondary'; }
                                                                else { $letter_grade = 'F'; $grade_class = 'bg-danger'; }
                                                            }
                                                            
                                                            // Get student's previous average
                                                            $prev_avg = '';
                                                            try {
                                                                $stmt = $pdo->prepare('SELECT AVG(score) as avg_score FROM grades WHERE subject_id = ? AND student_id = ? AND exam_type != ?');
                                                                $stmt->execute([$selected_subject_id, $student['id'], $selected_exam_type]);
                                                                $result = $stmt->fetch();
                                                                if ($result && $result['avg_score']) {
                                                                    $prev_avg = number_format($result['avg_score'], 1);
                                                                }
                                                            } catch (PDOException $e) {
                                                                // Ignore
                                                            }
                                                        ?>
                                                            <tr id="row-<?= $student['id'] ?>">
                                                                <td><?= $student_number ?></td>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($student['name']) ?></strong>
                                                                    <?php if ($prev_avg): ?>
                                                                        <br><small class="text-muted">Avg: <?= $prev_avg ?>%</small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <input type="number" 
                                                                           step="0.01" 
                                                                           class="form-control grade-input" 
                                                                           name="grades[<?= $student['id'] ?>]" 
                                                                           id="grade-<?= $student['id'] ?>"
                                                                           min="0" 
                                                                           max="100" 
                                                                           value="<?= htmlspecialchars($student_grade) ?>" 
                                                                           onchange="updateStudentGrade(<?= $student['id'] ?>)"
                                                                           oninput="updateStudentGrade(<?= $student['id'] ?>)">
                                                                </td>
                                                                <td>
                                                                    <span class="badge <?= $grade_class ?> fs-6" id="letter-<?= $student['id'] ?>"><?= $letter_grade ?: '-' ?></span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge status-badge" id="status-<?= $student['id'] ?>">
                                                                        <?php if ($student_grade !== ''): ?>
                                                                            <?php if (floatval($student_grade) >= 60): ?>
                                                                                <i class="bi bi-check-circle"></i> Pass
                                                                            <?php else: ?>
                                                                                <i class="bi bi-x-circle"></i> Fail
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            <i class="bi bi-dash-circle"></i> Pending
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <input type="text" 
                                                                           class="form-control form-control-sm" 
                                                                           name="comments[<?= $student['id'] ?>]" 
                                                                           placeholder="Add comments..."
                                                                           value="<?= isset($_POST['comments'][$student['id']]) ? htmlspecialchars($_POST['comments'][$student['id']]) : '' ?>">
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted"><?= $prev_avg ?: 'N/A' ?></small>
                                                                </td>
                                                            </tr>
                                                        <?php 
                                                        $student_number++;
                                                        endforeach; ?>
                                                    </tbody>
                                                    <tfoot class="table-secondary">
                                                        <tr>
                                                            <td colspan="2"><strong>Class Summary</strong></td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm text-center" 
                                                                       id="class-average" readonly value="0.0">
                                                            </td>
                                                            <td colspan="4">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <span class="badge bg-success me-1">A: <span id="count-a">0</span></span>
                                                                        <span class="badge bg-info me-1">B: <span id="count-b">0</span></span>
                                                                        <span class="badge bg-warning me-1">C: <span id="count-c">0</span></span>
                                                                        <span class="badge bg-secondary me-1">D: <span id="count-d">0</span></span>
                                                                        <span class="badge bg-danger">F: <span id="count-f">0</span></span>
                                                                    </div>
                                                                    <small class="text-muted">Pass Rate: <span id="pass-rate">0%</span></small>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-primary btn-lg">
                                                        <i class="bi bi-save"></i> Save Grades (<?= count($students) ?> students)
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" onclick="clearAllGrades()">
                                                        <i class="bi bi-arrow-clockwise"></i> Clear All
                                                    </button>
                                                    <button type="button" class="btn btn-info" onclick="validateAllGrades()">
                                                        <i class="bi bi-check-square"></i> Validate
                                                    </button>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <div class="alert alert-info d-inline-block">
                                                        <small><strong>Grading Scale:</strong><br>
                                                        A: 90-100% | B: 80-89% | C: 70-79%<br>
                                                        D: 60-69% | F: 0-59%</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> No students found in this class.
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($selected_class_id && $selected_subject_id): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Please select an exam type to enter grades.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Please select a class, subject, and exam type to enter grades.
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
            window.location.href = 'grades_enter.php';
        }

        function updateStudentGrade(studentId) {
            var gradeInput = document.getElementById('grade-' + studentId);
            var score = parseFloat(gradeInput.value) || 0;
            
            // Clamp to valid range
            if (score < 0) score = 0;
            if (score > 100) score = 100;
            gradeInput.value = score;
            
            // Update letter grade
            var letterGrade = '';
            var letterClass = 'bg-secondary';
            if (score >= 90) { letterGrade = 'A'; letterClass = 'bg-success'; }
            else if (score >= 80) { letterGrade = 'B'; letterClass = 'bg-info'; }
            else if (score >= 70) { letterGrade = 'C'; letterClass = 'bg-warning'; }
            else if (score >= 60) { letterGrade = 'D'; letterClass = 'bg-secondary'; }
            else { letterGrade = 'F'; letterClass = 'bg-danger'; }
            
            // Update letter badge
            var letterElement = document.getElementById('letter-' + studentId);
            letterElement.textContent = score > 0 ? letterGrade : '-';
            letterElement.className = 'badge ' + letterClass + ' fs-6';
            
            // Update status badge
            var statusElement = document.getElementById('status-' + studentId);
            if (score > 0) {
                if (score >= 60) {
                    statusElement.innerHTML = '<i class="bi bi-check-circle"></i> Pass';
                    statusElement.className = 'badge bg-success status-badge';
                } else {
                    statusElement.innerHTML = '<i class="bi bi-x-circle"></i> Fail';
                    statusElement.className = 'badge bg-danger status-badge';
                }
            } else {
                statusElement.innerHTML = '<i class="bi bi-dash-circle"></i> Pending';
                statusElement.className = 'badge bg-secondary status-badge';
            }
            
            // Update row styling
            var row = document.getElementById('row-' + studentId);
            row.className = '';
            if (score > 0 && score < 60) {
                row.classList.add('table-danger');
            } else if (score >= 90) {
                row.classList.add('table-success');
            }
            
            updateClassSummary();
        }

        function updateClassSummary() {
            var gradeInputs = document.querySelectorAll('.grade-input');
            var total = 0;
            var count = 0;
            var grades = { a: 0, b: 0, c: 0, d: 0, f: 0 };
            var passCount = 0;
            
            gradeInputs.forEach(function(input) {
                var score = parseFloat(input.value) || 0;
                if (score > 0) {
                    total += score;
                    count++;
                    if (score >= 60) passCount++;
                    
                    if (score >= 90) grades.a++;
                    else if (score >= 80) grades.b++;
                    else if (score >= 70) grades.c++;
                    else if (score >= 60) grades.d++;
                    else grades.f++;
                }
            });
            
            var average = count > 0 ? total / count : 0;
            var passRate = count > 0 ? (passCount / count) * 100 : 0;
            
            // Update class average
            document.getElementById('class-average').value = average.toFixed(1);
            
            // Update grade counts
            document.getElementById('count-a').textContent = grades.a;
            document.getElementById('count-b').textContent = grades.b;
            document.getElementById('count-c').textContent = grades.c;
            document.getElementById('count-d').textContent = grades.d;
            document.getElementById('count-f').textContent = grades.f;
            document.getElementById('pass-rate').textContent = passRate.toFixed(1) + '%';
        }

        function setBulkGrade(value) {
            if (confirm('Set all students to ' + value + ' points?')) {
                document.querySelectorAll('.grade-input').forEach(function(input) {
                    input.value = value;
                    var studentId = input.id.match(/grade-(\d+)/)[1];
                    updateStudentGrade(studentId);
                });
            }
        }

        function generateRandomGrades() {
            if (confirm('Generate random grades for all students?')) {
                document.querySelectorAll('.grade-input').forEach(function(input) {
                    var randomGrade = Math.floor(Math.random() * 40) + 60; // 60-100
                    input.value = randomGrade;
                    var studentId = input.id.match(/grade-(\d+)/)[1];
                    updateStudentGrade(studentId);
                });
            }
        }

        function clearAllGrades() {
            if (confirm('Are you sure you want to clear all grade inputs?')) {
                document.querySelectorAll('.grade-input').forEach(function(input) {
                    input.value = '';
                    var studentId = input.id.match(/grade-(\d+)/)[1];
                    updateStudentGrade(studentId);
                });
            }
        }

        function validateAllGrades() {
            var errors = [];
            var gradeInputs = document.querySelectorAll('.grade-input');
            
            gradeInputs.forEach(function(input) {
                var score = parseFloat(input.value);
                if (input.value && (isNaN(score) || score < 0 || score > 100)) {
                    var studentId = input.id.match(/grade-(\d+)/)[1];
                    var row = document.getElementById('row-' + studentId);
                    var studentName = row.querySelector('td:nth-child(2) strong').textContent;
                    errors.push(studentName + ': Invalid grade (' + input.value + ')');
                }
            });
            
            if (errors.length > 0) {
                alert('Validation Errors:\n' + errors.join('\n'));
            } else {
                alert('All grades are valid!');
            }
        }

        // Initialize all grades on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.grade-input').forEach(function(input) {
                var studentId = input.id.match(/grade-(\d+)/)[1];
                updateStudentGrade(studentId);
            });
            updateClassSummary();
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('grade-input')) {
                var inputs = Array.from(document.querySelectorAll('.grade-input'));
                var currentIndex = inputs.indexOf(e.target);
                
                if (e.key === 'ArrowDown' && currentIndex < inputs.length - 1) {
                    e.preventDefault();
                    inputs[currentIndex + 1].focus();
                    inputs[currentIndex + 1].select();
                } else if (e.key === 'ArrowUp' && currentIndex > 0) {
                    e.preventDefault();
                    inputs[currentIndex - 1].focus();
                    inputs[currentIndex - 1].select();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentIndex < inputs.length - 1) {
                        inputs[currentIndex + 1].focus();
                        inputs[currentIndex + 1].select();
                    }
                }
            }
        });
    </script>

    <style>
        .grade-input:focus {
            background-color: #fff3cd;
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        
        .table-success {
            background-color: #d4edda !important;
        }
        
        .table-danger {
            background-color: #f8d7da !important;
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .table-hover tbody tr:hover {
            cursor: pointer;
        }
    </style>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>