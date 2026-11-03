<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    // Create a new arrays called routes
private array $routes = [];


    // Create a new methods , called add
    // Creating a routes array
public function add(string $method, string $path, callable $handler): void
{

// Add this information into routes array
    $this->routes[] = [
        'method'  => strtoupper($method),    /// POST, PUT, GET , DELETE
        'path'    => $path,      // products, /products/{id} - PATH definition
        'handler' => $handler   // which controller and which method to be called
    ];
}

// creae a new method called it dispatch
// Function to match the uri to check if it exists..
public function dispatch(string $method, string $uri): void
{
    $path = parse_url($uri, PHP_URL_PATH);
    $allowedMethods = [];

    foreach ($this->routes as $route) {

        // replace any number with {id}

        // /products/1 => /products/{id}
        $pattern = preg_replace('/\{id\}/', '([0-9]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';

        // Is there same path definitionn fron $routes /products/{id}
        if (!preg_match($pattern, $path, $matches)) {
            continue;  // go to the next route  (keluar loop sekarang)
        }

        // Check method , POST , PUT, DELETE
        if ($route['method'] !== strtoupper($method)) {
            $allowedMethods[] = $route['method'];
            continue; // if it is not the same, try again on the next iteration
        }

        array_shift($matches);
        call_user_func_array($route['handler'], $matches);  // Kalau jumpa, panggil handler
        return;
    }

    if ($allowedMethods !== []) {
        header('Allow: ' . implode(', ', array_unique($allowedMethods)));
        Response::json(['error' => 'Method Not Allowed'], 405);
        return;
    }

    Response::json(['error' => 'Endpoint Not Found'], 404);
}
}
