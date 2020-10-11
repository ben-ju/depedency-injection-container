<?php

use App\Container;

require "vendor/autoload.php";



$container = Container::getInstance();

try {
    $container->register('Database');
} catch (Exception $e) {
echo $e;
}