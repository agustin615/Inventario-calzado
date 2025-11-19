<?php
$page_title = 'Lista de ventas';
require_once('includes/load.php');
page_require_level(3);

/*
  Trae todas las ventas con su total final
*/
$sql = "
  SELECT 
    s.id AS venta_id,
    s.date,
    IFNULL(SUM(d.cantidad * d.precio_unitario), 0) AS total,
    IFNULL(SUM(d.cantidad), 0) AS total_items
  FROM sales s
  LEFT JOIN detalle_venta d ON d.venta_id = s.id
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
      
      <div class="panel-heading clearfix">
        <strong><span class="glyphicon glyphicon-th"></span> Todas las ventas</strong>
        <div class="pull-right">
          <a href="add_sale.php" class="btn btn-primary">Agregar venta</a>
        </div>
      </div>

      <div class="panel-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Productos</th>
              <th class="text-center">Cantidad de productos</th>
              <th class="text-center">Total</th>
              <th class="text-center">Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if (empty($ventas)): ?>
            <tr>
              <td colspan="6" class="text-center">No hay ventas registradas.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach ($ventas as $v): ?>

              <?php
              /* Obtener productos de esta venta */
              $detalles = find_by_sql("
                SELECT 
                  p.brand, p.model,
                  v.color, v.size,
                  d.cantidad
                FROM detalle_venta d
                LEFT JOIN products p ON p.id = d.producto_id
                LEFT JOIN product_variants v ON v.id = d.variant_id
                WHERE d.venta_id = {$v['venta_id']}
              ");
              ?>

              <tr>
                <td><?php echo $i++; ?></td>

                <td>
                  <?php foreach ($detalles as $d): ?>
                    <div>
                      <?php 
                        echo strtoupper($d['brand'] . " " . $d['model']);
                        echo " — " . strtoupper($d['color']) . " / " . strtoupper($d['size']);
                        echo " <strong>x" . $d['cantidad'] . "</strong>";
                      ?>
                    </div>
                  <?php endforeach; ?>
                </td>

                <td class="text-center">
                  <?php echo (int)$v['total_items']; ?>
                </td>

                <td class="text-center">
                  $<?php echo number_format($v['total'], 0, '', '.'); ?>
                </td>

                <td class="text-center">
                  <?php echo read_date($v['date']); ?>
                </td>

                <td class="text-center">
                  <div class="btn-group">
                    
                    <!-- EDITAR -->
                    <a href="edit_sale.php?id=<?php echo (int)$v['venta_id']; ?>" 
                       class="btn btn-warning btn-xs" title="Editar">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>

                    <!-- ELIMINAR -->
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
