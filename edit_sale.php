<?php
$page_title = 'Editar venta';
require_once('includes/load.php');
page_require_level(3);

// Verificar que la venta exista
$sale = find_by_id('sales', (int)$_GET['id']);
if (!$sale) {
  $session->msg("d", "ID de venta no válido.");
  redirect('sales.php');
}

// Obtener producto y variante asociada
$product = find_by_id('products', $sale['product_id']);
$variant = find_by_id('product_variants', $sale['variant_id']);

if (isset($_POST['update_sale'])) {
  $req_fields = array('quantity', 'price', 'date');
  validate_fields($req_fields);

  if (empty($errors)) {
    $new_qty = (int)$db->escape($_POST['quantity']);
    $price   = (float)$db->escape($_POST['price']);
    $date    = $db->escape($_POST['date']);

    // Diferencia de cantidad (para ajustar stock)
    $old_qty = (int)$sale['qty'];
    $qty_diff = $new_qty - $old_qty;

    // Actualizar venta
    $sql  = "UPDATE sales SET ";
    $sql .= "qty = '{$new_qty}', price = '{$price}', date = '{$date}' ";
    $sql .= "WHERE id = '{$sale['id']}'";

    if ($db->query($sql)) {

      // Actualizar stock de la variante
      if ($variant) {
        $sql_stock = "UPDATE product_variants 
                      SET stock = stock - {$qty_diff}
                      WHERE id = '{$variant['id']}'";
        $db->query($sql_stock);
      }

      $session->msg('s', "Venta actualizada exitosamente.");
      redirect('sales.php', false);
    } else {
      $session->msg('d', 'Error al actualizar la venta.');
      redirect('edit_sale.php?id=' . $sale['id'], false);
    }
  } else {
    $session->msg("d", $errors);
    redirect('edit_sale.php?id=' . $sale['id'], false);
  }
}
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-8">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-edit"></span>
          <span>Editar venta</span>
        </strong>
        <div class="pull-right">
          <a href="sales.php" class="btn btn-primary">Volver</a>
        </div>
      </div>

      <div class="panel-body">
        <form method="post" action="edit_sale.php?id=<?php echo (int)$sale['id']; ?>">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Fecha</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo strtoupper(remove_junk($product['brand'] . ' ' . $product['model'])); ?></td>
                <td><?php echo strtoupper(remove_junk($variant['color'])); ?></td>
                <td><?php echo strtoupper(remove_junk($variant['size'])); ?></td>
                <td><input type="number" class="form-control" name="quantity" value="<?php echo (int)$sale['qty']; ?>" min="1"></td>
                <td><input type="number" class="form-control" name="price" value="<?php echo (float)$sale['price']; ?>" step="0.01"></td>
                <td><input type="date" class="form-control" name="date" value="<?php echo remove_junk($sale['date']); ?>"></td>
                <td><button type="submit" name="update_sale" class="btn btn-success">Guardar</button></td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
