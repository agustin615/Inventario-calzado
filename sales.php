<?php
$page_title = 'Lista de ventas';
require_once('includes/load.php');
page_require_level(3);

/* ------------------ FILTRO DE FECHAS ------------------ */
$from = isset($_GET['from']) ? $db->escape($_GET['from']) : '';
$to   = isset($_GET['to'])   ? $db->escape($_GET['to'])   : '';

$condiciones = "";

if ($from != '' && $to != '') {
  $condiciones = "WHERE s.date BETWEEN '{$from}' AND '{$to}'";
} 
else if ($from != '') {
  $condiciones = "WHERE s.date >= '{$from}'";
}
else if ($to != '') {
  $condiciones = "WHERE s.date <= '{$to}'";
}

/* ------------------ CONSULTA PRINCIPAL ------------------ */
$sql = "
  SELECT 
    s.id AS venta_id,
    s.date,
    IFNULL(SUM(d.cantidad * d.precio_unitario), 0) AS total,
    IFNULL(SUM(d.cantidad), 0) AS total_items
  FROM sales s
  LEFT JOIN detalle_venta d ON d.venta_id = s.id
  {$condiciones}
  GROUP BY s.id
  ORDER BY s.date DESC
";

$ventas = find_by_sql($sql);
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">

      <!-- HEADER -->
      <div class="panel-heading clearfix">
        <strong><span class="glyphicon glyphicon-th"></span> Todas las ventas</strong>
        <div class="pull-right">
          <a href="add_sale.php" class="btn btn-primary">Agregar venta</a>
        </div>
      </div>

      <!-- FORMULARIO DE BUSQUEDA -->
      <div class="panel-body">
        <form method="GET" class="form-inline" style="margin-bottom:15px;">
          <label>Desde: </label>
          <input type="date" name="from" class="form-control"
                 value="<?php echo isset($_GET['from']) ? $_GET['from'] : ''; ?>">

          <label style="margin-left:10px;">Hasta: </label>
          <input type="date" name="to" class="form-control"
                 value="<?php echo isset($_GET['to']) ? $_GET['to'] : ''; ?>">

          <button type="submit" class="btn btn-success" style="margin-left:10px;">
            Buscar
          </button>

          <a href="sales.php" class="btn btn-default">Limpiar</a>
        </form>

        <!-- TABLA DE VENTAS -->
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Productos - Marca - Modelo - Variación</th>
              <th class="text-center">Precio unitario</th>
              <th class="text-center">Total producto</th>
              <th class="text-center">Cantidad total</th>
              <th class="text-center">Total venta</th>
              <th class="text-center">Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if (empty($ventas)): ?>
            <tr>
              <td colspan="8" class="text-center">No hay ventas registradas.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach ($ventas as $v): ?>

              <?php
              /* OBTENER DETALLES DE CADA PRODUCTO DE LA VENTA */
              $detalles = find_by_sql("
                SELECT 
                  p.brand, p.model,
                  c.name AS categoria,
                  v.color, v.size,
                  d.cantidad,
                  d.precio_unitario
                FROM detalle_venta d
                LEFT JOIN products p ON p.id = d.producto_id
                LEFT JOIN categories c ON c.id = p.categorie_id
                LEFT JOIN product_variants v ON v.id = d.variant_id
                WHERE d.venta_id = {$v['venta_id']}
              ");
              ?>

              <tr>
                <td><?php echo $i++; ?></td>

                <td>
                  <?php foreach ($detalles as $d): ?>
                    <div style="margin-bottom:4px;">
                      <strong><?php echo strtoupper($d['categoria']); ?></strong>:
                      <?php
                        echo strtoupper($d['brand'] . " " . $d['model']);
                        echo " — " . strtoupper($d['color']) . " / " . strtoupper($d['size']);
                        echo " <strong>x{$d['cantidad']}</strong>";
                      ?>
                    </div>
                  <?php endforeach; ?>
                </td>

                <!-- PRECIO UNITARIO -->
                <td class="text-center">
                  <?php foreach ($detalles as $d): ?>
                    $<?php echo number_format($d['precio_unitario'], 0, '', '.'); ?><br>
                  <?php endforeach; ?>
                </td>

                <!-- TOTAL PRODUCTO -->
                <td class="text-center">
                  <?php foreach ($detalles as $d): ?>
                    $<?php echo number_format($d['precio_unitario'] * $d['cantidad'], 0, '', '.'); ?><br>
                  <?php endforeach; ?>
                </td>

                <!-- TOTAL ITEMS -->
                <td class="text-center">
                  <?php echo (int)$v['total_items']; ?>
                </td>

                <!-- TOTAL VENTA -->
                <td class="text-center">
                  $<?php echo number_format($v['total'], 0, '', '.'); ?>
                </td>

                <td class="text-center">
                  <?php echo read_date($v['date']); ?>
                </td>

                <!-- ACCIONES -->
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_sale.php?id=<?php echo (int)$v['venta_id']; ?>"
                       class="btn btn-warning btn-xs" title="Editar">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>

                    <a href="delete_sale.php?id=<?php echo (int)$v['venta_id']; ?>"
                       class="btn btn-danger btn-xs"
                       onclick="return confirm('¿Seguro que deseas eliminar esta venta?');">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                  </div>
                </td>
              </tr>

            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>

        </table>
      </div>

    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
