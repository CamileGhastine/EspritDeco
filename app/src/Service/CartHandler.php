<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct(
        private RequestStack $request,
        private ProductRepository $productRepository,
        ) {}

    public function getCart(): array
    {
        $cart = $this->request->getSession()->get('cart', []);

        $items = [];
        $totalPrice = 0;

        foreach ($cart as $productId => $qty) {
            $product = $this->productRepository->find($productId);

            if (!$product) continue;

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
            ];

            $totalPrice += $qty * $product->getPrice();
        }

        return [
            'items' => $items,
            'totalPrice' => $totalPrice
            ];
    }
}