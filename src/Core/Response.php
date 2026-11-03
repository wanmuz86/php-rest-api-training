<?php

declare(strict_types=1);

namespace App\Core;

// Class name  Response

class Response
{

// Nama method json
// parameters  - status defaulted at 200
// The purpose of this function is to create a standard response
// that contains: status code, response  body format and body
    public static function json(mixed $data, int $status = 200): void
    {
        // Set the status code
        http_response_code($status);
        // Set the content type - the body will be in json
        // If you want to reply xml : application/xml
        header('Content-Type: application/json; charset=utf-8');

        // Success for delete => will not return body
        if ($status === 204) {
            return;
        }

        // return body
        // transform php/data in json format
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
    }
}
