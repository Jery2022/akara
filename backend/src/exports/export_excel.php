<?php
require '../vendors/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nom');
$sheet->setCellValue('C1', 'Quantité');

// Exemple de données
$data = [[1, 'Ciment', 12], [2, 'Sable', 8]];
foreach ($data as $i => $row) {
    $sheet->setCellValue('A'.($i+2), $row[0]);
    $sheet->setCellValue('B'.($i+2), $row[1]);
    $sheet->setCellValue('C'.($i+2), $row[2]);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="stocks.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>