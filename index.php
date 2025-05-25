<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Facades\Router;

$router = new Router();
$router->dispatch();
