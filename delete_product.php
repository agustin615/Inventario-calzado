<?php
require_once('includes/load.php');
page_require_level(2);

$product_id = (int)$_GET['id'];

$product = find_by_id('products', $product_id);
if (!$product) {
    $session->msg("d", "Producto no encontrado.");
    redirect('product.php');
}

// En lugar de borrar, lo descontinuamos
$sql = "UPDATE products SET status = 'Descontinuado' WHERE id = '{$product_id}' LIMIT 1";
$result = $db->query($sql);

if ($result) {
    $session->msg("s", "Producto descontinuado correctamente.");
} else {
    $session->msg("d", "No se pudo descontinuar el producto.");
}

redirect('product.php');
?>
