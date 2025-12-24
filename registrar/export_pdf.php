<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit();
}

$type = $_GET['type'] ?? '';

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
        
        // Start output buffering
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Students Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #3498db; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border: 1px solid #ddd; }
                .enrolled { color: #28a745; font-weight: bold; }
                .not-enrolled { color: #dc3545; font-style: italic; }
                .footer { text-align: center; margin-top: 20px; color: #95a5a6; font-size: 12px; }
            </style>
        </head>
        <body>
            <h1>EduWave - Students Management Report</h1>
            <p style="text-align: center; color: #7f8c8d;">Generated on: <?php echo date('F j, Y H:i:s'); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td class="<?php echo $student['grade_name'] ? 'enrolled' : 'not-enrolled'; ?>">
                            <?php echo htmlspecialchars($student['grade_name'] ?? 'N/A'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                        <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="footer">
                <p>Total Students: <?php echo count($students); ?></p>
                <p>© 2025 EduWave - Students Management Report</p>
            </div>
        </body>
        </html>
        <?php
        
        $html = ob_get_clean();
        
        // Set headers to force download
        $fileName = 'students_management_' . date('Y-m-d') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($html));
        
        echo $html;
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
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Parents Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #3498db; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border: 1px solid #ddd; }
                .has-children { color: #28a745; font-weight: bold; }
                .no-children { color: #ffc107; font-style: italic; }
                .footer { text-align: center; margin-top: 20px; color: #95a5a6; font-size: 12px; }
            </style>
        </head>
        <body>
            <h1>EduWave - Parents Management Report</h1>
            <p style="text-align: center; color: #7f8c8d;">Generated on: <?php echo date('F j, Y H:i:s'); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Children Count</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parents as $parent): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($parent['id']); ?></td>
                        <td><?php echo htmlspecialchars($parent['name']); ?></td>
                        <td><?php echo htmlspecialchars($parent['email']); ?></td>
                        <td class="<?php echo $parent['children_count'] > 0 ? 'has-children' : 'no-children'; ?>">
                            <?php echo $parent['children_count']; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($parent['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="footer">
                <p>Total Parents: <?php echo count($parents); ?></p>
                <p>© 2025 EduWave - Parents Management Report</p>
            </div>
        </body>
        </html>
        <?php
        
        $html = ob_get_clean();
        
        // Set headers to force download
        $fileName = 'parents_management_' . date('Y-m-d') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($html));
        
        echo $html;
        exit;
        
    } catch (Exception $e) {
        die('Error generating report: ' . $e->getMessage());
    }
}
?>