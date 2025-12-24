<?php
require_once __DIR__ . '/../includes/config.php';

// Define required constant to prevent TCPDF config issues
define('K_TCPDF_EXTERNAL_CONFIG', false);

require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Registrar') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';

switch($type) {
    case 'students':
        exportStudentsPDF();
        break;
    case 'parents':
        exportParentsPDF();
        break;
    default:
        die('Invalid export type: ' . htmlspecialchars($type));
}

function exportStudentsPDF() {
    global $pdo;
    
    try {
        $query = 'SELECT u.id, u.name, u.email, u.created_at, c.grade_name, s.section_name 
                  FROM users u 
                  LEFT JOIN student_classes sc ON u.id = sc.student_id 
                  LEFT JOIN classes c ON sc.class_id = c.id 
                  LEFT JOIN sections s ON sc.section_id = s.id 
                  WHERE u.role = "Student" 
                  ORDER BY u.name';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Students Management Report');
        $pdf->SetSubject('Students Management Report');
        
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
        <h2 style="text-align: center; color: #2c3e50;">EduWave - Students Management Report</h2>
        <p style="text-align: center; color: #7f8c8d;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <br>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px;">Name</th>
                    <th style="padding: 8px;">Email</th>
                    <th style="padding: 8px;">Class</th>
                    <th style="padding: 8px;">Section</th>
                    <th style="padding: 8px; text-align: center;">Joined Date</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($students as $student) {
            $classColor = $student['grade_name'] ? '#28a745' : '#dc3545';
            
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($student['id']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($student['name']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($student['email']) . '</td>
                    <td style="padding: 6px; color: ' . $classColor . '; font-weight: bold;">' . htmlspecialchars($student['grade_name'] ?? 'N/A') . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($student['section_name'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . date('M j, Y', strtotime($student['created_at'])) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <br>
        <p style="text-align: center; color: #95a5a6; font-size: 10px;">Total Students: ' . count($students) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Output('students_management_' . date('Y-m-d') . '.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating report: ' . $e->getMessage());
    }
}

function exportParentsPDF() {
    global $pdo;
    
    try {
        $query = 'SELECT u.id, u.name, u.email, u.created_at, COUNT(ps.student_id) as children_count 
                  FROM users u 
                  LEFT JOIN parent_student ps ON u.id = ps.parent_id 
                  WHERE u.role = "Parent" 
                  GROUP BY u.id 
                  ORDER BY u.name';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $parents = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Parents Management Report');
        $pdf->SetSubject('Parents Management Report');
        
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
        <h2 style="text-align: center; color: #2c3e50;">EduWave - Parents Management Report</h2>
        <p style="text-align: center; color: #7f8c8d;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <br>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px;">Name</th>
                    <th style="padding: 8px;">Email</th>
                    <th style="padding: 8px; text-align: center;">Children Count</th>
                    <th style="padding: 8px; text-align: center;">Registration Date</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($parents as $parent) {
            $childrenColor = $parent['children_count'] > 0 ? '#28a745' : '#ffc107';
            
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($parent['id']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($parent['name']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($parent['email']) . '</td>
                    <td style="padding: 6px; text-align: center; color: ' . $childrenColor . '; font-weight: bold;">' . $parent['children_count'] . '</td>
                    <td style="padding: 6px; text-align: center;">' . date('M j, Y', strtotime($parent['created_at'])) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <br>
        <p style="text-align: center; color: #95a5a6; font-size: 10px;">Total Parents: ' . count($parents) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Output('parents_management_' . date('Y-m-d') . '.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating report: ' . $e->getMessage());
    }
}
?>