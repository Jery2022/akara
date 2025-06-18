<?php
require '../vendors/autoload.php';

use Dompdf\Dompdf;

$html = '
<h1>Stocks</h1>
<table border="1">
  <tr><th>Nom</th><th>Quantité</th></tr>
  <tr><td>Ciment</td><td>12</td></tr>
  <tr><td>Sable</td><td>8</td></tr>
</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("stocks.pdf", ["Attachment" => false]);
?>