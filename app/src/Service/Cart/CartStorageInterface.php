<?php

namespace App\Service\Cart;

use App\Entity\Product;

interface CartStorageInterface
{
    public function add(Product $product): array;
    public function remove(Product $product): array;
    public function clear(): void;
    public function getCart();
}