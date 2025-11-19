<?php
  $page_title = 'Lista de productos';
  require_once('includes/load.php');
  page_require_level(2);

  // Trae solo productos activos
  $products = find_by_sql("
    SELECT p.*, c.name AS categorie, m.file_name AS image
    FROM products p
    LEFT JOIN categories c ON c.id = p.categorie_id
    LEFT JOIN media m ON m.id = p.media_id
    WHERE p.status = 'Activo'
  ");
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
        <div class="pull-right">
          <a href="add_product.php" class="btn btn-primary">Agregar producto</a>
        </div>
      </div>

      <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>Imagen</th>
              <th>Marca</th>
              <th class="text-center">Modelo</th>
              <th class="text-center">Categoría</th>
              <th class="text-center" style="width: 20%;">Variantes (Color - Talla : Stock)</th>
              <th class="text-center">Precio compra</th>
              <th class="text-center">Precio venta</th>
              <th class="text-center">Agregado</th>
              <th class="text-center" style="width: 100px;">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td class="text-center"><?php echo count_id(); ?></td>

                <td>
                  <?php if ($product['media_id'] == 0): ?>
                    <img class="img-avatar img-circle" src="uploads/products/no_image.jpg">
                  <?php else: ?>
                    <img class="img-avatar img-circle" src="uploads/products/<?php echo $product['image']; ?>">
                  <?php endif; ?>
                </td>

                <td><?php echo strtoupper(remove_junk($product['brand'])); ?></td>
                <td class="text-center"><?php echo strtoupper(remove_junk($product['model'])); ?></td>
                <td class="text-center"><?php echo strtoupper(remove_junk($product['categorie'])); ?></td>

                <!-- Variantes -->
                <td class="text-center">
                  <?php
                    $product_id = (int)$product['id'];
                    $variants = $db->query("
                      SELECT color, size, stock 
                      FROM product_variants 
                      WHERE product_id = '{$product_id}'
                    ");
                    if ($variants && $variants->num_rows > 0) {
                      while ($v = $variants->fetch_assoc()) {
                        echo strtoupper(remove_junk($v['color'])) . " - " .
                             strtoupper(remove_junk($v['size'])) . " : " .
                             (int)$v['stock'] . "<br>";
                      }
                    } else {
                      echo "Sin variantes";
                    }
                  ?>
                </td>

                <td class="text-center"><?php echo number_format($product['buy_price'], 0, '', '.'); ?></td>
                <td class="text-center"><?php echo number_format($product['sale_price'], 0, '', '.'); ?></td>
                <td class="text-center"><?php echo read_date($product['date']); ?></td>
                <td class="text-center">


                 <div class="btn-group">
                <?php if ($product['status'] == 'Activo'): ?>
                    <!-- Editar -->
                    <a href="edit_product.php?id=<?php echo (int)$product['id']; ?>" 
                      class="btn btn-info btn-xs" title="Editar">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <!-- Descontinuar -->
                    <a href="delete_product.php?id=<?php echo (int)$product['id']; ?>" 
                      class="btn btn-danger btn-xs" title="Descontinuar"
                      onclick="return confirm('¿Seguro que deseas descontinuar este producto?');">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                <?php else: ?>
                    <!-- Restaurar -->
                    <a href="restore_product.php?id=<?php echo (int)$product['id']; ?>" 
                      class="btn btn-success btn-xs" title="Restaurar"
                      onclick="return confirm('¿Restaurar este producto?');">
                      <span class="glyphicon glyphicon-repeat"></span>
                    </a>

                <?php endif; ?>

              </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
