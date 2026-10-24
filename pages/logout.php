<?php
require_once __DIR__ . '/../private_core/config.php';
require_once __DIR__ . '/../private_core/functions.php';

db_destroySession();
header('Location: index.php');
exit;
?>