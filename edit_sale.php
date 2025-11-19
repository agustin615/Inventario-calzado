<?php
$page_title = 'Editar venta';
require_once('includes/load.php');
page_require_level(3);

if (!isset($_GET['id'])) {
  $session->msg("d", "ID de venta inválido.");
  redirect('sales.php');
}

$venta_id = (int)$_GET['id'];

// Obtener datos de la venta
$venta = find_by_id('sales', $venta_id);
if (!$venta) {
  $session->msg("d", "La venta no existe.");
  redirect('sales.php');
}

// Obtener todos los productos vendidos
$sql = "
  SELECT d.id AS detalle_id, d.cantidad, d.precio_unitario,
         p.id AS product_id, p.brand, p.model,
         v.id AS variant_id, v.color, v.size
  FROM detalle_venta d
  JOIN products p ON d.producto_id = p.id
  JOIN product_variants v ON d.variant_id = v.id
  WHERE d.venta_id = {$venta_id}
";
$detalle = find_by_sql($sql);

// Si se edita cantidad
if (isset($_POST['update_qty'])) {
  $detalle_id = (int)$_POST['detalle_id'];
  $nueva_cantidad = (int)$_POST['cantidad'];

  // Obtener datos actuales del detalle
  $det = find_by_id('detalle_venta', $detalle_id);
  $old_qty = (int)$det['cantidad'];
  $variant_id = (int)$det['variant_id'];

  // Ajustar stock
  $diff = $nueva_cantidad - $old_qty;
  $db->query("UPDATE product_variants SET stock = stock - {$diff} WHERE id = {$variant_id}");

  // Actualizar detalle
  $db->query("UPDATE detalle_venta SET cantidad = {$nueva_cantidad} WHERE id = {$detalle_id}");

  $session->msg("s", "Cantidad actualizada.");
  redirect("edit_sale.php?id={$venta_id}");
}

// Eliminar producto de la venta
if (isset($_GET['del'])) {
  $detalle_id = (int)$_GET['del'];

  // Obtener detalle antes de eliminar
  $det = find_by_id('detalle_venta', $detalle_id);
  $qty = (int)$det['cantidad'];
  $variant_id = (int)$det['variant_id'];

  // Restaurar stock
  $db->query("UPDATE product_variants SET stock = stock + {$qty} WHERE id = {$variant_id}");

  // Eliminar detalle
  delete_by_id('detalle_venta', $detalle_id);

  $session->msg("s", "Producto eliminado de la venta.");
  redirect("edit_sale.php?id={$venta_id}");
}

// Actualizar fecha
if (isset($_POST['update_date'])) {
  $fecha = $db->escape($_POST['date']);
  $db->query("UPDATE sales SET date = '{$fecha}' WHERE id = {$venta_id}");
  $session->msg("s", "Fecha de la venta actualizada.");
  redirect("edit_sale.php?id={$venta_id}");
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">

      <div class="panel-heading clearfix">
        <strong><span class="glyphicon glyphicon-edit"></span> Editar Venta</strong>
        <a href="sales.php" class="btn btn-primary pull-right">Volver</a>
      </div>

      <div class="panel-body">

        <!-- EDITAR FECHA -->
        <form method="post">
          <div class="form-group">
            <label>Fecha de la venta:</label>
            <input type="datetime-local" name="date" class="form-control"
                   value="<?php echo date('Y-m-d\TH:i', strtotime($venta['date'])); ?>">
          </div>
          <button type="submit" name="update_date" class="btn btn-info">Actualizar fecha</button>
        </form>

        <hr>

        <!-- LISTA DE PRODUCTOS DE LA VENTA -->
        <h3>Productos en esta venta</h3>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Variante</th>
              <th>Cantidad</th>
              <th>Precio</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($detalle as $d): ?>
            <tr>
              <td><?php echo strtoupper($d['brand'] . ' ' . $d['model']); ?></td>
              <td><?php echo strtoupper($d['color']) . ' - ' . strtoupper($d['size']); ?></td>

              <!-- EDITAR CANTIDAD -->
              <td>
                <form method="post" style="display:flex; gap:5px;">
                  <input type="hidden" name="detalle_id" value="<?php echo $d['detalle_id']; ?>">
                  <input type="number" name="cantidad" min="1" class="form-control"
                         value="<?php echo $d['cantidad']; ?>">
                  <button type="submit" name="update_qty" class="btn btn-success btn-sm">OK</button>
                </form>
              </td>

              <td>$<?php echo number_format($d['precio_unitario'], 0, '', '.'); ?></td>
              <td>$<?php echo number_format($d['precio_unitario'] * $d['cantidad'], 0, '', '.'); ?></td>

              <!-- ELIMINAR PRODUCTO DE LA VENTA -->
              <td>
                <a href="edit_sale.php?id=<?php echo $venta_id; ?>&del=<?php echo $d['detalle_id']; ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('¿Eliminar este producto de la venta?');">
                  <span class="glyphicon glyphicon-trash"></span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <hr>

        <a href="add_sale.php?edit=<?php echo $venta_id; ?>" class="btn btn-primary">
          Agregar producto a esta venta
        </a>

      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
