<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartHandler
{
    public function __construct(
        private RequestStack $request,
        private ProductRepository $productRepository,
        private EntityManagerInterface $em,
        private CartRepository $cartRepository,
        private Security $security,
        ) {}

    public function getCartDetails(): array
    {
        $items = [];
        $totalPrice = 0;
        $user = $this->security->getUser();
        
        if (!$user) {
            $cart = $this->request->getSession()->get('cart', []);
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
            $cart = $this->getCart($user);

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

    public function addToCart(Product $product): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            $cart = $this->request->getSession()->get('cart', []);
            $productId = $product->getId();
            $cart[$productId] = ($cart[$productId] ?? 0) + 1;
            $this->request->getSession()->set('cart', $cart);            
        } else {
            $cartDb = $this->getCart($user);
            $existingLine = $this->findExistingCartLine($cartDb, $product->getId());
            if ($existingLine) {
                $existingLine->setQuantity($existingLine->getQuantity() + 1);
            } else {
                $cartLine = new CartLine();
                $cartLine->setProduct($product)->setQuantity(1);
                $cartDb->addCartLine($cartLine);
            }
            
            $this->em->persist($cartDb);
            $this->em->flush();
            
            $cart = [];
            foreach ($cartDb->getCartLines() as $cartLine) {
                $cart[$cartLine->getProduct()->getId()] = $cartLine->getQuantity();
            } 
        }

        return $cart;
    }

    public function removeFromCart(Product $product): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            $cart = $this->request->getSession()->get('cart', []);
            $productId = $product->getId();

            if (!isset($cart[$productId])) {
                return $cart;
            }

            $cart[$productId]--;

            if ($cart[$productId] <= 0) {
                unset($cart[$productId]);
            }

            $this->request->getSession()->set('cart', $cart);
        } else {
            $cartDb = $this->getCart($user);
            $existingLine = $this->findExistingCartLine($cartDb, $product->getId());
            if ($existingLine) {
                if ($existingLine->getQuantity() > 1) {
                    $existingLine->setQuantity($existingLine->getQuantity() - 1);
                    $this->em->persist($cartDb);
                } else {
                    $cartDb->removeCartLine($existingLine);
                    $this->em->remove($existingLine);
                }

                if (($cartDb->getCartLines())->count() === 0) {
                    $this->em->remove($cartDb);
                }

                $this->em->flush();
            }
            
            $cart = [];
            foreach ($cartDb->getCartLines() as $cartLine) {
                $cart[$cartLine->getProduct()->getId()] = $cartLine->getQuantity();
            } 
        }

        return $cart;
    }

    public function getTotalQuantity(): int
    {       
        $user = $this->security->getUser();
 
        if (!$user) {
            $cart = $this->request->getSession()->get('cart', []);
            $totalItems = array_sum($cart);
        } else {
            $cart = $this->getCart($user);
            $totalItems = 0;
            foreach ($cart->getCartLines() as $cartLine) {
                $totalItems += $cartLine->getQuantity();
            }
        }

        return $totalItems;
    }

    public function persistCart(User $user)
    {
        $session = $this->request->getSession();
        $cartSession = $session->get('cart', []);
        if (empty($cartSession)) return;

        $cart = $this->getCart($user);

        $products = $this->loadProductsFromSession($cartSession);

        $this->transferSessionCartToDatabase($cart, $cartSession, $products);
        
        $this->em->persist($cart);
        $this->em->flush();
        $session->remove('cart');
    }

    public function getCart(User $user)
    {
        $cart = $this->cartRepository->findOneBy([
            'user' => $user,
            'status' => Cart::OPEN
        ]);

        if (!$cart) {
            $cart = new Cart($user);
        }

        return $cart;
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

    private function transferSessionCartToDatabase(Cart $cart, array $cartSession, array $productsById): void
    {
        foreach ($cartSession as $productId => $qty) {
            // Le produit aurait pu être supprimé par l'admin entre temps
            if (!isset($productsById[$productId])) continue;

            $product = $productsById[$productId];
            $existingLine = $this->findExistingCartLine($cart, $product->getId());

            if ($existingLine) {
                $existingLine->setQuantity($existingLine->getQuantity() + $qty);
            } else {
                $cartLine = new CartLine();
                $cartLine->setProduct($product)->setQuantity($qty);
                $cart->addCartLine($cartLine);
            }
        }
    }

    public function findExistingCartLine(Cart $cart, int $productId): ?CartLine
    {
        foreach ($cart->getCartLines() as $line) {
            if ($line->getProduct()->getId() === $productId) {
                return $line;
            }
        }

        return null;
    }

    public function clearCart(): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            $this->request->getSession()->remove('cart');
        } else {
            $cart = $this->getCart($user);
            $this->em->remove($cart);
            $this->em->flush();
        }
    }
}
