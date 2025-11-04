<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'session.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generatePDF($filename, $html)
{
    // Set PDF options
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    // Initialize Dompdf
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');

    // Render the PDF
    $dompdf->render();

    // Output to browser (false = open in browser)
    $dompdf->stream($filename, ['Attachment' => false]);
}

// Only run the standalone report generation if this file is accessed directly (not included)
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    // Require login
    requireLogin();

    $user = getCurrentUser();
    $conn = getDBConnection();

    // ✅ Fixed query: Use actual columns from your DB schema
    // No user_id or created_at; use cashier_name and date_ordered
    $sql = "SELECT o.id, o.items, o.total_amount, o.cashier_name, o.date_ordered, o.voided 
            FROM orders o 
            ORDER BY o.date_ordered DESC";

    $result = $conn->query($sql);

    if (!$result) {
        die('Query error: ' . $conn->error);
    }

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    // Initialize Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $html = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
            h1 { text-align: center; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 6px; text-align: left; }
            th { background-color: #f2f2f2; }
            tr.voided { background-color: #ffcccc; }
        </style>
    </head>
    <body>
        <h1>Order History Report</h1>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Cashier</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';

    $i = 1;
    foreach ($orders as $row) {
        // ✅ Fixed: Check voided (0/1) instead of non-existent 'status'
        $voidClass = ($row['voided'] == 1) ? 'voided' : '';
        $status = ($row['voided'] == 1) ? 'Voided' : 'Active';
        $html .= "
            <tr class='{$voidClass}'>
                <td>{$i}</td>
                <td>{$row['id']}</td>
                <td>{$row['cashier_name']}</td>
                <td>₱" . number_format($row['total_amount'], 2) . "</td>
                <td>{$status}</td>
                <td>{$row['date_ordered']}</td>
            </tr>";
        $i++;
    }

    $html .= '
            </tbody>
        </table>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream("order_history_report.pdf", ["Attachment" => false]);
    exit;
}
