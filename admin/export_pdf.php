<?php
require_once __DIR__ . '/../includes/config.php';

// Define required constant to prevent TCPDF config issues
define('K_TCPDF_EXTERNAL_CONFIG', false);

// Check if TCPDF exists and load it properly
if (!file_exists(__DIR__ . '/../includes/tcpdf/tcpdf.php')) {
    die('TCPDF library not found. Please ensure TCPDF is properly installed.');
}

require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$type = $_REQUEST['type'] ?? '';

switch($type) {
    case 'users':
        exportUsersPDF();
        break;
    case 'classes':
        exportClassesPDF();
        break;
    case 'subjects':
        exportSubjectsPDF();
        break;
    case 'library':
        exportLibraryPDF();
        break;
    case 'events':
        exportEventsPDF();
        break;
    default:
        die('Invalid export type: ' . htmlspecialchars($type));
}

function exportUsersPDF() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document properties
        $pdf->SetCreator('EduWave School Management System');
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Users List Report');
        $pdf->SetSubject('Users Management Report');
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Build HTML content
        $html = '
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">EduWave - Users Management Report</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <table border="1" style="width: 100%; border-collapse: collapse; margin: 0 auto;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px;">Name</th>
                    <th style="padding: 8px;">Email</th>
                    <th style="padding: 8px; text-align: center;">Role</th>
                    <th style="padding: 8px; text-align: center;">Created</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($users as $user) {
            // Set role color
            $roleColor = '#000000';
            if ($user['role'] === 'Admin') $roleColor = '#007bff';
            elseif ($user['role'] === 'Teacher') $roleColor = '#ffc107';
            elseif ($user['role'] === 'Student') $roleColor = '#17a2b8';
            elseif ($user['role'] === 'Parent') $roleColor = '#6c757d';
            elseif ($user['role'] === 'Registrar') $roleColor = '#28a745';
            
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($user['id']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($user['name']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($user['email']) . '</td>
                    <td style="padding: 6px; text-align: center;">
                        <span style="color: ' . $roleColor . '; font-weight: bold; padding: 2px 8px; border-radius: 3px;">' . htmlspecialchars($user['role']) . '</span>
                    </td>
                    <td style="padding: 6px; text-align: center;">' . date('M j, Y', strtotime($user['created_at'])) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <p style="text-align: center; color: #95a5a6; font-size: 10px; margin-top: 20px;">Total Users: ' . count($users) . '</p>';
        
        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output PDF
        $fileName = 'users_list_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($fileName, 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating PDF: ' . $e->getMessage());
    }
}

function exportClassesPDF() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT * FROM classes ORDER BY grade_name');
        $classes = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('EduWave School Management System');
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Classes List Report');
        $pdf->SetSubject('Classes Management Report');
        
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
        $html = '
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">EduWave - Classes Management Report</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <table border="1" style="width: 100%; border-collapse: collapse; margin: 0 auto;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px; text-align: center;">Grade</th>
                    <th style="padding: 8px; text-align: center;">Section</th>
                    <th style="padding: 8px; text-align: center;">Room</th>
                    <th style="padding: 8px; text-align: center;">Capacity</th>
                    <th style="padding: 8px;">Teacher</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($classes as $class) {
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['id']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['grade_name']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['section'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['room_number'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($class['capacity'] ?? 'N/A') . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($class['teacher_name'] ?? 'N/A') . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <p style="text-align: center; color: #95a5a6; font-size: 10px; margin-top: 20px;">Total Classes: ' . count($classes) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $fileName = 'classes_list_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($fileName, 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating PDF: ' . $e->getMessage());
    }
}

function exportSubjectsPDF() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT * FROM subjects ORDER BY subject_name');
        $subjects = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('EduWave School Management System');
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Subjects List Report');
        $pdf->SetSubject('Subjects Management Report');
        
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
        $html = '
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">EduWave - Subjects Management Report</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <table border="1" style="width: 100%; border-collapse: collapse; margin: 0 auto;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px; text-align: center;">Subject Code</th>
                    <th style="padding: 8px;">Subject Name</th>
                    <th style="padding: 8px; text-align: center;">Grade Level</th>
                    <th style="padding: 8px; text-align: center;">Credits</th>
                    <th style="padding: 8px;">Teacher</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($subjects as $subject) {
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($subject['id']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($subject['subject_code'] ?? 'N/A') . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($subject['subject_name']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($subject['grade_level'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($subject['credits'] ?? 'N/A') . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($subject['teacher_name'] ?? 'N/A') . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <p style="text-align: center; color: #95a5a6; font-size: 10px; margin-top: 20px;">Total Subjects: ' . count($subjects) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $fileName = 'subjects_list_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($fileName, 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating PDF: ' . $e->getMessage());
    }
}

function exportLibraryPDF() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, title, category, file_path, uploaded_by, uploaded_at FROM library_books ORDER BY title');
        $books = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('EduWave School Management System');
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Library Books Report');
        $pdf->SetSubject('Library Management Report');
        
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
$html = '
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">EduWave - Library Books Report</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <table border="1" style="width: 100%; border-collapse: collapse; margin: 0 auto;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px;">Title</th>
                    <th style="padding: 8px; text-align: center;">Category</th>
                    <th style="padding: 8px;">File Path</th>
                    <th style="padding: 8px; text-align: center;">Uploaded By</th>
                    <th style="padding: 8px; text-align: center;">Upload Date</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($books as $book) {
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($book['id']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($book['title']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($book['category'] ?? 'N/A') . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($book['file_path'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($book['uploaded_by'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . date('M j, Y', strtotime($book['uploaded_at'])) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <p style="text-align: center; color: #95a5a6; font-size: 10px; margin-top: 20px;">Total Books: ' . count($books) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $fileName = 'library_books_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($fileName, 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating PDF: ' . $e->getMessage());
    }
}

function exportEventsPDF() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, title, start_date, end_date, user_id, class_id FROM calendar_events ORDER BY start_date');
        $events = $stmt->fetchAll();
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('EduWave School Management System');
        $pdf->SetAuthor('EduWave');
        $pdf->SetTitle('Calendar Events Report');
        $pdf->SetSubject('Calendar Events Report');
        
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
$html = '
        <h2 style="text-align: center; color: #2c3e50; margin-bottom: 20px;">EduWave - Calendar Events Report</h2>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 30px;">Generated on: ' . date('F j, Y H:i:s') . '</p>
        <table border="1" style="width: 100%; border-collapse: collapse; margin: 0 auto;">
            <thead>
                <tr style="background-color: #3498db; color: white; font-weight: bold;">
                    <th style="padding: 8px; text-align: center;">ID</th>
                    <th style="padding: 8px;">Event Title</th>
                    <th style="padding: 8px; text-align: center;">Start Date</th>
                    <th style="padding: 8px; text-align: center;">End Date</th>
                    <th style="padding: 8px; text-align: center;">Created By</th>
                    <th style="padding: 8px; text-align: center;">Class</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($events as $event) {
            $html .= '
                <tr>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($event['id']) . '</td>
                    <td style="padding: 6px;">' . htmlspecialchars($event['title']) . '</td>
                    <td style="padding: 6px; text-align: center;">' . date('M j, Y', strtotime($event['start_date'])) . '</td>
                    <td style="padding: 6px; text-align: center;">' . ($event['end_date'] ? date('M j, Y', strtotime($event['end_date'])) : 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($event['user_id'] ?? 'N/A') . '</td>
                    <td style="padding: 6px; text-align: center;">' . htmlspecialchars($event['class_id'] ?? 'N/A') . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        <p style="text-align: center; color: #95a5a6; font-size: 10px; margin-top: 20px;">Total Events: ' . count($events) . '</p>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $fileName = 'calendar_events_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($fileName, 'D');
        exit;
        
    } catch (Exception $e) {
        die('Error generating PDF: ' . $e->getMessage());
    }
}
?>