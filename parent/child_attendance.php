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
    
    // Get attendance data for each child
    $child_attendance = [];
    foreach ($children as $child) {
        $attendance_stmt = $pdo->prepare('
            SELECT a.attend_date, a.status, s.name as subject_name 
            FROM attendance a 
            LEFT JOIN subjects s ON a.subject_id = s.id 
            WHERE a.student_id = ? 
            ORDER BY a.attend_date DESC 
            LIMIT 30
        ');
        $attendance_stmt->execute([$child['student_id']]);
        $child_attendance[$child['student_id']] = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $children = [];
    $child_attendance = [];
}

// Handle AJAX requests for specific child attendance
if (isset($_GET['child_id']) && isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    $child_id = $_GET['child_id'];
    
    if (isset($child_attendance[$child_id])) {
        echo json_encode([
            'success' => true,
            'attendance' => $child_attendance[$child_id]
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
    <title>Child Attendance - EduWave Parent</title>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../dashboardassets/css/bootstrap.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="../dashboardassets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="../dashboardassets/css/app.css">
    <link rel="shortcut icon" href="../assets/logo.svg" type="image/x-icon">

    <style>
        .attendance-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .attendance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .present { background-color: #28a745; }
        .absent { background-color: #dc3545; }
        .attendance-chart {
            max-height: 300px;
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
                                <li class="submenu-item active">
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
                <h3>Child Attendance</h3>
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
                                            <div class="card attendance-card" onclick="loadChildAttendance(<?= $child['student_id'] ?>)" style="border-left: 4px solid #<?= $index === 0 ? '435ebe' : ($index === 1 ? 'ffa726' : ($index === 2 ? '26c6da' : '#ec407a')) ?>">
                                                <div class="card-body text-center">
                                                    <h6 class="mb-2"><?= htmlspecialchars($child['student_name']) ?></h6>
                                                    <p class="text-muted mb-0"><?= htmlspecialchars($child['grade_name']) ?></p>
                                                    <div class="subject-icon bg-light mb-2">
                                                        <i class="bi bi-calendar-check text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Display -->
                    <div class="col-12">
                        <div id="attendanceContainer">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Attendance Records</h5>
                                    <div class="ms-auto">
                                        <select class="form-select form-select-sm" id="monthFilter" onchange="loadChildAttendance()">
                                            <option value="all">All Time</option>
                                            <option value="month">This Month</option>
                                            <option value="week">This Week</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <canvas id="attendanceChart" class="attendance-chart"></canvas>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h6 class="text-muted">Attendance Summary</h6>
                                                <div id="attendanceStats">
                                                    <p class="text-muted">Select a child to view attendance</p>
                                                </div>
                                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        let currentChildId = null;
        let attendanceChart = null;

        function loadChildAttendance(childId) {
            currentChildId = childId;
            
            // Update active child card
            document.querySelectorAll('.attendance-card').forEach(card => {
                card.classList.remove('border-primary');
            });
            event.currentTarget.classList.add('border-primary');
            
            // Load attendance via AJAX
            const monthFilter = document.getElementById('monthFilter').value;
            const url = `child_attendance.php?child_id=${childId}&format=json`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAttendance(data.attendance);
                    } else {
                        displayError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading attendance:', error);
                    displayError('Error loading attendance');
                });
        }

        function displayAttendance(attendance) {
            const container = document.getElementById('attendanceStats');
            
            if (attendance.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Attendance Records</h5>
                        <p class="text-muted">No attendance has been recorded for this student yet.</p>
                    </div>
                `;
                updateAttendanceChart([], []);
                return;
            }
            
            // Calculate statistics
            const presentCount = attendance.filter(a => a.status === 'Present').length;
            const absentCount = attendance.filter(a => a.status === 'Absent').length;
            const totalCount = attendance.length;
            const attendanceRate = totalCount > 0 ? ((presentCount / totalCount) * 100).toFixed(1) : 0;
            
            // Prepare chart data
            const last30Days = attendance.slice(0, 30);
            const dates = [...new Set(last30Days.map(a => a.attend_date))].sort();
            const presentData = dates.map(date => {
                const record = last30Days.find(a => a.attend_date === date);
                return record ? (record.status === 'Present' ? 1 : 0) : 0;
            });
            
            // Update statistics display
            container.innerHTML = `
                <div class="mb-3">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success mb-0">${presentCount}</h3>
                                    <p class="text-muted mb-0">Days Present</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h3 class="text-danger mb-0">${absentCount}</h3>
                                        <p class="text-muted mb-0">Days Absent</p>
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h3 class="text-primary mb-0">${totalCount}</h3>
                                        <p class="text-muted mb-0">Total Days</p>
                                    </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h3 class="text-warning mb-0">${attendanceRate}%</h3>
                                        <p class="text-muted mb-0">Attendance Rate</p>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-center mb-3">Attendance Trend - Last 30 Days</h6>
                        </div>
                    </div>
                </div>
            `;
            
            updateAttendanceChart(dates, presentData);
        }

        function updateAttendanceChart(labels, data) {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            if (attendanceChart) {
                attendanceChart.destroy();
            }
            
            attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.map(date => new Date(date).toLocaleDateString()),
                    datasets: [{
                        label: 'Attendance',
                        data: data,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Daily Attendance Pattern'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1.2,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value === 0 ? 'Absent' : (value === 1 ? 'Present' : '');
                                }
                            }
                        }
                    }
                }
            });
        }

        function displayError(message) {
            const container = document.getElementById('attendanceStats');
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