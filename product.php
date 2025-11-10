<?php
  $page_title = 'Lista de productos';
  require_once('includes/load.php');
  // Verificar nivel de usuario
  page_require_level(2);
  $products = join_product_table();
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
              <th class="text-center" style="width: 10%;">Modelo</th>
              <th class="text-center" style="width: 10%;">Categoría</th>
              <th class="text-center" style="width: 20%;">Variantes (Color - Talla : Stock)</th>
              <th class="text-center" style="width: 10%;">Precio de compra</th>
              <th class="text-center" style="width: 10%;">Precio de venta</th>
              <th class="text-center" style="width: 10%;">Agregado</th>
              <th class="text-center" style="width: 100px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td class="text-center"><?php echo count_id(); ?></td>
                <td>
                  <?php if($product['media_id'] === '0'): ?>
                    <img class="img-avatar img-circle" src="uploads/products/no_image.jpg" alt="">
                  <?php else: ?>
                    <img class="img-avatar img-circle" src="uploads/products/<?php echo $product['image']; ?>" alt="">
                  <?php endif; ?>
                </td>
                <td><?php echo strtoupper(remove_junk($product['brand'])); ?></td>
                <td class="text-center"><?php echo strtoupper(remove_junk($product['model'])); ?></td>
                <td class="text-center"><?php echo strtoupper(remove_junk($product['categorie'])); ?></td>
                <td class="text-center">
                  <?php
                    $product_id = (int)$product['id'];
                    $variants = $db->query("SELECT color, size, stock FROM product_variants WHERE product_id = '{$product_id}'");
                    if($variants && $variants->num_rows > 0){
                      while($v = $variants->fetch_assoc()){
                        echo strtoupper(remove_junk($v['color'])) . " - " . strtoupper(remove_junk($v['size'])) . " : " . (int)$v['stock'] . "<br>";
                      }
                    } else {
                      echo "Sin variantes";
                    }
                  ?>
                </td>
                <td class="text-center"><?php echo number_format((float)$product['buy_price'], 0, '', '.'); ?></td>
                <td class="text-center"><?php echo number_format((float)$product['sale_price'], 0, '', '.'); ?></td>

                <td class="text-center"><?php echo read_date($product['date']); ?></td>
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-info btn-xs" title="Editar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a href="delete_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-danger btn-xs" title="Eliminar" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
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
