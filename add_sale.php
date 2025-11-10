<?php
$page_title = 'Agregar venta';
require_once('includes/load.php');
page_require_level(3);

// Obtener todos los productos con sus variantes y categoría
$sql  = "SELECT p.id AS product_id, p.brand, p.model, p.sale_price, ";
$sql .= "c.name AS categorie, ";
$sql .= "v.id AS variant_id, v.color, v.size, v.stock ";
$sql .= "FROM products p ";
$sql .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
$sql .= "LEFT JOIN product_variants v ON p.id = v.product_id ";
$sql .= "ORDER BY p.brand, p.model";
$products = find_by_sql($sql);

// Procesar venta
if (isset($_POST['add_sale'])) {
  $variant_id = (int)$db->escape($_POST['variant_id']);
  $qty = (int)$db->escape($_POST['quantity']);
  $price = (float)$db->escape($_POST['price']);
  $date = make_date();

  if ($qty <= 0) {
    $session->msg('d', "La cantidad debe ser mayor que 0");
    redirect('add_sale.php', false);
  }

  // Verificar stock disponible
  $variant = find_by_id('product_variants', $variant_id);
  if ($variant && $variant['stock'] >= $qty) {
    $product_id = (int)$variant['product_id'];

    // Insertar venta
    $sql  = "INSERT INTO sales (product_id, variant_id, qty, price, date) ";
    $sql .= "VALUES ('{$product_id}', '{$variant_id}', '{$qty}', '{$price}', '{$date}')";
    if ($db->query($sql)) {
      // Actualizar stock
      $new_stock = $variant['stock'] - $qty;
      $update_stock = "UPDATE product_variants SET stock = '{$new_stock}' WHERE id = '{$variant_id}'";
      $db->query($update_stock);

      $session->msg('s', "Venta agregada correctamente");
      redirect('add_sale.php', false);
    } else {
      $session->msg('d', "No se pudo registrar la venta");
      redirect('add_sale.php', false);
    }
  } else {
    $session->msg('d', "No hay suficiente stock disponible");
    redirect('add_sale.php', false);
  }
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
        <strong><span class="glyphicon glyphicon-th"></span> <span>Agregar venta</span></strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Marca</th>
              <th>Modelo</th>
              <th>Categoría</th>
              <th class="text-center">Color</th>
              <th class="text-center">Talla</th>
              <th class="text-center">Stock disponible</th>
              <th class="text-center">Precio</th>
              <th class="text-center" style="width: 150px;">Cantidad a vender</th>
              <th class="text-center" style="width: 100px;">Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <form method="post" action="add_sale.php">
                  <td class="text-center"><?php echo count_id(); ?></td>
                  <td><?php echo strtoupper(remove_junk($product['brand'])); ?></td>
                  <td><?php echo strtoupper(remove_junk($product['model'])); ?></td>
                  <td class="text-center"><?php echo strtoupper(remove_junk($product['categorie'])); ?></td>
                  <td class="text-center"><?php echo strtoupper(remove_junk($product['color'])); ?></td>
                  <td class="text-center"><?php echo strtoupper(remove_junk($product['size'])); ?></td>
                  <td class="text-center"><?php echo (int)$product['stock']; ?></td>
                  <td class="text-center">$<?php echo number_format($product['sale_price'], 0, '', '.'); ?></td>
                  <td class="text-center">
                    <input type="number" name="quantity" min="1" max="<?php echo (int)$product['stock']; ?>" class="form-control" style="width:80px; margin:auto;" required>
                    <input type="hidden" name="variant_id" value="<?php echo (int)$product['variant_id']; ?>">
                    <input type="hidden" name="price" value="<?php echo (float)$product['sale_price']; ?>">
                  </td>
                  <td class="text-center">
                    <button type="submit" name="add_sale" class="btn btn-success btn-sm">Vender</button>
                  </td>
                </form>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
