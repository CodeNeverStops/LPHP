<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('Asia/Shanghai');
$config = dirname(__FILE__).'/app/config/main.php';
$framework = dirname(__FILE__).'/../../framework/L.php';
require_once $framework;
L::createApp($config)->run();

?>
