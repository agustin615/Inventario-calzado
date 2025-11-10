<?php
  require_once('includes/load.php');
  // Verificar nivel de usuario
  page_require_level(3);

  // Verificar si se pasó un ID válido
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $session->msg("d", "ID de venta no válido.");
    redirect('sales.php');
  }

  $sale_id = (int)$_GET['id'];
  $sale = find_by_id('sales', $sale_id);

  if (!$sale) {
    $session->msg("d", "Venta no encontrada.");
    redirect('sales.php');
  }

  // Recuperar datos de la venta
  $product_id = (int)$sale['product_id'];
  $variant_id = (int)$sale['variant_id'];
  $qty = (int)$sale['qty'];

  // Devolver el stock al inventario
  $sql_update = "UPDATE product_variants 
                 SET stock = stock + {$qty} 
                 WHERE id = '{$variant_id}' 
                 AND product_id = '{$product_id}'";

  $db->query($sql_update);

  // Eliminar la venta
  $delete_id = delete_by_id('sales', $sale_id);

  if ($delete_id) {
      $session->msg("s", "Venta eliminada y stock restaurado correctamente.");
  } else {
      $session->msg("d", "No se pudo eliminar la venta.");
  }

  redirect('sales.php');
?>
