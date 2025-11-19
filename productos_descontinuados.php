<?php
$page_title = 'Productos Descontinuados';
require_once('includes/load.php');
page_require_level(2);

// Obtener solo productos descontinuados
$sql = "
  SELECT p.*, c.name AS categorie, m.file_name AS image
  FROM products p
  LEFT JOIN categories c ON p.categorie_id = c.id
  LEFT JOIN media m ON p.media_id = m.id
  WHERE p.status = 'Descontinuado'
  ORDER BY p.date DESC
";
$products = find_by_sql($sql);
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
        <strong><span class="glyphicon glyphicon-ban-circle"></span> Productos Descontinuados</strong>
        <div class="pull-right">
          <a href="product.php" class="btn btn-primary btn-sm">Volver a productos</a>
        </div>
      </div>

      <div class="panel-body">

        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th>Imagen</th>
              <th>Marca</th>
              <th>Modelo</th>
              <th>Categoría</th>
              <th>Variantes</th>
              <th>Precio compra</th>
              <th>Precio venta</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if (empty($products)): ?>
            <tr>
              <td colspan="10" class="text-center">No hay productos descontinuados.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <tr>

                <td class="text-center"><?php echo count_id(); ?></td>

                <!-- Imagen -->
                <td>
                  <?php if($product['media_id'] === '0'): ?>
                    <img class="img-avatar img-circle" 
                         src="uploads/products/no_image.jpg" alt="">
                  <?php else: ?>
                    <img class="img-avatar img-circle" 
                         src="uploads/products/<?php echo $product['image']; ?>" alt="">
                  <?php endif; ?>
                </td>

                <td><?php echo strtoupper($product['brand']); ?></td>
                <td><?php echo strtoupper($product['model']); ?></td>
                <td class="text-center"><?php echo strtoupper($product['categorie']); ?></td>

                <!-- Variantes -->
                <td class="text-center">
                  <?php
                    $pid = (int)$product['id'];
                    $variants = $db->query("SELECT color, size, stock FROM product_variants WHERE product_id = '{$pid}'");
                    if ($variants && $variants->num_rows > 0) {
                      while($v = $variants->fetch_assoc()) {
                        echo strtoupper($v['color']) . " - " . strtoupper($v['size']) . " : " . $v['stock'] . "<br>";
                      }
                    } else {
                      echo "Sin variantes";
                    }
                  ?>
                </td>

                <td class="text-center">$<?php echo number_format($product['buy_price'], 0, '', '.'); ?></td>
                <td class="text-center">$<?php echo number_format($product['sale_price'], 0, '', '.'); ?></td>
                <td class="text-center"><?php echo read_date($product['date']); ?></td>

                <td class="text-center">
                  <a href="restore_product.php?id=<?php echo (int)$product['id']; ?>"
                     class="btn btn-success btn-xs"
                     onclick="return confirm('¿Restaurar este producto?');">
                    <span class="glyphicon glyphicon-repeat"></span>
                  </a>
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
