<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\ProductService;
use JsonException;

class ProductController
{
    public function __construct(
        // Service -> Digunakan oleh controller
        // We need to instatiate service before using Controller
        private ProductService $service
    ) {}

    private function body(): ?array
    {
        $raw = file_get_contents('php://input');

        try {
            // JSON convert to PHP
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {

        // If wrong format, throw 400= > Malformed JSON , tertinggal {},[]
            Response::json(['error' => 'Malformed JSON'], 400);
            return null;
        }

        // array or array associative
        if (!is_array($data) || array_is_list($data)) {
            // throw an error , Expected JSON
            Response::json(['error' => 'Expected a JSON object'], 400);
            return null;
        }

        return $data;
    }

    // perform validation
    private function validate(array $data, bool $partial = false): array
    {
        $errors = [];
        // name description price and stock
        $fields = ['name', 'description', 'price', 'stock'];

        // make sure semua diisi, semua dihantar
        // mandatory
        foreach ($fields as $field) {
            if (!$partial && !array_key_exists($field, $data)) {
                $errors[$field] = 'Field is required';
            }
        }

        // in_array
        // make sure only send wjhatever in $fields
        // eg if i send "color" -> column/field does not exist
        foreach (array_keys($data) as $field) {
            if (!in_array($field, $fields, true)) {
                $errors[$field] = 'Unknown field';
            }
        }

        // name tak bleh kosong, dan mesti string
        if (array_key_exists('name', $data) && (!is_string($data['name']) || trim($data['name']) === '')) {
            $errors['name'] = 'Must be a non-empty string';
        }

        // description tidak boleh kosong dan mestri string
        if (array_key_exists('description', $data) && !is_string($data['description'])) {
            $errors['description'] = 'Must be a string';
        }

        // price tidak boleh kosong dan mesti integer
        if (array_key_exists('price', $data) && (!is_numeric($data['price']) || !is_int($data['price']) && !is_float($data['price']) || !is_finite((float) $data['price']) || $data['price'] < 0)) {
            $errors['price'] = 'Must be a non-negative number';
        }

        // stock tidak boleh kosog dan mesti integer dan tidak boleh negative
        if (array_key_exists('stock', $data) && (!is_int($data['stock']) || $data['stock'] < 0)) {
            $errors['stock'] = 'Must be a non-negative integer';
        }

        return $errors;
    }

    public function index(): void
    {
        // panggil function all() di dalam service
        Response::json(['data' => $this->service->all()]);
    }

    public function show(string $id): void
    {
        // panggil function find() di dalam service
        $product = $this->service->find((int) $id);

        if ($product === null) {
            // kalau tak jumpa dia akan return 404
            Response::json(['error' => 'Product not found'], 404);
            return;
        }

        Response::json(['data' => $product]);
    }

    public function store(): void
    {
        $data = $this->body();
        if ($data === null) {
            return;
        }

        // validate
        $errors = $this->validate($data);
        if ($errors !== []) {
            Response::json([
                'error'   => 'Validation failed',
                'details' => $errors
            ], 422);
            return;
        }

        // panggil function create() di dalam service
        $product = $this->service->create($data);
        header('Location: /api/products/' . $product['id']);
        Response::json(['data' => $product], 201);
    }

    public function update(string $id): void
    {
        $data = $this->body();
        if ($data === null) {
            return;
        }
        // validate
        $errors = $this->validate($data);
        if ($errors !== []) {
            Response::json([
                'error'   => 'Validation failed',
                'details' => $errors
            ], 422);
            return;
        }

        // panggil function update() di dalam service
        $product = $this->service->update((int) $id, $data);
        if ($product === null) {
            Response::json(['error' => 'Product not found'], 404);
            return;
        }

        Response::json(['data' => $product]);
    }

public function patch(string $id): void
{
    // Step 1: Read request body
    $data = $this->body();
    if ($data === null) {
        return;
    }
    // Step 2: Validate partial data
    $errors = $this->validate($data, true);
    if ($errors !== []) {
        Response::json([
            'error'   => 'Validation failed',
            'details' => $errors
        ], 422);
        return;
    }
    // Step 3: Check if product exists
    $existing = $this->service->find((int) $id);
    if ($existing === null || $existing === false) {
        Response::json([
            'error' => 'Product not found'
        ], 404);
        return;
    }
    // Step 4: Update only supplied fields
    $updated = $this->service->update(
        (int) $id,
        $data
    );
    if (!$updated) {
        Response::json([
            'error' => 'Failed to update product'
        ], 500);
        return;
    }
    // Step 5: Retrieve updated product
    $product = $this->service->find((int) $id);
    // Step 6: Return updated product
    Response::json([
        'data' => $product
    ], 200);
}

    public function destroy(string $id): void
    {
        // terus panggil function delete
        $deleted = $this->service->delete((int) $id);
        if (!$deleted) {
            Response::json(['error' => 'Product not found'], 404);
            return;
        }

        Response::json(null, 204);
    }
}
