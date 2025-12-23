<?php
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Parent') {
    header('Location: ../login.php');
    exit();
}

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Parent';
$parent_id = $_SESSION['user_id'];

// Get parent's children
try {
    $children_stmt = $pdo->prepare('
        SELECT u.id as student_id, u.name as student_name, c.grade_name 
        FROM users u 
        JOIN parent_student ps ON u.id = ps.student_id 
        JOIN student_classes sc ON u.id = sc.student_id 
        JOIN classes c ON sc.class_id = c.id 
        WHERE ps.parent_id = ? AND u.role = "Student"
        ORDER BY u.name
    ');
    $children_stmt->execute([$parent_id]);
    $children = $children_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get grades for each child
    $child_grades = [];
    foreach ($children as $child) {
        $grades_stmt = $pdo->prepare('
            SELECT g.score, g.exam_type, g.date_given, s.name as subject_name 
            FROM grades g 
            JOIN subjects s ON g.subject_id = s.id 
            WHERE g.student_id = ? 
            ORDER BY g.date_given DESC 
            LIMIT 10
        ');
        $grades_stmt->execute([$child['student_id']]);
        $child_grades[$child['student_id']] = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $children = [];
    $child_grades = [];
}

// Handle AJAX requests for specific child grades
if (isset($_GET['child_id']) && isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    $child_id = $_GET['child_id'];
    
    if (isset($child_grades[$child_id])) {
        echo json_encode([
            'success' => true,
            'grades' => $child_grades[$child_id]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Child not found']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Grades - EduWave Parent</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../assets/logo.svg" type="image/x-icon">

    <style>
        .grade-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .grade-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .grade-badge {
            font-size: 0.75rem;
        }
        .subject-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
    </style>
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

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>My Children</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="my_children.php">View Children</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-mortarboard"></i>
                                <span>Academic Performance</span>
                            </a>
                            <ul class="submenu active">
                                <li class="submenu-item active">
                                    <a href="child_grades.php">Grades</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_attendance.php">Attendance</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_timetable.php">Timetable</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-file-text"></i>
                                <span>Reports</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="weekly_summary.php">Weekly Summary</a>
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
                <h3>Child Grades</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <!-- Child Selection -->
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Select Child</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($children as $index => $child): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card grade-card" onclick="loadChildGrades(<?= $child['student_id'] ?>)" style="border-left: 4px solid #<?= $index === 0 ? '435ebe' : ($index === 1 ? 'ffa726' : ($index === 2 ? '26c6da' : '#ec407a')) ?>">
                                                <div class="card-body text-center">
                                                    <h6 class="mb-2"><?= htmlspecialchars($child['student_name']) ?></h6>
                                                    <p class="text-muted mb-0"><?= htmlspecialchars($child['grade_name']) ?></p>
                                                    <div class="subject-icon bg-light mb-2">
                                                        <i class="bi bi-mortarboard text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grades Display -->
                    <div class="col-12">
                        <div id="gradesContainer">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Recent Grades</h5>
                                    <div class="ms-auto">
                                        <select class="form-select form-select-sm" id="timeFilter" onchange="loadChildGrades()">
                                            <option value="all">All Time</option>
                                            <option value="week">Last Week</option>
                                            <option value="month">Last Month</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="gradesDisplay">
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-arrow-up-circle" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3">Select a child to view grades</h5>
                                            <p class="text-muted">Click on any child card above to see their academic performance</p>
                                        </div>
                                    </div>
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
    
    <script>
        let currentChildId = null;

        function loadChildGrades(childId) {
            currentChildId = childId;
            
            // Update active child card
            document.querySelectorAll('.grade-card').forEach(card => {
                card.classList.remove('border-primary');
            });
            event.currentTarget.classList.add('border-primary');
            
            // Load grades via AJAX
            const timeFilter = document.getElementById('timeFilter').value;
            const url = `child_grades.php?child_id=${childId}&format=json`;
            if (timeFilter !== 'all') {
                // For time filtering, we'll filter on client side
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayGrades(data.grades);
                    } else {
                        displayError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading grades:', error);
                    displayError('Error loading grades');
                });
        }

        function displayGrades(grades) {
            const container = document.getElementById('gradesDisplay');
            
            if (grades.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Grades Available</h5>
                        <p class="text-muted">No grades have been recorded for this student yet.</p>
                    </div>
                `;
                return;
            }
            
            // Group grades by subject
            const subjectGroups = {};
            grades.forEach(grade => {
                if (!subjectGroups[grade.subject_name]) {
                    subjectGroups[grade.subject_name] = [];
                }
                subjectGroups[grade.subject_name].push(grade);
            });
            
            const gradesHtml = Object.entries(subjectGroups).map(([subjectName, subjectGrades]) => {
                const latestGrade = subjectGrades[0]; // Most recent
                const scoreColor = latestGrade.score >= 90 ? 'success' : (latestGrade.score >= 80 ? 'warning' : 'danger');
                
                return `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">${subjectName}</h6>
                                <span class="badge bg-${scoreColor}">${latestGrade.score}%</span>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Latest: ${latestGrade.exam_type}</small><br>
                                    <strong>${latestGrade.score}%</strong>
                                </div>
                                <div class="text-muted small">
                                    <small>Given: ${new Date(latestGrade.date_given).toLocaleDateString()}</small>
                                </div>
                                ${subjectGrades.length > 1 ? `
                                    <div class="mt-2">
                                        <small class="text-muted">View all ${subjectGrades.length} grades</small>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            container.innerHTML = gradesHtml;
        }

        function displayError(message) {
            const container = document.getElementById('gradesDisplay');
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #dc3545;"></i>
                    <h5 class="mt-3">Error</h5>
                    <p class="text-muted">${message}</p>
                </div>
            `;
        }
    </script>
</body>

</html>