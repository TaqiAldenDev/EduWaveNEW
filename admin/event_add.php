<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin User';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['title']) && !empty($_POST['start_date'])) {
        $title = $_POST['title'];
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
        $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;

        try {
            $stmt = $pdo->prepare('INSERT INTO calendar_events (title, start_date, end_date, user_id, class_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$title, $start_date, $end_date, $user_id, $class_id]);
            $message = 'Event added successfully!';
        } catch (PDOException $e) {
            $message = 'Error adding event.';
        }
    } else {
        $message = 'Please fill in all required fields.';
    }
}

$stmt_users = $pdo->query('SELECT id, name FROM users');
$users = $stmt_users->fetchAll();

$stmt_classes = $pdo->query('SELECT id, grade_name FROM classes');
$classes = $stmt_classes->fetchAll();

// Get existing events with additional information
$events_stmt = $pdo->query('
    SELECT
        ce.*,
        u.name as user_name,
        c.grade_name as class_name
    FROM calendar_events ce
    LEFT JOIN users u ON ce.user_id = u.id
    LEFT JOIN classes c ON ce.class_id = c.id
    ORDER BY ce.start_date DESC
');
$events = $events_stmt->fetchAll();

// Get event statistics
$event_count = $pdo->query('SELECT COUNT(*) FROM calendar_events')->fetchColumn();
$upcoming_events = $pdo->query('SELECT COUNT(*) FROM calendar_events WHERE start_date >= CURDATE()')->fetchColumn();
$today_events = $pdo->query('SELECT COUNT(*) FROM calendar_events WHERE start_date = CURDATE()')->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Calendar Event - EduWave Admin</title>

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

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span>Classes & Subjects</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="classes.php">Classes</a>
                                </li>
                                <li class="submenu-item ">
                                    <a href="subject_edit.php">Subjects</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item">
                            <a href="library_add.php" class='sidebar-link'>
                                <i class="bi bi-book"></i>
                                <span>Library Management</span>
                            </a>
                        </li>

                        <li class="sidebar-item active">
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
                <h3>Calendar Events</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Add Calendar Event</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($message): ?>
                                    <div class="alert alert-info"><?= $message ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="end_date" class="form-label">End Date (Optional)</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="user_id" class="form-label">Assign to User</label>
                                            <select class="form-select" id="user_id" name="user_id">
                                                <option value="">All Users</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="class_id" class="form-label">Assign to Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                <option value="">All Classes</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['grade_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Event</button>
                                </form>
                            </div>
                        </div>

                        <!-- Event Statistics -->
                        <div class="row mb-4">
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon purple">
                                                    <i class="bi bi-calendar-event"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Total Events</h6>
                                                <h6 class="font-extrabold mb-0"><?= $event_count ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon blue">
                                                    <i class="bi bi-calendar-check"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Upcoming</h6>
                                                <h6 class="font-extrabold mb-0"><?= $upcoming_events ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon green">
                                                    <i class="bi bi-calendar-day"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Today</h6>
                                                <h6 class="font-extrabold mb-0"><?= $today_events ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4>Event Calendar</h4>
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
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="eventsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Title</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Assigned To</th>
                                                <th>Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($events as $event): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($event['id']) ?></td>
                                                    <td><?= htmlspecialchars($event['title']) ?></td>
                                                    <td><?= date('M j, Y', strtotime($event['start_date'])) ?></td>
                                                    <td>
                                                        <?php if ($event['end_date']): ?>
                                                            <?= date('M j, Y', strtotime($event['end_date'])) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($event['user_name']): ?>
                                                            <span class="badge bg-primary"><?= htmlspecialchars($event['user_name']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">All Users</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($event['class_name']): ?>
                                                            <span class="badge bg-info"><?= htmlspecialchars($event['class_name']) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">All Classes</span>
                                                        <?php endif; ?>
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

    <script>
        function exportTable(type) {
            const fileName = 'calendar_events_' + new Date().toISOString().slice(0, 10);
            
            const options = {
                tableName: 'Calendar Events',
                worksheetName: 'Events',
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
                $('#eventsTable').tableExport(options);
                break;
            case 'txt':
                options.type = 'txt';
                $('#eventsTable').tableExport(options);
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
            input.value = 'events';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>