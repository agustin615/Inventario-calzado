<?php
  require_once('includes/load.php');
  page_require_level(3);

  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $session->msg('d','ID vacío.');
    redirect('sales.php');
  }

  $sale_id = (int) $_GET['id'];

  // comprobar que la venta existe
  $sale = find_by_id('sales', $sale_id);
  if (!$sale) {
    $session->msg('d','Venta no encontrada.');
    redirect('sales.php');
  }

  // obtener los detalles de la venta
  $sql_det = "SELECT producto_id, variant_id, cantidad 
              FROM detalle_venta
              WHERE venta_id = {$sale_id}";
  $detalles = find_by_sql($sql_det);

  // empezar transacción
  $db->query("START TRANSACTION");
  $ok = true;

  // restaurar stock
  foreach ($detalles as $d) {
    $variant = (int)$d['variant_id'];
    $qty = (int)$d['cantidad'];

    if ($variant > 0) {
      $sql_up = "UPDATE product_variants 
                 SET stock = stock + {$qty}
                 WHERE id = {$variant} LIMIT 1";

      if (!$db->query($sql_up)) {
        $ok = false;
        break;
      }
    }
  }

  // eliminar detalle_venta
  if ($ok) {
    $sql_del_det = "DELETE FROM detalle_venta WHERE venta_id = {$sale_id}";
    if (!$db->query($sql_del_det)) {
      $ok = false;
    }
  }

  // eliminar venta
  if ($ok) {
    $sql_del_sale = "DELETE FROM sales WHERE id = {$sale_id} LIMIT 1";
    if (!$db->query($sql_del_sale)) {
      $ok = false;
    }
  }

  // commit o rollback
  if ($ok) {
    $db->query("COMMIT");
    $session->msg('s','Venta eliminada y stock restaurado correctamente.');
  } else {
    $db->query("ROLLBACK");
    $session->msg('d','Error al eliminar la venta. No se aplicaron cambios.');
  }

  redirect('sales.php');
?>
