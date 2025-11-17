<?php
$page_title = 'Lista de ventas';
require_once('includes/load.php');
page_require_level(3);

// OBTENER TODAS LAS VENTAS CON SU TOTAL
$sql = "
    SELECT 
        s.id AS venta_id,
        s.date,
        SUM(d.cantidad * d.precio_unitario) AS total,
        COUNT(d.id) AS total_items
    FROM sales s
    LEFT JOIN detalle_venta d ON s.id = d.venta_id
    GROUP BY s.id
    ORDER BY s.date DESC
";

$ventas = find_by_sql($sql);

include_once('layouts/header.php');
?>

<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong><span class="glyphicon glyphicon-th"></span> Ventas registradas</strong>
        <div class="pull-right">
          <a href="add_sale.php" class="btn btn-primary">Agregar venta</a>
        </div>
      </div>

      <div class="panel-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">Productos</th>
              <th class="text-center">Total</th>
              <th class="text-center">Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>

            <?php if (empty($ventas)): ?>
              <tr>
                <td colspan="5" class="text-center">No hay ventas registradas.</td>
              </tr>
            <?php else: ?>
              <?php $i = 1; foreach ($ventas as $v): ?>
                <tr>
                  <td class="text-center"><?php echo $i++; ?></td>

                  <td class="text-center">
                    <?php echo $v['total_items']; ?> productos
                  </td>

                  <td class="text-center">
                    $<?php echo number_format($v['total'], 0, '', '.'); ?>
                  </td>

                  <td class="text-center">
                    <?php echo read_date($v['date']); ?>
                  </td>

                  <td class="text-center">
                    <div class="btn-group">
                      <a href="view_sale.php?id=<?php echo (int)$v['venta_id']; ?>" 
                        class="btn btn-info btn-xs" title="Ver">
                        <span class="glyphicon glyphicon-eye-open"></span>
                      </a>

                      <a href="delete_sale.php?id=<?php echo (int)$v['venta_id']; ?>" 
                        class="btn btn-danger btn-xs" 
                        onclick="return confirm('¿Eliminar venta completa? Se restaurará el stock.');">
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
