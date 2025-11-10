<?php
require_once('includes/load.php');
if (!$session->isUserLoggedIn()) { redirect('index.php', false); }

$html = '';
?>
