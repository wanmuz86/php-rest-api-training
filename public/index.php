<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Response;
use App\Controllers\HealthController;
use App\Controllers\ProductController;
use App\Services\ProductService;

// Create a new Router instance // arrays that will store our routes
$router = new Router();

// ProductService needs to be initialized before Controller
$service = new ProductService(__DIR__ . '/../storage/products.json');
// ProductService injectied into Controller
$controller = new ProductController($service);

$router->add('GET', '/api/health', function () {
    $health = new HealthController();
    Response::json($health->index());  // HealthController method 'index'
});

// /api/products   => It will call 'index' method (ProductController)
$router->add('GET', '/api/products', [$controller, 'index']);
// /api/products/1   => It will call 'show' method (ProductController)
$router->add('GET', '/api/products/{id}', [$controller, 'show']);
// /api/products (POST)   => It will call 'store' method (ProductController)
$router->add('POST', '/api/products', [$controller, 'store']);
$router->add('PUT', '/api/products/{id}', [$controller, 'update']);
$router->add('PATCH', '/api/products/{id}', [$controller, 'patch']);
$router->add('DELETE', '/api/products/{id}', [$controller, 'destroy']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
