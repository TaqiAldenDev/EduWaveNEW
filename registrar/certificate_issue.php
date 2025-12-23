<?php
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}
// Ensure username is available
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Registrar User';

$message = '';
$error = '';

// Handle certificate issuance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_certificate'])) {
    if (!empty($_POST['student_id']) && !empty($_POST['academic_year'])) {
        try {
            $student_id = $_POST['student_id'];
            $academic_year = $_POST['academic_year'];
            $issue_date = date('Y-m-d');

            // Check if certificate already exists for this student and academic year
            $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM certificates WHERE student_id = ? AND academic_year = ?');
            $check_stmt->execute([$student_id, $academic_year]);
            if ($check_stmt->fetchColumn() > 0) {
                $error = 'Certificate already exists for this student in the specified academic year.';
            } else {
                // Get student and class info
                $stmt = $pdo->prepare('SELECT users.name as student_name, classes.grade_name, classes.id as class_id FROM users JOIN student_classes ON users.id = student_classes.student_id JOIN classes ON student_classes.class_id = classes.id WHERE users.id = ? AND student_classes.academic_year = ?');
                $stmt->execute([$student_id, $academic_year]);
                $student_info = $stmt->fetch();
                
                if (!$student_info) {
                    $error = 'Student not found or not enrolled in the specified academic year.';
                } else {
                    $student_name = $student_info['student_name'];
                    $class_name = $student_info['grade_name'];
                    $class_id = $student_info['class_id'];

                    // PDF generation with improved design
                    require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $pdf->SetCreator(PDF_CREATOR);
                    $pdf->SetAuthor('EduWave School Management System');
                    $pdf->SetTitle('Graduation Certificate - ' . $student_name);
                    $pdf->SetSubject('Graduation Certificate');
                    
                    // Set margins
                    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                    
                    // Add page
                    $pdf->AddPage();
                    
                    // Set font
                    $pdf->SetFont('helvetica', '', 12);
                    
                    // Improved certificate HTML with better styling
                    $html = '
                    <style>
                        .certificate-header {
                            text-align: center;
                            margin-bottom: 30px;
                        }
                        .certificate-title {
                            font-size: 28px;
                            font-weight: bold;
                            color: #2c3e50;
                            margin-bottom: 10px;
                        }
                        .certificate-subtitle {
                            font-size: 20px;
                            color: #7f8c8d;
                            margin-bottom: 40px;
                        }
                        .certificate-body {
                            text-align: center;
                            font-family: serif;
                            line-height: 1.6;
                        }
                        .student-name {
                            font-size: 24px;
                            font-weight: bold;
                            color: #2c3e50;
                            margin: 20px 0;
                            text-decoration: underline;
                        }
                        .certificate-details {
                            font-size: 16px;
                            margin: 30px 0;
                        }
                        .certificate-footer {
                            text-align: center;
                            margin-top: 50px;
                        }
                        .signature-line {
                            border-bottom: 1px solid #000;
                            width: 200px;
                            margin: 0 auto;
                            margin-top: 40px;
                        }
                        .signature-text {
                            margin-top: 5px;
                            font-size: 12px;
                        }
                    </style>
                    
                    <div class="certificate-header">
                        <div class="certificate-title">EduWave Virtual School</div>
                        <div class="certificate-subtitle">Graduation Certificate</div>
                    </div>
                    
                    <div class="certificate-body">
                        <p>This is to certify that</p>
                        <div class="student-name">' . htmlspecialchars($student_name) . '</div>
                        <p>has successfully completed the academic requirements for</p>
                        <div class="certificate-details">
                            <strong>Grade Level:</strong> ' . htmlspecialchars($class_name) . '<br>
                            <strong>Academic Year:</strong> ' . htmlspecialchars($academic_year) . '<br>
                            <strong>Date of Completion:</strong> ' . date('F j, Y', strtotime($issue_date)) . '
                        </div>
                        <p>This certificate is awarded in recognition of the student\'s dedication, hard work, and successful completion of all academic requirements.</p>
                    </div>
                    
                    <div class="certificate-footer">
                        <div class="signature-line"></div>
                        <div class="signature-text">Registrar Signature</div>
                        <p style="margin-top: 20px;"><strong>Certificate ID:</strong> CERT-' . $student_id . '-' . $academic_year . '</p>
                    </div>';
                    
                    $pdf->writeHTML($html, true, false, true, false, '');
                    
                    // Ensure upload directory exists
                    $upload_dir = __DIR__ . '/../uploads/certificates/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = 'certificate_' . $student_id . '_' . $academic_year . '.pdf';
                    $file_path = $upload_dir . $file_name;
                    $pdf->Output($file_path, 'F');

                    // Insert into database
                    $stmt = $pdo->prepare('INSERT INTO certificates (student_id, issue_date, academic_year, class_id, issued_by, file_path) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$student_id, $issue_date, $academic_year, $class_id, $_SESSION['user_id'], 'uploads/certificates/' . $file_name]);
                    $message = 'Certificate issued successfully! <a href="uploads/certificates/' . $file_name . '" class="btn btn-sm btn-success ms-2" target="_blank"><i class="bi bi-download"></i> Download</a>';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error issuing certificate: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'Error generating PDF: ' . $e->getMessage();
        }
    } else {
        $error = 'Please select a student and academic year.';
    }
}

// Get students with their current class info
$students_stmt = $pdo->query('SELECT u.id, u.name, c.grade_name as current_class FROM users u LEFT JOIN student_classes sc ON u.id = sc.student_id AND sc.academic_year = ' . date('Y') . ' LEFT JOIN classes c ON sc.class_id = c.id WHERE u.role = "Student" ORDER BY u.name');
$students = $students_stmt->fetchAll();

// Get already issued certificates
$certificates_stmt = $pdo->query('SELECT c.id, c.issue_date, c.academic_year, c.file_path, u.name as student_name, cl.grade_name as class_name, issuer.name as issued_by_name FROM certificates c JOIN users u ON c.student_id = u.id JOIN classes cl ON c.class_id = cl.id LEFT JOIN users issuer ON c.issued_by = issuer.id ORDER BY c.issue_date DESC');
$certificates = $certificates_stmt->fetchAll();

// Get available academic years
$years_stmt = $pdo->query('SELECT DISTINCT academic_year FROM student_classes ORDER BY academic_year DESC');
$academic_years = $years_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Certificate - EduWave Registrar</title>

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
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-people"></i>
                                <span>Parent Management</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="parent_management.php">Manage Parents</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-exchange"></i>
                                <span>Student Transfer</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item">
                                    <a href="student_transfer.php">Transfer Student</a>
                                </li>
                            </ul>
                        </li>

                        <li class="sidebar-item  has-sub active">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-award"></i>
                                <span>Certificate Issuing</span>
                            </a>
                            <ul class="submenu ">
                                <li class="submenu-item active">
                                    <a href="certificate_issue.php">Issue Certificate</a>
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
                <h3>Certificate Issuing</h3>
            </div>
            <div class="page-content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <section class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Issue Graduation Certificate</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="student_id" class="form-label">Student</label>
                                                <select class="form-select" id="student_id" name="student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach ($students as $student): ?>
                                                        <option value="<?= $student['id'] ?>">
                                                            <?= htmlspecialchars($student['name']) ?>
                                                            <?php if ($student['current_class']): ?>
                                                                (<?= htmlspecialchars($student['current_class']) ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_year" class="form-label">Academic Year</label>
                                                <select class="form-select" id="academic_year" name="academic_year" required>
                                                    <option value="">Select Academic Year</option>
                                                    <?php foreach ($academic_years as $year): ?>
                                                        <option value="<?= $year['academic_year'] ?>" <?= $year['academic_year'] == date('Y') ? 'selected' : '' ?>>
                                                            <?= $year['academic_year'] ?>-<?= $year['academic_year'] + 1 ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="issue_certificate" class="btn btn-primary">
                                        <i class="bi bi-award"></i> Issue Certificate
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Issued Certificates</h4>
                                <div>
                                    <input type="text" class="form-control form-control-sm" id="certificateSearch" placeholder="Search certificates..." style="width: 200px;">
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($certificates)): ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> No certificates have been issued yet.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="certificatesTable">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Grade</th>
                                                    <th>Academic Year</th>
                                                    <th>Issue Date</th>
                                                    <th>Issued By</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($certificates as $cert): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($cert['student_name']) ?></td>
                                                        <td><?= htmlspecialchars($cert['class_name']) ?></td>
                                                        <td><?= $cert['academic_year'] ?>-<?= $cert['academic_year'] + 1 ?></td>
                                                        <td><?= date('M j, Y', strtotime($cert['issue_date'])) ?></td>
                                                        <td><?= htmlspecialchars($cert['issued_by_name'] ?: 'System') ?></td>
                                                        <td>
                                                            <a href="<?= $cert['file_path'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Download Certificate">
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                            <button class="btn btn-sm btn-outline-info" onclick="viewCertificateDetails(<?= $cert['id'] ?>)" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
    <script src="../dashboardassets/js/main.js"></script>
    
    <script>
        // Search functionality for certificates table
        document.getElementById('certificateSearch').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#certificatesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Function to view certificate details (can be expanded)
        function viewCertificateDetails(certificateId) {
            // This could open a modal with more details or show additional information
            alert('Certificate ID: ' + certificateId + '\n\nAdditional details can be shown in a modal here.');
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const studentId = document.getElementById('student_id').value;
            const academicYear = document.getElementById('academic_year').value;
            
            if (!studentId || !academicYear) {
                e.preventDefault();
                alert('Please select both a student and an academic year.');
            }
        });
    </script>
</body>

</html>
