<?php
  $page_title = 'Lista de productos';
  require_once('includes/load.php');
  page_require_level(2);

  // --- CAPTURAR FILTROS ---
  $search_brand = isset($_GET['brand']) ? remove_junk($db->escape($_GET['brand'])) : '';
  $search_model = isset($_GET['model']) ? remove_junk($db->escape($_GET['model'])) : '';
  $search_category = isset($_GET['category']) ? remove_junk($db->escape($_GET['category'])) : '';

  // --- TRAER CATEGORÍAS ---
  $all_categories = find_by_sql("SELECT id, name FROM categories");

  // --- CONSTRUIR CONSULTA ---
  $sql = "
    SELECT p.*, c.name AS categorie, m.file_name AS image
    FROM products p
    LEFT JOIN categories c ON c.id = p.categorie_id
    LEFT JOIN media m ON m.id = p.media_id
    WHERE p.status = 'Activo'
  ";

  if ($search_brand != '') {
    $sql .= " AND p.brand LIKE '%{$search_brand}%'";
  }

  if ($search_model != '') {
    $sql .= " AND p.model LIKE '%{$search_model}%'";
  }

  if ($search_category != '') {
    $sql .= " AND c.name = '{$search_category}'";
  }

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
      <?php
// Capturar filtros
$search_brand = isset($_GET['brand']) ? remove_junk($db->escape($_GET['brand'])) : '';
$search_model = isset($_GET['model']) ? remove_junk($db->escape($_GET['model'])) : '';
$search_category = isset($_GET['category']) ? remove_junk($db->escape($_GET['category'])) : '';

// Traer categorías dinámicamente
$all_categories = find_by_sql("SELECT id, name FROM categories");
  ?>
  <form method="GET" action="product.php" class="clearfix" style="margin-bottom: 15px;">
    <div class="row">
      
      <div class="col-md-3">
        <input type="text" name="brand" class="form-control"
              placeholder="Buscar por marca..."
              value="<?php echo $search_brand; ?>">
      </div>

      <div class="col-md-3">
        <input type="text" name="model" class="form-control"
              placeholder="Buscar por modelo..."
              value="<?php echo $search_model; ?>">
      </div>

      <div class="col-md-3">
        <select name="category" class="form-control">
          <option value="">Todas las categorías</option>
          <?php foreach ($all_categories as $cat): ?>
            <option value="<?php echo $cat['name']; ?>"
              <?php if ($search_category == $cat['name']) echo "selected"; ?>>
              <?php echo strtoupper($cat['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-block">Buscar</button>
      </div>

    </div>
  </form>

        <th>Lista de productos</th>
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
