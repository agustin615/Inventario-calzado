<?php
$page_title = 'Agregar venta';
require_once('includes/load.php');
page_require_level(3);

$edit_mode = false;
$venta_id = 0;

/* =====================================================
    DETECTAR MODO EDICIÓN
   ===================================================== */
if (isset($_GET['edit'])) {
  $edit_mode = true;
  $venta_id = (int)$_GET['edit'];

  $venta = find_by_id('sales', $venta_id);
  if (!$venta) {
    $session->msg("d", "La venta que intentas editar no existe.");
    redirect('sales.php');
  }
}

/* =====================================================
    INICIALIZAR CARRITO SI NO EXISTE
   ===================================================== */
if (!isset($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}

/* =====================================================
    OBTENER PRODUCTOS Y VARIANTES
   ===================================================== */
$sql  = "SELECT p.id AS product_id, p.brand, p.model, p.sale_price, 
         c.name AS categorie, 
         v.id AS variant_id, v.color, v.size, v.stock
         FROM products p
         LEFT JOIN categories c ON p.categorie_id = c.id
         LEFT JOIN product_variants v ON p.id = v.product_id
         WHERE p.estado = 1   -- 🔥 Solo productos activos
         ORDER BY p.brand, p.model";

$products = find_by_sql($sql);

/* =====================================================
    AGREGAR PRODUCTO (NORMAL O EDIT MODE)
   ===================================================== */
if (isset($_POST['add_to_cart'])) {

  $variant_id = (int)$_POST['variant_id'];
  $qty        = (int)$_POST['quantity'];
  $price      = (float)$_POST['price'];

  $variant = find_by_id('product_variants', $variant_id);

  $product_check = find_by_id('products', $variant['product_id']);

  if ($product_check['estado'] == 0) {   // 🔥 producto descontinuado
      $session->msg("d", "Este producto está descontinuado y no puede venderse.");
      redirect("add_sale.php" . ($edit_mode ? "?edit={$venta_id}" : ""));
  }

  if (!$variant || $variant['stock'] < $qty) {
    $session->msg("d", "No hay suficiente stock disponible.");
    redirect("add_sale.php" . ($edit_mode ? "?edit={$venta_id}" : ""));
  }

  /* ====== SI ESTÁS MODIFICANDO UNA VENTA EXISTENTE ====== */
  if ($edit_mode) {
  $product_check = find_by_id('products', $variant['product_id']);

  if ($product_check['estado'] == 1) {
      $session->msg("d", "Este producto está descontinuado y no puede agregarse a una venta.");
      redirect("edit_sale.php?id={$venta_id}");
  }
    // Insertar directo en detalle_venta
    $sql_det = "INSERT INTO detalle_venta 
                (venta_id, producto_id, variant_id, cantidad, precio_unitario)
                VALUES ('{$venta_id}', '{$variant['product_id']}', '{$variant_id}', '{$qty}', '{$price}')";
    $db->query($sql_det);

    // Descontar stock
    $db->query("UPDATE product_variants 
                SET stock = stock - {$qty}
                WHERE id = {$variant_id}");

    $session->msg("s", "Producto agregado a la venta.");
    redirect("edit_sale.php?id={$venta_id}");
    exit;
  }

  /* ====== MODO NORMAL: AGREGAR AL CARRITO ====== */
  if (isset($_SESSION['carrito'][$variant_id])) {
    $_SESSION['carrito'][$variant_id]['qty'] += $qty;
  } else {
    $_SESSION['carrito'][$variant_id] = [
      "product_id" => $variant['product_id'],
      "variant_id" => $variant_id,
      "qty"        => $qty,
      "price"      => $price
    ];
  }

  $session->msg("s", "Producto agregado al carrito");
  redirect('add_sale.php');
}

/* =====================================================
    FINALIZAR VENTA (SOLO CUANDO NO ES EDIT MODE)
   ===================================================== */
if (isset($_POST['finalizar_venta']) && !$edit_mode) {

  if (empty($_SESSION['carrito'])) {
    $session->msg("d", "No hay productos en el carrito.");
    redirect('add_sale.php');
  }

  $date = make_date();

  // Crear venta
  $sql = "INSERT INTO sales (date) VALUES ('{$date}')";
  if ($db->query($sql)) {

    $venta_id = $db->insert_id();

    // Insertar detalles
    foreach ($_SESSION['carrito'] as $item) {

      $p_id = $item['product_id'];
      $v_id = $item['variant_id'];
      $qty  = $item['qty'];
      $price = $item['price'];

      // Insertar detalle
      $sql_det = "INSERT INTO detalle_venta 
                  (venta_id, producto_id, variant_id, cantidad, precio_unitario)
                  VALUES ('{$venta_id}', '{$p_id}', '{$v_id}', '{$qty}', '{$price}')";
      $db->query($sql_det);

      // Actualizar stock
      $variant = find_by_id('product_variants', $v_id);
      $new_stock = $variant['stock'] - $qty;

      $db->query("UPDATE product_variants SET stock = '{$new_stock}' WHERE id = '{$v_id}'");
    }

    $_SESSION['carrito'] = [];

    $session->msg("s", "Venta registrada correctamente");
    redirect('sales.php');

  } else {
    $session->msg("d", "Error al registrar la venta.");
    redirect('add_sale.php');
  }
}

include_once('layouts/header.php');
?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-8">

    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span> 
          <?php echo $edit_mode ? "Agregar producto a venta #{$venta_id}" : "Productos"; ?>
        </strong>
      </div>

      <div class="panel-body">

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th><th>Marca</th><th>Modelo</th><th>Categoría</th>
              <th>Color</th><th>Talla</th><th>Stock</th><th>Precio</th>
              <th>Cantidad</th><th>Agregar</th>
            </tr>
          </thead>
          <tbody>

          <?php foreach ($products as $product): ?>
            <tr>
              <form method="post">
                <td><?php echo count_id(); ?></td>
                <td><?php echo $product['brand']; ?></td>
                <td><?php echo $product['model']; ?></td>
                <td><?php echo $product['categorie']; ?></td>
                <td><?php echo $product['color']; ?></td>
                <td><?php echo $product['size']; ?></td>
                <td><?php echo $product['stock']; ?></td>
                <td>$<?php echo number_format($product['sale_price'],0,'','.'); ?></td>

                <td>
                  <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" 
                         class="form-control" required>
                </td>

                <td>
                  <input type="hidden" name="variant_id" value="<?php echo $product['variant_id']; ?>">
                  <input type="hidden" name="price" value="<?php echo $product['sale_price']; ?>">
                  <button name="add_to_cart" class="btn btn-primary btn-sm">Agregar</button>
                </td>

              </form>
            </tr>
          <?php endforeach; ?>

          </tbody>
        </table>

      </div>
    </div>

  </div>

  <!-- SOLO MODO NORMAL MUESTRA CARRITO -->
  <?php if (!$edit_mode): ?>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-shopping-cart"></span> Carrito</strong>
      </div>
      <div class="panel-body">

        <?php if (empty($_SESSION['carrito'])): ?>
          <p>No hay productos en el carrito.</p>
        <?php else: ?>

        <table class="table table-bordered">
          <thead>
            <tr><th>Producto</th><th>Cant</th><th>Total</th></tr>
          </thead>
          <tbody>
          <?php
            $total = 0;
            foreach ($_SESSION['carrito'] as $item):
              $p = find_by_id('products', $item['product_id']);
              $subtotal = $item['qty'] * $item['price'];
              $total += $subtotal;
          ?>
            <tr>
              <td><?php echo $p['brand'] . " " . $p['model']; ?></td>
              <td><?php echo $item['qty']; ?></td>
              <td>$<?php echo number_format($subtotal, 0, '', '.'); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h4>Total venta: <strong>$<?php echo number_format($total, 0, '', '.'); ?></strong></h4>

        <form method="post">
          <button name="finalizar_venta" class="btn btn-success btn-block">
            Finalizar venta
          </button>
        </form>

        <?php endif; ?>

      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
