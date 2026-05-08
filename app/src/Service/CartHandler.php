<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    public function __construct(
        private RequestStack $request,
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
        private CartRepository $cartRepository,
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
        $cartSession = $session->get('cart', []);
        if (empty($cartSession)) return;

        $cart = $this->cartRepository->findOneBy([
            'user' => $user,
            'status' => Cart::OPEN
        ]);

        if (!$cart) {
            $cart = new Cart($user);
        }
        
        $productIds = array_keys($cartSession);
        $products = $this->productRepository->findByIds($productIds);
        // indexation des produits par ID (optimisation)
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }

        foreach ($cartSession as $productId => $qty) {      
            // Le produit aurait pu être supprimé par l'admin entre temps      
            if (!isset($productsById[$productId])) continue;

            $product = $productsById[$productId];

            // On gère le cas où un produit est dans le panier en BDD et aussi dans le panier en session
            $cartLine = null;
            foreach ($cart->getCartLines() as $line) {
                if ($line->getProduct()->getId() === $product->getId()) {
                    $cartLine = $line;
                    break;
                }
            }

            if ($cartLine) {
                $cartLine->setQuantity($cartLine->getQuantity() + $qty);
            } else {
                $cartLine = new CartLine();
                $cartLine->setProduct($product)->setQuantity($qty);
                $cart->addCartLine($cartLine);
            }
        }

        try {
            $this->em->persist($cart);
            $this->em->flush();
            $session->remove('cart');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
