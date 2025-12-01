<?php
$page_title = 'Reporte de ventas';
require_once('includes/load.php');
page_require_level(3);

// ------------ VALIDAR FECHAS ------------
if (isset($_POST['submit'])) {
    $req_dates = array('start-date', 'end-date');
    validate_fields($req_dates);

    if (empty($errors)) {
        $start_date = remove_junk($db->escape($_POST['start-date']));
        $end_date   = remove_junk($db->escape($_POST['end-date']));
    } else {
        $session->msg("d", $errors);
        redirect('sales_report.php', false);
    }
} else {
    $session->msg("d", "Seleccione un rango de fechas");
    redirect('sales_report.php', false);
}

// ------------ CONSULTA REAL ------------
$sql = "
    SELECT 
        s.id AS venta_id,
        s.date AS fecha,
        p.id AS product_id,
        p.brand,
        p.model,
        c.name AS categoria,
        v.color,
        v.size,
        dv.cantidad,
        dv.precio_unitario,
        (dv.cantidad * dv.precio_unitario) AS total_producto
    FROM sales s
    JOIN detalle_venta dv ON dv.venta_id = s.id
    JOIN products p ON p.id = dv.producto_id
    LEFT JOIN categories c ON c.id = p.categorie_id
    LEFT JOIN product_variants v ON v.id = dv.variant_id
    WHERE DATE(s.date) BETWEEN '{$start_date}' AND '{$end_date}'
    ORDER BY s.date DESC, s.id DESC
";

$results = $db->query($sql);

// ------------ PREPARAR HTML PARA PDF ------------
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de ventas</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
th, td { border: 1px solid #444; padding: 6px; text-align: left; }
th { background: #f0f0f0; }
h2 { text-align: center; }
.subtitulo { font-size: 14px; margin-bottom: 15px; }
</style>
</head>
<body>

<h2>Reporte de ventas</h2>
<p class="subtitulo"><strong>Desde:</strong> '.$start_date.' &nbsp;&nbsp; <strong>Hasta:</strong> '.$end_date.'</p>
';

$ventas = [];
$total_general = 0;

while ($row = $results->fetch_assoc()) {
    $ventas[$row['venta_id']][] = $row;
}

// ------------ IMPRIMIR CADA VENTA ------------
foreach ($ventas as $venta_id => $items) {
    $fecha = $items[0]['fecha'];

    $html .= "
    <h3>Venta #{$venta_id}</h3>
    <table>
        <thead>
            <tr>
                <th>Producto: Marca-Modelo</th>
                <th>Categoría</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
    ";

    $total_venta = 0;

    foreach ($items as $p) {
        $total_venta += $p['total_producto'];
        $total_general += $p['total_producto'];

        $html .= "
        <tr>
            <td>{$p['brand']} {$p['model']}</td>
            <td>".($p['categoria'] ?? 'Sin categoría')."</td>
            <td>".($p['color'] ?? 'N/A')."</td>
            <td>".($p['size'] ?? 'N/A')."</td>
            <td>{$p['cantidad']}</td>
            <td>$ ".number_format($p['precio_unitario'], 0, '', '.')."</td>
            <td>$ ".number_format($p['total_producto'], 0, '', '.')."</td>
        </tr>
        ";

    }

    $html .= "
        </tbody>
        <tfoot>
            <tr>
                <td colspan='6' style='text-align:right;'><strong>Total venta:</strong></td>
                <td><strong>$ ".number_format($total_venta, 0, '', '.')."</strong></td>

            </tr>
        </tfoot>
    </table>
    <hr>
    ";
}

$html .= "
<h2>Total General: $ ".number_format($total_general, 0, '', '.')."</h2>

</body>
</html>
";

// ------------ GENERAR PDF ------------
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// -------- DESCARGA FORZADA --------
$pdf = $dompdf->output();
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=reporte_ventas.pdf");
header("Content-Length: " . strlen($pdf));

echo $pdf;
exit;
