<?php

declare(strict_types=1);

namespace App\Controllers;

// class Name
class HealthController
{

    // method di dalam class
    public function index(): array
    {
        // Return array associatitive
        // status ok, message :PHP REST API is running, gmt time
        return [
            'status' => 'ok',
            'message' => 'PHP REST API is running',
            'timestamp' => gmdate('c')
        ];
    }
}
