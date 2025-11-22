<?php
  $page_title = 'Admin página de inicio';
  require_once('includes/load.php');
  page_require_level(1);
?>

<?php
 $c_categorie     = count_by_id('categories');
 $c_product       = count_by_id('products');
 $c_sale          = count_by_id('sales');
 $c_user          = count_by_id('users');
 $products_sold   = find_higest_saleing_product('5');
 $recent_products = find_recent_product_added('5');
 $recent_sales    = find_recent_sale_added('5');
$ultimas_ventas = find_last_sales_full('5');
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
   <div class="col-md-6">
     <?php echo display_msg($msg); ?>
   </div>
</div>

<div class="row">
    <!-- RESUMEN -->
    <div class="col-md-3">
        <div class="panel panel-box clearfix">
            <div class="panel-icon pull-left bg-green">
                <i class="glyphicon glyphicon-user"></i>
            </div>
            <div class="panel-value pull-right">
                <h2 class="margin-top"><?php echo $c_user['total']; ?></h2>
                <p class="text-muted">Usuarios</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-box clearfix">
            <div class="panel-icon pull-left bg-red">
                <i class="glyphicon glyphicon-list"></i>
            </div>
            <div class="panel-value pull-right">
                <h2 class="margin-top"><?php echo $c_categorie['total']; ?></h2>
                <p class="text-muted">Categorías</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-box clearfix">
            <div class="panel-icon pull-left bg-blue">
                <i class="glyphicon glyphicon-shopping-cart"></i>
            </div>
            <div class="panel-value pull-right">
                <h2 class="margin-top"><?php echo $c_product['total']; ?></h2>
                <p class="text-muted">Productos</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-box clearfix">
            <div class="panel-icon pull-left bg-yellow">
                <i class="glyphicon glyphicon-usd"></i>
            </div>
            <div class="panel-value pull-right">
                <h2 class="margin-top"><?php echo $c_sale['total']; ?></h2>
                <p class="text-muted">Ventas</p>
            </div>
        </div>
    </div>
</div>


<div class="row">
  <!-- PRODUCTOS MÁS VENDIDOS -->
  <div class="col-md-6">   <!-- AUMENTO A 6 -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-th"></span> Productos más vendidos</strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered table-condensed">
          <thead>
            <tr>
              <th>Producto - Modelo</th>
              <th>Categoría</th>
              <th>Variaciones vendidas</th>
              <th>Cantidad total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products_sold as $product_sold): ?>
              <tr>
                <td><?php echo isset($product_sold['name']) ? remove_junk(first_character($product_sold['name'])) : 'Producto sin nombre'; ?></td>
                <td><?php echo remove_junk(first_character($product_sold['category_name'] ?? 'Sin categoría')); ?></td>
                <td class="text-center"><?php echo (int)$product_sold['totalSold']; ?></td>
                <td class="text-center"><?php echo (int)$product_sold['totalQty']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- PRODUCTOS RECIENTES -->
  <div class="col-md-6">   <!-- AUMENTO A 6 -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-th"></span> Los últimos 5 productos añadidos</strong>
      </div>
      <div class="panel-body">
        <div class="list-group">
          <?php foreach ($recent_products as $recent_product): ?>
            <a class="list-group-item clearfix" href="edit_product.php?id=<?php echo (int)$recent_product['id']; ?>">
              <h4 class="list-group-item-heading">
                <?php if (!isset($recent_product['media_id']) || $recent_product['media_id'] == 0): ?>
                  <img class="img-avatar img-circle" src="uploads/products/no_image.jpg" alt="">
                <?php else: ?>
                  <img class="img-avatar img-circle" src="uploads/products/<?php echo $recent_product['image'] ?? 'no_image.jpg'; ?>" alt="">
                <?php endif; ?>
                <?php echo remove_junk(first_character(($recent_product['brand'] ?? 'Sin marca') . " " . ($recent_product['model'] ?? 'Sin modelo'))); ?>
                <span class="label label-warning pull-right">
                  $<?php echo (int)$recent_product['sale_price']; ?>
                </span>
              </h4>
              <span class="list-group-item-text pull-right">
                <?php echo remove_junk(first_character($recent_product['categorie'] ?? 'Sin categoría')); ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</div>


  <!-- Ultimas ventas --> 

<div class="panel panel-default">
  <div class="panel-heading">
    <strong>
      <span class="glyphicon glyphicon-shopping-cart"></span>
      Últimas 5 ventas
    </strong>
  </div>

  <div class="panel-body">

    <?php foreach ($ultimas_ventas as $venta): ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <strong>Venta #<?php echo $venta['sale_id']; ?></strong>
          <span class="pull-right"><?php echo $venta['date']; ?></span>
        </div>
        
        <div class="panel-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($venta['items'] as $item): ?>
              <tr>
                <td><?php echo $item['name']; ?></td>
                <td><?php echo $item['category']; ?></td>
                <td><?php echo $item['cantidad']; ?></td>
                <td><?php echo $item['precio']; ?></td>
                <td><?php echo $item['subtotal']; ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>

          <h4 class="text-right">
            Total de la venta: <strong>$<?php echo number_format($venta['total'], 0, ',', '.'); ?></strong>
          </h4>
        </div>

      </div>
    <?php endforeach; ?>

  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
