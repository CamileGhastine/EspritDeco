<?php

namespace App\Service\Cart;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Cart\CartDbHandler;
use App\Service\Cart\CartSessionHandler;
use Symfony\Bundle\SecurityBundle\Security;

class CartHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private Security $security,
        private CartSessionHandler $cartSessionHandler,
        private CartDbHandler $cartDbHandler
        ) {}

    public function addToCart(Product $product): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            $cart = $this->cartSessionHandler->add($product);
        } else {
            $cart = $this->cartDbHandler->add($product);
        }

        return $cart;
    }

    public function removeFromCart(Product $product): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            $cart = $this->cartSessionHandler->remove($product);
        } else {
            $cart = $this->cartDbHandler->remove($product);
        }

        return $cart;
    }

    public function clearCart(): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            $this->cartSessionHandler->clear();
        } else {
            $this->cartDbHandler->clear();
        }
    }

    public function getCartDetails(): array
    {
        $items = [];
        $totalPrice = 0;
        $user = $this->security->getUser();
        
        if (!$user) {
            $cart = $this->cartSessionHandler->getCart();
            foreach ($cart as $productId => $qty) {
                $product = $this->productRepository->find($productId);

                if (!$product) continue;

                $items[] = [
                    'product' => $product,
                    'quantity' => $qty,
                ];

                $totalPrice += $qty * (float)$product->getPrice();
            }
        } else {
            $cart = $this->cartDbHandler->getCart();

            foreach ($cart->getCartLines() as $cartLine) {
                $product = $cartLine->getProduct();

                if (!$product) continue;

                $items[] = [
                    'product' => $product,
                    'quantity' => $cartLine->getQuantity(),
                ];

                $totalPrice += $cartLine->getQuantity() * (float)$product->getPrice();
            }
        } 

        return [
            'items' => $items,
            'totalPrice' => round((float) $totalPrice, 2)
            ];
    }

    public function getTotalQuantity(): int
    {       
        $user = $this->security->getUser();
 
        if (!$user) {
            $cart = $this->cartSessionHandler->getCart();
            $totalItems = array_sum($cart);
        } else {
            $cart = $this->cartDbHandler->getCart();
            $totalItems = 0;
            foreach ($cart->getCartLines() as $cartLine) {
                $totalItems += $cartLine->getQuantity();
            }
        }

        return $totalItems;
    }

    // Appelée par l'EventSUbscriber CartSubscriber
    public function persistCart()
    {
        $cartSession = $this->cartSessionHandler->getCart();
        if (empty($cartSession)) return;
        
        $cartDb = $this->cartDbHandler->getCart();

        $products = $this->loadProductsFromSession($cartSession);
        $this->cartDbHandler->transferSessionCartToDatabase($cartDb, $cartSession, $products);
        
        $this->cartSessionHandler->clear();
    }

    private function loadProductsFromSession(array $cartSession): array
    {
        // Les clefs de $cartSession sont les ids des produits dans le panier
        $products = $this->productRepository->findByIds(array_keys($cartSession));
        
        // indexation des produits par id pour optimiser le transfert du panier en session en bdd
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }
        
        return $productsById;
    }
}
