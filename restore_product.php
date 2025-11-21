<?php
require_once('includes/load.php');
page_require_level(2);

$product_id = (int)$_GET['id'];

$product = find_by_id('products', $product_id);
if (!$product) {
    $session->msg("d", "Producto no encontrado.");
    redirect('product.php');
}

// Restaurar correctamente (status + estado)
$sql = "UPDATE products 
        SET status = 'Activo', estado = 1 
        WHERE id = '{$product_id}' 
        LIMIT 1";

$result = $db->query($sql);

if ($result) {
    $session->msg("s", "Producto restaurado correctamente.");
} else {
    $session->msg("d", "No se pudo restaurar el producto.");
}

redirect('product.php');
?>
