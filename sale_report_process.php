<?php
$page_title = 'Reporte de Ventas';
require_once('includes/load.php');
page_require_level(3);

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_POST['submit'])) {

    global $db;

    $start_date  = date("Y-m-d", strtotime($_POST['start-date']));
    $end_date    = date("Y-m-d", strtotime($_POST['end-date']));

    $sql = "
        SELECT 
            s.id AS sale_id,
            DATE(s.date) AS fecha_venta,
            dv.producto_id,
            dv.cantidad,
            dv.precio_unitario,
            p.brand,
            p.model
        FROM sales s
        JOIN detalle_venta dv ON s.id = dv.venta_id
        JOIN products p ON dv.producto_id = p.id
        WHERE DATE(s.date) BETWEEN '{$start_date}' AND '{$end_date}'
        ORDER BY s.id DESC
    ";

    $result = $db->query($sql);

    $ventas = [];
    $total_general = 0;

    while($row = $result->fetch_assoc()) {

        $sale_id = $row['sale_id'];

        if (!isset($ventas[$sale_id])) {
            $ventas[$sale_id] = [
                'fecha' => $row['fecha_venta'],
                'items' => [],
                'total_venta' => 0
            ];
        }

        $subtotal = $row['cantidad'] * $row['precio_unitario'];
        $ventas[$sale_id]['total_venta'] += $subtotal;
        $total_general += $subtotal;

        $ventas[$sale_id]['items'][] = [
            'brand' => $row['brand'],
            'model' => $row['model'],
            'cantidad' => $row['cantidad'],
            'precio_unitario' => $row['precio_unitario'],
            'subtotal' => $subtotal
        ];
    }

    // ------------------------------
    //   CONSTRUIR EL HTML DEL PDF
    // ------------------------------

    $html = "
    <h2 style='text-align:center;'>Reporte de Ventas</h2>
    <h4 style='text-align:center;'>Desde: {$start_date} - Hasta: {$end_date}</h4>
    <hr>
    ";

    foreach ($ventas as $id => $v) {

        $html .= "
        <h3>Venta #{$id} — Fecha: {$v['fecha']}</h3>
        <table border='1' width='100%' cellpadding='5' cellspacing='0'>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
        ";

        foreach ($v['items'] as $item) {
            $html .= "
                <tr>
                    <td>{$item['brand']} {$item['model']}</td>
                    <td>{$item['cantidad']}</td>
                    <td>$".number_format($item['precio_unitario'],0,',','.')."</td>
                    <td>$".number_format($item['subtotal'],0,',','.')."</td>
                </tr>
            ";
        }

        $html .= "
            </tbody>
        </table>
        <h4>Total venta: $".number_format($v['total_venta'],0,',','.')."</h4>
        <hr>
        ";
    }

    $html .= "
    <h2 style='text-align:right;'>TOTAL GENERAL: $".number_format($total_general,0,',','.')."</h2>
    ";


    // ------------------------------
    //   GENERAR EL PDF
    // ------------------------------

    require_once __DIR__ . '/libs/dompdf/autoload.inc.php';


    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Descargar automáticamente
    $dompdf->stream("reporte_ventas.pdf", ["Attachment" => true]);
}
