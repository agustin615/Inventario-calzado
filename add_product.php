<?php
$page_title = 'Agregar producto';
require_once('includes/load.php');
page_require_level(2);
$all_categories = find_all('categories');
$all_photo = find_all('media');
?>

<?php
if(isset($_POST['add_product'])){
    $req_fields = array('product-title','product-categorie','buying-price','saleing-price');
    validate_fields($req_fields);

    if(empty($errors)){
        // Convertir a mayúsculas los campos de texto
        $p_name  = strtoupper(remove_junk($db->escape($_POST['product-title'])));
        $p_cat   = (int) $_POST['product-categorie'];
        
        // 🔹 Quitar decimales si el usuario los escribe
        $p_buy   = (int)remove_junk($db->escape($_POST['buying-price']));
        $p_sale  = (int)remove_junk($db->escape($_POST['saleing-price']));

        $p_model = strtoupper(remove_junk($db->escape($_POST['product-model'])));
        $date    = make_date();

        $media_id = (empty($_POST['product-photo'])) ? 0 : remove_junk($db->escape($_POST['product-photo']));

        // Insertar producto
        $query  = "INSERT INTO products (brand, categorie_id, buy_price, sale_price, media_id, model, date)
                   VALUES ('{$p_name}', '{$p_cat}', '{$p_buy}', '{$p_sale}', '{$media_id}', '{$p_model}', '{$date}')";

        $result = $db->query($query);
        if ($result !== false) {
            $product_id = $db->insert_id();

            // Guardar variantes
            if (isset($_POST['variant_color'])) {
                foreach ($_POST['variant_color'] as $index => $color) {
                    $v_color = strtoupper(remove_junk($db->escape($color)));
                    $v_size  = strtoupper(remove_junk($db->escape($_POST['variant_size'][$index])));
                    $v_stock = (int) $_POST['variant_stock'][$index];

                    $query_variant = "INSERT INTO product_variants (product_id, color, size, stock)
                                      VALUES ('{$product_id}', '{$v_color}', '{$v_size}', '{$v_stock}')";
                    $db->query($query_variant);
                }
            }

            $session->msg('s', "Producto y variantes agregados exitosamente.");
            redirect('add_product.php', false);
        } else {
            $session->msg('d', 'Lo siento, el registro del producto falló.');
            redirect('product.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('add_product.php', false);
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
  <div class="col-md-9">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Agregar producto</span>
        </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="add_product.php" class="clearfix">

          <!-- Marca -->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-th-large"></i></span>
              <input type="text" class="form-control" name="product-title" placeholder="Marca" required>
            </div>
          </div>

          <!-- Categoría e Imagen -->
          <div class="form-group">
            <div class="row">
              <div class="col-md-6">
                <select class="form-control" name="product-categorie" required>
                  <option value="">Selecciona una categoría</option>
                  <?php foreach ($all_categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id'] ?>">
                      <?php echo $cat['name'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <select class="form-control" name="product-photo">
                  <option value="">Selecciona una imagen</option>
                  <?php foreach ($all_photo as $photo): ?>
                    <option value="<?php echo (int)$photo['id'] ?>">
                      <?php echo $photo['file_name'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Modelo -->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon"><i class="glyphicon glyphicon-tags"></i></span>
              <input type="text" class="form-control" name="product-model" placeholder="Modelo / Uso (ej: Fútbol, Trekking)">
            </div>
          </div>

          <!-- Precios -->
          <div class="form-group">
            <div class="row">
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
                  <input type="number" class="form-control" name="buying-price" placeholder="Precio de compra" min="0" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="input-group">
                  <span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
                  <input type="number" class="form-control" name="saleing-price" placeholder="Precio de venta" min="0" required>
                </div>
              </div>
            </div>
          </div>

          <!-- Variantes -->
          <div class="form-group">
            <label>Variantes (Color, Talla, Stock)</label>
            <table class="table table-bordered" id="variants-table">
              <tr>
                <th>Color</th>
                <th>Talla</th>
                <th>Stock</th>
                <th><button type="button" id="add-variant" class="btn btn-success btn-sm">+</button></th>
              </tr>
              <tr>
                <td><input type="text" name="variant_color[]" class="form-control" required></td>
                <td><input type="text" name="variant_size[]" class="form-control" required></td>
                <td><input type="number" name="variant_stock[]" class="form-control" min="0" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-variant">x</button></td>
              </tr>
            </table>
          </div>

          <button type="submit" name="add_product" class="btn btn-danger">Agregar producto</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JS variantes -->
<script>
document.getElementById('add-variant').addEventListener('click', function(){
  var table = document.getElementById('variants-table');
  var row = table.insertRow(-1);
  row.innerHTML = `
    <td><input type="text" name="variant_color[]" class="form-control" required></td>
    <td><input type="text" name="variant_size[]" class="form-control" required></td>
    <td><input type="number" name="variant_stock[]" class="form-control" min="0" required></td>
    <td><button type="button" class="btn btn-danger btn-sm remove-variant">x</button></td>
  `;
});

document.addEventListener('click', function(e){
  if(e.target && e.target.classList.contains('remove-variant')){
    e.target.closest('tr').remove();
  }
});
</script>

<?php include_once('layouts/footer.php'); ?>
