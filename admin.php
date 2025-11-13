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
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong><span class="glyphicon glyphicon-th"></span> Productos más vendidos</strong>
      </div>
      <div class="panel-body">
        <table class="table table-striped table-bordered table-condensed">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Categoría</th>
              <th>Variaciones vendidas</th>
              <th>Cantidad total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products_sold as $product_sold): ?>
              <tr>
                <td><?php echo isset($product_sold['name']) ? remove_junk(first_character($product_sold['name'])) : 'Producto sin nombre'; ?></td>
                <td><?php echo remove_junk(first_character($product_sold['categorie'] ?? 'Sin categoría')); ?></td>
                <td class="text-center"><?php echo (int)$product_sold['totalSold']; ?></td>
                <td class="text-center"><?php echo (int)$product_sold['totalQty']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>



  <!-- ÚLTIMAS VENTAS -->
<div class="col-md-4">
  <div class="panel panel-default">
    <div class="panel-heading">
      <strong><span class="glyphicon glyphicon-th"></span> ÚLTIMAS 5 VENTAS</strong>
    </div>
    <div class="panel-body">
      <table class="table table-striped table-bordered table-condensed" style="table-layout: fixed; width: 100%;">
        <thead>
          <tr>
            <th class="text-center" style="width: 8%;">#</th>
            <th style="width: 25%;">Producto - Tipo</th>
            <th style="width: 20%;">Categoría</th>
            <th style="width: 27%;">Fecha y hora de la venta</th>
            <th style="width: 20%;">Total de Venta</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recent_sales as $recent_sale): ?>
          <tr>
            <td class="text-center"><?php echo count_id(); ?></td>
            <td>
              <a href="edit_sale.php?id=<?php echo (int)$recent_sale['id']; ?>">
                <?php echo isset($recent_sale['product_name']) && $recent_sale['product_name'] !== null 
                  ? remove_junk(first_character($recent_sale['product_name']))
                  : '<em>Producto eliminado</em>'; ?>
              </a>
            </td>
            <td><?php echo remove_junk($recent_sale['category_name'] ?? 'Sin categoría'); ?></td>
            <td><?php echo remove_junk($recent_sale['date'] ?? '-'); ?></td>
            <td>$<?php echo remove_junk(number_format($recent_sale['total_sale'] ?? 0, 0, '', '.')); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>




   <!-- PRODUCTOS RECIENTES -->
   <div class="col-md-4">
     <div class="panel panel-default">
       <div class="panel-heading">
         <strong><span class="glyphicon glyphicon-th"></span> Los  ultimos 5 Productos recientemente añadidos</strong>
       </div>
       <div class="panel-body">
         <div class="list-group">
           <?php foreach ($recent_products as $recent_product): ?>
             <a class="list-group-item clearfix" href="edit_product.php?id=<?php echo (int)$recent_product['id']; ?>">
              <h4 class="list-group-item-heading">
              <?php
                if (!isset($recent_product['media_id']) || $recent_product['media_id'] == 0):?>
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


<?php include_once('layouts/footer.php'); ?>
