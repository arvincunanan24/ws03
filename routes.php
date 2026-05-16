<?php

// return [
//     '/' => 'Controllers/home.php',
//     '/listings' => 'Controllers/listings/index.php',
//     '/listings/create' => 'Controllers/listings/create.php',
//     '404' => 'Controllers/error/404.php'
// ];

$router->get('/', 'controllers/home.php');
$router->get('/listings', 'controllers/listings/index.php');
$router->get('/listings/create', 'controllers/listings/create.php');
