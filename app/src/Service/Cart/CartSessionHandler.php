<?php

namespace App\Service\Cart;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\RequestStack;

class CartSessionHandler implements CartStorageInterface
{
    public function __construct(private RequestStack $request) {}
     
    public function add(Product $product): array
    {
        $session = $this->request->getSession();
        
        $cart = $session->get('cart', []);
        $productId = (int)$product->getId();
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;
        $session->set('cart', $cart);

        return $cart;
    }
    
    public function remove(Product $product): array
    {
        $session = $this->request->getSession();

        $cart = $session->get('cart', []);
        $productId = $product->getId();

        if (!isset($cart[$productId])) {
            return $cart;
        }

        $cart[$productId]--;

        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }

        $session->set('cart', $cart);

        return $cart;
    }

    public function clear(): void
    {
        $this->request->getSession()->remove('cart');
    }

    public function getCart(): array
    {
        return $this->request->getSession()->get('cart', []);
    }
}
