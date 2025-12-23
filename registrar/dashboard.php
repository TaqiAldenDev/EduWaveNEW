<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Registrar';

// Get counts for statistics cards
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'Student' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $recent_enrollments = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_classes WHERE academic_year = YEAR(CURDATE())");
    $stmt->execute();
    $recent_transfers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE issue_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $recent_certificates = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'Student'");
    $stmt->execute();
    $total_students = $stmt->fetchColumn();
} catch (PDOException $e) {
    $recent_enrollments = 0;
    $recent_transfers = 0;
    $recent_certificates = 0;
    $total_students = 0;
}

// Get recent activities
try {
    // Recent student enrollments
    $stmt = $pdo->prepare("SELECT users.name, users.created_at FROM users WHERE role = 'Student' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $recent_enrollments_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent certificate issuances
    $stmt = $pdo->prepare("SELECT users.name, certificates.issue_date FROM certificates JOIN users ON certificates.student_id = users.id WHERE certificates.issue_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY certificates.issue_date DESC LIMIT 2");
    $stmt->execute();
    $recent_certificates_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine and sort all recent activities
    $all_activities = [];

    foreach ($recent_enrollments_data as $activity) {
        $all_activities[] = [
            'activity' => 'New Student Enrolled',
            'user' => $activity['name'],
            'date' => $activity['created_at'],
            'status' => 'Completed'
        ];
    }

    foreach ($recent_certificates_data as $activity) {
        $all_activities[] = [
            'activity' => 'Certificate Issued',
            'user' => $activity['name'],
            'date' => $activity['issue_date'],
            'status' => 'Completed'
        ];
    }

    // Sort by date descending
    usort($all_activities, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Limit to 4 most recent
    $recent_activities = array_slice($all_activities, 0, 4);

} catch (PDOException $e) {
    $recent_activities = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - EduWave</title>

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
                        <li class="sidebar-item active ">
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
                                <li class="submenu-item ">
                                    <a href="student_enrol.php">Enroll Student</a>
                                </li>
                                <li class="submenu-item">
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
                <h3>Registrar Dashboard</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-9">
                        <div class="row">
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon purple">
                                                    <i class="bi bi-person-plus"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Enrollments</h6>
                                                <h6 class="font-extrabold mb-0"><?= $recent_enrollments ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon blue">
                                                    <i class="bi bi-exchange"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Transfers</h6>
                                                <h6 class="font-extrabold mb-0"><?= $recent_transfers ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon green">
                                                    <i class="bi bi-award"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Certificates</h6>
                                                <h6 class="font-extrabold mb-0"><?= $recent_certificates ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 col-md-6">
                                <div class="card">
                                    <div class="card-body px-3 py-4-5">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="stats-icon red">
                                                    <i class="bi bi-people"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="text-muted font-semibold">Students</h6>
                                                <h6 class="font-extrabold mb-0"><?= $total_students ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Recent Activity</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-lg">
                                                <thead>
                                                    <tr>
                                                        <th>Activity</th>
                                                        <th>User</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($recent_activities)): ?>
                                                        <?php foreach ($recent_activities as $activity): ?>
                                                            <tr>
                                                                <td class="col-3">
                                                                    <h5 class="mb-0"><?= htmlspecialchars($activity['activity']) ?></h5>
                                                                </td>
                                                                <td class="col-auto">
                                                                    <p class="mb-0"><?= htmlspecialchars($activity['user']) ?></p>
                                                                </td>
                                                                <td class="col-auto">
                                                                    <p class="mb-0"><?= date('M j, g:i A', strtotime($activity['date'])) ?></p>
                                                                </td>
                                                                <td class="col-auto">
                                                                    <span class="badge bg-success"><?= htmlspecialchars($activity['status']) ?></span>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted">No recent activity</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3">
                        <div class="card">
                            <div class="card-body py-4 px-5">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xl">
                                        <img src="../dashboardassets/images/faces/2.jpg" alt="Registrar">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold"><?=$username?></h5>
                                        <h6 class="text-muted mb-0">Registrar</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4>Quick Actions</h4>
                            </div>
                            <div class="card-content pb-4">
                                <div class="d-grid gap-2">
                                    <a href="student_enrol.php" class="btn btn-primary">
                                        <i class="bi bi-person-plus me-2"></i>Enroll Student
                                    </a>
                                    <a href="student_transfer.php" class="btn btn-outline-primary">
                                        <i class="bi bi-exchange me-2"></i>Transfer Student
                                    </a>
                                    <a href="certificate_issue.php" class="btn btn-outline-primary">
                                        <i class="bi bi-award me-2"></i>Issue Certificate
                                    </a>
                                    <a href="#" class="btn btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Reports
                                    </a>
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

    <script src="../dashboardassets/vendors/apexcharts/apexcharts.js"></script>
    <script src="../dashboardassets/js/pages/dashboard.js"></script>

    <script src="../dashboardassets/js/main.js"></script>
</body>

</html>
