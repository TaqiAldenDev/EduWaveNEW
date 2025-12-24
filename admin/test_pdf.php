<?php
require_once __DIR__ . '/../includes/config.php';

// Define required constant to prevent TCPDF config issues
define('K_TCPDF_EXTERNAL_CONFIG', false);

require_once __DIR__ . '/../includes/tcpdf/tcpdf.php';

// Simple test to verify PDF generation works
try {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('EduWave Test');
    $pdf->SetTitle('PDF Generation Test');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->AddPage();
    
    $html = '
    <h2 style="text-align: center; color: #2c3e50;">PDF Generation Test</h2>
    <p style="text-align: center;">This is a test to verify PDF generation is working correctly.</p>
    <p style="text-align: center;">Generated on: ' . date('F j, Y H:i:s') . '</p>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $fileName = 'pdf_test_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($fileName, 'D');
    echo "PDF generated successfully: " . $fileName;
    
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage();
}
?>