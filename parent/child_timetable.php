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
    
    // Get timetable data for each child
    $child_timetables = [];
    foreach ($children as $child) {
        $timetable_stmt = $pdo->prepare('
            SELECT s.*, sub.name as subject_name, u.name as teacher_name 
            FROM schedule s 
            JOIN subjects sub ON s.subject_id = sub.id 
            JOIN users u ON s.teacher_id = u.id 
            WHERE s.class_id = (SELECT class_id FROM student_classes WHERE student_id = ?)
            ORDER BY s.day_of_week, s.start_time
        ');
        $timetable_stmt->execute([$child['student_id']]);
        $child_timetables[$child['student_id']] = $timetable_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $children = [];
    $child_timetables = [];
}

// Handle AJAX requests for specific child timetable
if (isset($_GET['child_id']) && isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    $child_id = $_GET['child_id'];
    
    if (isset($child_timetables[$child_id])) {
        echo json_encode([
            'success' => true,
            'timetable' => $child_timetables[$child_id]
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
    <title>Child Timetable - EduWave Parent</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../assets/logo.svg" type="image/x-icon">

    <style>
        .timetable-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        .timetable-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left-color: #435ebe;
        }
        .time-slot {
            padding: 8px 12px;
            border-radius: 8px;
            margin: 4px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .subject-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
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
                                <li class="submenu-item">
                                    <a href="child_grades.php">Grades</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="child_attendance.php">Attendance</a>
                                </li>
                                <li class="submenu-item active">
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
                <h3>Child Timetable</h3>
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
                                            <div class="card timetable-card" onclick="loadChildTimetable(<?= $child['student_id'] ?>)" style="border-left: 4px solid #<?= $index === 0 ? '435ebe' : ($index === 1 ? 'ffa726' : ($index === 2 ? '26c6da' : '#ec407a')) ?>">
                                                <div class="card-body text-center">
                                                    <h6 class="mb-2"><?= htmlspecialchars($child['student_name']) ?></h6>
                                                    <p class="text-muted mb-0"><?= htmlspecialchars($child['grade_name']) ?></p>
                                                    <div class="subject-icon bg-light mb-2">
                                                        <i class="bi bi-calendar3 text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timetable Display -->
                    <div class="col-12">
                        <div id="timetableContainer">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Weekly Timetable</h5>
                                </div>
                                <div class="card-body">
                                    <div id="timetableDisplay">
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-calendar3" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3">Select a child to view timetable</h5>
                                            <p class="text-muted">Click on any child card above to see their weekly schedule</p>
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

        function loadChildTimetable(childId) {
            currentChildId = childId;
            
            // Update active child card
            document.querySelectorAll('.timetable-card').forEach(card => {
                card.classList.remove('border-primary');
            });
            event.currentTarget.classList.add('border-primary');
            
            // Load timetable via AJAX
            const url = `child_timetable.php?child_id=${childId}&format=json`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTimetable(data.timetable);
                    } else {
                        displayError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading timetable:', error);
                    displayError('Error loading timetable');
                });
        }

        function displayTimetable(timetable) {
            const container = document.getElementById('timetableDisplay');
            
            if (timetable.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Timetable Available</h5>
                        <p class="text-muted">No timetable has been set up for this student yet.</p>
                    </div>
                `;
                return;
            }
            
            // Group timetable by day
            const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            const timetableHtml = daysOfWeek.map(day => {
                const daySchedule = timetable.filter(item => item.day_of_week === day);
                
                if (daySchedule.length === 0) {
                    return `
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted">${day}</h6>
                                <div class="text-muted text-center">No classes scheduled</div>
                            </div>
                        </div>
                    `;
                }
                
                return `
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="mb-3">${day}</h6>
                            <div class="row">
                                ${daySchedule.map(item => {
                                    const subjectColors = {
                                        'Mathematics': '#e74c3c',
                                        'Science': '#28a745',
                                        'English': '#20c997',
                                        'History': '#6f42c1',
                                        'Geography': '#198754'
                                    };
                                    const color = subjectColors[item.subject_name] || '#6c757d';
                                    
                                    return `
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="time-slot">
                                                <div class="subject-badge" style="background-color: ${color};">
                                                    ${item.subject_name}
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-white">${item.start_time} - ${item.end_time}</small>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-white">Teacher: ${item.teacher_name}</small>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            container.innerHTML = timetableHtml;
        }

        function displayError(message) {
            const container = document.getElementById('timetableDisplay');
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