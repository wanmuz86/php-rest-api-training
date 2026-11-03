<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ProductService
{
    public function __construct(
        private string $file
    ) {}

    // read 
    private function read(): array
    {
        // Read from file => read from products.json
        $contents = file_get_contents($this->file);
        if ($contents === false) {
            throw new RuntimeException('Unable to read product data');
        }

        // if exists, transform from JSON into PHP
        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    //  write file  , in this case it will write products.json file
    private function write(array $products): void
    {
        // Encode the data, the file will be stored as JSON
        $json = json_encode($products, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        // To write content into php file
        if (file_put_contents($this->file, $json, LOCK_EX) === false) {
            throw new RuntimeException('Unable to save product data');
        }
    }

    //will be used by index()
    public function all(): array
    {
        return $this->read();   // baca semua yang ada di dalam file
    }

    // ProductController -> show
    public function find(int $id): ?array
    {
        foreach ($this->read() as $product) { // read all the data in file
            if ($product['id'] === $id) { // for each of the data, check first if the id is the same as given id
                return $product;
            }
        }

        return null; // if it is not there, return null
    }

    // 
    public function create(array $data): array
    {
        $products = $this->read(); /// read all the data in json

        // to perform auto increment.
        // 1 or max , id terakhir + 1
        $id = empty($products) ? 1 : max(array_column($products, 'id')) + 1;


        // json data from the body + id
        $product = array_merge(['id' => $id], $data);
        $products[] = $product;  // array_push (add the product inside $products[] array)

        // Rewrite the json file
        $this->write($products);
        return $product;
    }

    public function update(int $id, array $data): ?array
    {
        $products = $this->read(); // read from file

        foreach ($products as &$product) { //go through 1 by 1 
            if ($product['id'] === $id) {  // check if the id exist
            
            // update the found product with the updated data (body)
                $product = array_merge($product, $data, ['id' => $id]); 
                $updated = $product;
                unset($product);

                // tulis semula di dalam file dengan updated product
                $this->write($products);
                return $updated;
            }
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $products = $this->read(); // read from file
        $filtered = array_values(
            // [1,2,3,4,5]
            // num % 2 = 0 (baki bahagi sama dengan 0 ) = genap
            // [2,4]

            // $id = 3
            // [["id"=>1,...], ["id"=>2,...], ["id"=>3,...]]
            // $product['id'] !== $id (syarat)
            
            //["id"=>1,...], ["id"=>2,...]]
            
            array_filter($products, fn($product) => $product['id'] !== $id)
        );

        if (count($filtered) === count($products)) {
            return false;
        }

        $this->write($filtered);
        return true;
    }
}
