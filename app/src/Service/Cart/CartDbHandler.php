<?php

namespace App\Service\Cart;

use App\Entity\Cart;
use App\Entity\CartLine;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CartDbHandler implements CartStorageInterface
{
    public function __construct(
        private Security $security,
        private CartRepository $cartRepository,
        private EntityManagerInterface $em
        ) {}

    public function add(Product $product): array
    {
            $cart = $this->getCart();
            $existingLine = $this->findExistingCartLine($cart, $product->getId());
            if ($existingLine) {
                $existingLine->setQuantity($existingLine->getQuantity() + 1);
            } else {
                $cartLine = new CartLine();
                $cartLine->setProduct($product)->setQuantity(1);
                $cart->addCartLine($cartLine);
            }
            
            $this->save($cart);
            
            return $this->formatCart($cart);
    }
    
    public function remove(Product $product): array
    {
        $cart = $this->getCart();
        $existingLine = $this->findExistingCartLine($cart, $product->getId());
        if ($existingLine) {
            if ($existingLine->getQuantity() > 1) {
                $existingLine->setQuantity($existingLine->getQuantity() - 1);
            } else {
                $cart->removeCartLine($existingLine);
                $this->em->remove($existingLine);
            }

            if ($cart->getCartLines()->count() === 0) {
                $this->em->remove($cart);
            }

            $this->save($cart);
        }
        
        return $this->formatCart($cart);
    }

    public function clear(): void
    {
        $cart = $this->getCart();
        if (!$cart->getId()) {
            return;
        }

        $this->em->remove($cart);
        $this->em->flush();
    }

    public function getCart(): Cart
    {
        $user = $this->security->getUser();

        $cart = $this->cartRepository->findOpenWithLineAndProduct($user);

        return $cart ?? new Cart($user);
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

    public function save(Cart $cart)
    {
        $this->em->persist($cart);
        $this->em->flush();
    }


    private function formatCart(Cart $cart): array
    {
        $result = [];

        foreach ($cart->getCartLines() as $line) {
            $result[$line->getProduct()->getId()] = $line->getQuantity();
        }

        return $result;
    }

    public function transferSessionCartToDatabase(Cart $cart, array $cartSession, array $productsById): void
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
        
        $this->save($cart);
    }

}