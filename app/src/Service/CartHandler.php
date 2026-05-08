<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct(
        private RequestStack $request,
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
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

            $totalPrice += $qty * (float)$product->getPrice();
        }

        return [
            'items' => $items,
            'totalPrice' => round((float) $totalPrice, 2)
            ];
    }

    public function getTotalQuantity(): int
    {
        $cart = $this->request->getSession()->get('cart', []);

        return array_sum($cart);
    }

    public function persistCart(User $user)
    {
        $session = $this->request->getSession();
        if (!$session->get('cart')) return;

        $cart = new Cart($user);

        foreach ($session->get('cart') as $productId => $qty) {            
            $product = $this->productRepository->find($productId);

            if (!$product) continue;

            $cartLine = new CartLine;
            $cartLine->setProduct($product)
                ->setQuantity($qty)
                ;
            $cart->addCartLine($cartLine);
        }

        $this->em->persist($cart);
        $this->em->flush();

        $session->remove('cart');
    }
}
