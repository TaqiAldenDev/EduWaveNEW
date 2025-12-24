<?php
require_once __DIR__ . '/../includes/config.php';

// Define required constant to prevent TCPDF config issues
define('K_TCPDF_EXTERNAL_CONFIG', false);

require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Teacher') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';

switch($type) {
    case 'classes':
        exportTeacherClassesPDF();
        break;
    default:
        die('Invalid export type');
}

function exportTeacherClassesPDF() {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT
            classes.id as class_id,
            classes.grade_name,
            subjects.id as subject_id,
            subjects.name AS subject_name,
            (SELECT COUNT(sc.student_id) FROM student_classes sc WHERE sc.class_id = classes.id AND sc.academic_year = YEAR(CURDATE())) AS student_count,
            (SELECT COUNT(*) FROM attendance a WHERE a.class_id = classes.id AND a.subject_id = subjects.id AND a.attend_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS attendance_records,
            (SELECT COUNT(*) FROM grades g WHERE g.subject_id = subjects.id) AS grade_records
        FROM teacher_assignments
        JOIN classes ON teacher_assignments.class_id = classes.id
        JOIN subjects ON teacher_assignments.subject_id = subjects.id
        WHERE teacher_assignments.teacher_id = ?
        ORDER BY classes.grade_name, subjects.name
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $classes = $stmt->fetchAll();
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('EduWave');
    $pdf->SetTitle('Teacher Classes and Subjects');
    $pdf->SetSubject('Teacher Classes Report');
    
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    $pdf->SetFont('helvetica', '', 10);
    
    $pdf->AddPage();
    
    $html = '
    <h2 style="text-align: center; color: #2c3e50;">EduWave - My Classes and Subjects Report</h2>
    <p style="text-align: center; color: #7f8c8d;">Generated on: ' . date('F j, Y H:i:s') . '</p>
    <br>
    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #3498db; color: white; font-weight: bold;">
                <th style="padding: 8px; text-align: center;">Class ID</th>
                <th style="padding: 8px;">Class</th>
                <th style="padding: 8px;">Subject</th>
                <th style="padding: 8px; text-align: center;">Current Students</th>
                <th style="padding: 8px; text-align: center;">Attendance Records (30 days)</th>
                <th style="padding: 8px; text-align: center;">Grade Records</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($classes as $class) {
        $studentColor = $class['student_count'] > 0 ? '#28a745' : '#dc3545';
        $attendanceColor = $class['attendance_records'] > 0 ? '#17a2b8' : '#dc3545';
        $gradeColor = $class['grade_records'] > 0 ? '#ffc107' : '#dc3545';
        
        $html .= '
            <tr>
                <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['class_id']) . '</td>
                <td style="padding: 6px;">' . htmlspecialchars($class['grade_name']) . '</td>
                <td style="padding: 6px;">' . htmlspecialchars($class['subject_name']) . '</td>
                <td style="padding: 6px; text-align: center; color: ' . $studentColor . '; font-weight: bold;">' . $class['student_count'] . '</td>
                <td style="padding: 6px; text-align: center; color: ' . $attendanceColor . '; font-weight: bold;">' . $class['attendance_records'] . '</td>
                <td style="padding: 6px; text-align: center; color: ' . $gradeColor . '; font-weight: bold;">' . $class['grade_records'] . '</td>
            </tr>';
    }
    
    $html .= '
        </tbody>
    </table>
    <br>
    <p style="text-align: center; color: #95a5a6; font-size: 10px;">Total Classes: ' . count($classes) . '</p>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output('teacher_classes_' . date('Y-m-d') . '.pdf', 'D');
    exit;
}
?>