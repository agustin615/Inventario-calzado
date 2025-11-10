<?php
$page_title = 'Editar producto';
require_once('includes/load.php');
page_require_level(2);

// Obtener producto y validar existencia
$product = find_by_id('products', (int)$_GET['id']);
if(!$product){
    $session->msg("d","ID de producto no válido.");
    redirect('product.php');
}

// Obtener datos base
$all_categories = find_all('categories');
$all_photo = find_all('media');

// Obtener variaciones del producto
$product_variants = find_by_sql("SELECT * FROM product_variants WHERE product_id = '{$product['id']}'");
?>

<?php
// GUARDAR CAMBIOS
if(isset($_POST['product'])){
    $req_fields = array('product-brand','product-categorie','buying-price','saleing-price');
    validate_fields($req_fields);

    if(empty($errors)){
        $p_brand  = strtoupper(remove_junk($db->escape($_POST['product-brand'])));
        $p_cat    = (int)$_POST['product-categorie'];
        $p_buy    = round(remove_junk($db->escape($_POST['buying-price'])), 0);  // Sin decimales
        $p_sale   = round(remove_junk($db->escape($_POST['saleing-price'])), 0); // Sin decimales
        $p_model  = strtoupper(remove_junk($db->escape($_POST['product-model'])));
        $media_id = empty($_POST['product-photo']) ? '0' : remove_junk($db->escape($_POST['product-photo']));

        // Actualizar producto
        $query  = "UPDATE products SET ";
        $query .= "brand ='{$p_brand}', buy_price ='{$p_buy}', sale_price ='{$p_sale}', ";
        $query .= "categorie_id ='{$p_cat}', media_id='{$media_id}', model='{$p_model}' ";
        $query .= "WHERE id ='{$product['id']}'";
        $db->query($query);

        // Actualizar/eliminar variantes existentes
        if(isset($_POST['variants'])){
            foreach($_POST['variants'] as $variant_id => $data){
                if(isset($data['delete']) && $data['delete'] == "1"){
                    // Eliminar variante marcada
                    $db->query("DELETE FROM product_variants WHERE id='{$variant_id}'");
                } else {
                    // Actualizar variante existente
                    $color = strtoupper(remove_junk($db->escape($data['color'])));
                    $size  = strtoupper(remove_junk($db->escape($data['size'])));
                    $stock = (int)$data['stock'];

                    $update_variant = "UPDATE product_variants 
                                       SET color='{$color}', size='{$size}', stock='{$stock}' 
                                       WHERE id='{$variant_id}'";
                    $db->query($update_variant);
                }
            }
        }

        // Agregar nuevas variantes
        if(isset($_POST['new_variant_color'])){
            foreach($_POST['new_variant_color'] as $index => $color){
                $v_color = strtoupper(remove_junk($db->escape($color)));
                $v_size  = strtoupper(remove_junk($db->escape($_POST['new_variant_size'][$index])));
                $v_stock = (int)$_POST['new_variant_stock'][$index];

                if($v_color != '' && $v_size != ''){ // Evitar insertar vacíos
                    $query_variant = "INSERT INTO product_variants (product_id, color, size, stock)
                                      VALUES ('{$product['id']}', '{$v_color}', '{$v_size}', '{$v_stock}')";
                    $db->query($query_variant);
                }
            }
        }

        $session->msg('s',"Producto y variantes actualizados correctamente.");
        redirect('product.php', false);
    } else {
        $session->msg("d", $errors);
        redirect('edit_product.php?id='.$product['id'], false);
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
  <div class="panel panel-default">
    <div class="panel-heading">
      <strong><span class="glyphicon glyphicon-th"></span> Editar producto</strong>
    </div>
    <div class="panel-body">
      <div class="col-md-7">
        <form method="post" action="edit_product.php?id=<?php echo (int)$product['id'] ?>">

          <!-- Marca -->
          <div class="form-group">
            <label>Marca</label>
            <input type="text" class="form-control" name="product-brand" value="<?php echo remove_junk($product['brand']);?>">
          </div>

          <!-- Modelo -->
          <div class="form-group">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="glyphicon glyphicon-tags"></i>
              </span>
              <input type="text" class="form-control" name="product-model" 
                     placeholder="Modelo / Uso (ej: Fútbol, Trekking, Calcetines largos)" 
                     value="<?php echo remove_junk($product['model']); ?>">
            </div>
          </div>

          <!-- Categoría e Imagen -->
          <div class="form-group">
            <div class="row">
              <div class="col-md-6">
                <label>Categoría</label>
                <select class="form-control" name="product-categorie">
                  <option value="">Selecciona una categoría</option>
                  <?php foreach ($all_categories as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php if($product['categorie_id'] == $cat['id']) echo 'selected'; ?>>
                      <?php echo remove_junk($cat['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label>Imagen</label>
                <select class="form-control" name="product-photo">
                  <option value="">Sin imagen</option>
                  <?php foreach ($all_photo as $photo): ?>
                    <option value="<?php echo (int)$photo['id'];?>" <?php if($product['media_id'] == $photo['id']) echo 'selected'; ?>>
                      <?php echo $photo['file_name']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Precios -->
          <div class="form-group">
            <div class="row">
              <div class="col-md-6">
                <label>Precio de compra</label>
                <input type="number" class="form-control" name="buying-price" 
                       value="<?php echo intval($product['buy_price']); ?>">
              </div>
              <div class="col-md-6">
                <label>Precio de venta</label>
                <input type="number" class="form-control" name="saleing-price" 
                       value="<?php echo intval($product['sale_price']); ?>">
              </div>
            </div>
          </div>

          <!-- Variaciones existentes -->
          <hr>
          <h4>Variaciones existentes</h4>
          <?php if(!empty($product_variants)): ?>
            <?php foreach($product_variants as $variant): ?>
              <div class="variant-box" id="variant-<?php echo $variant['id']; ?>" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:5px;">
                <div class="row">
                  <div class="col-md-3">
                    <label>Color</label>
                    <input type="text" class="form-control" name="variants[<?php echo $variant['id'];?>][color]" value="<?php echo remove_junk($variant['color']);?>">
                  </div>
                  <div class="col-md-3">
                    <label>Talla</label>
                    <input type="text" class="form-control" name="variants[<?php echo $variant['id'];?>][size]" value="<?php echo remove_junk($variant['size']);?>">
                  </div>
                  <div class="col-md-3">
                    <label>Stock</label>
                    <input type="number" class="form-control" name="variants[<?php echo $variant['id'];?>][stock]" value="<?php echo remove_junk($variant['stock']);?>">
                  </div>
                  <div class="col-md-3" style="display:flex;align-items:end;">
                    <button type="button" class="btn btn-danger btn-sm remove-existing-variant" data-id="<?php echo $variant['id']; ?>">❌ Eliminar</button>
                  </div>
                </div>
                <input type="hidden" name="variants[<?php echo $variant['id'];?>][delete]" value="0">
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No hay variaciones registradas para este producto.</p>
          <?php endif; ?>

          <!-- Agregar nuevas variantes -->
          <hr>
          <h4>Agregar nuevas variantes</h4>
          <table class="table table-bordered" id="new-variants-table">
            <tr>
              <th>Color</th>
              <th>Talla</th>
              <th>Stock</th>
              <th>
                <button type="button" id="add-new-variant" class="btn btn-success btn-sm">+</button>
              </th>
            </tr>
            <tr>
              <td><input type="text" name="new_variant_color[]" class="form-control"></td>
              <td><input type="text" name="new_variant_size[]" class="form-control"></td>
              <td><input type="number" name="new_variant_stock[]" class="form-control" min="0"></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-variant">x</button></td>
            </tr>
          </table>

          <button type="submit" name="product" class="btn btn-success">Guardar cambios</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Eliminar variantes existentes con confirmación
document.querySelectorAll('.remove-existing-variant').forEach(button => {
    button.addEventListener('click', function(){
        if(confirm('¿Seguro que quieres eliminar esta variante?')){
            const variantId = this.dataset.id;
            document.querySelector('#variant-' + variantId + ' input[name$="[delete]"]').value = 1;
            document.getElementById('variant-' + variantId).style.display = 'none';
        }
    });
});

// Añadir/eliminar nuevas variantes
document.getElementById('add-new-variant').addEventListener('click', function(){
  var table = document.getElementById('new-variants-table');
  var row = table.insertRow(-1);
  row.innerHTML = `
    <td><input type="text" name="new_variant_color[]" class="form-control"></td>
    <td><input type="text" name="new_variant_size[]" class="form-control"></td>
    <td><input type="number" name="new_variant_stock[]" class="form-control" min="0"></td>
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
