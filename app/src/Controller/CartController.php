<?php

namespace App\Controller;

use App\Entity\CartLine;
use App\Entity\Product;
use App\Service\CartHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{ 
    public function __construct(
        private CartHandler $cartHandler,
        private EntityManagerInterface $em,
        )
    {
    }

    #[Route('/cart/add/{id<[0-9]+>}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, SessionInterface $session): Response
    {
        $this->addToCart($product, $session);
        $this->addFlash(
            'success',
            $product->getTitle() . ' a été ajouté à votre panier.'
        );

        return $this->redirectToRoute('app_product_index');    
    }

    #[Route('/cart/increase/{id<[0-9]+>}', name: 'app_cart_increase', methods: ['POST'])]
    public function increaseAjax(
        Product $product,
        SessionInterface $session,
        CartHandler $cartHandler
    ): JsonResponse 
    {
        $cart = $this->addToCart($product, $session);
        $newQty      = $cart[$product->getId()];
        $linePrice   = $newQty * $product->getPrice();
        $cartData    = $cartHandler->getCartDetails();

        return new JsonResponse([
            'success'    => true,
            'newQty'     => $newQty,
            'linePrice'  => $linePrice,
            'totalPrice' => $cartData['totalPrice'],
            'totalQty'   => array_sum($cart),
        ]);
    }

    private function addToCart(Product $product, SessionInterface $session): array
    {
        if (!$this->getUser()) {
            $cart = $session->get('cart', []);
            $productId = $product->getId();
            $cart[$productId] = ($cart[$productId] ?? 0) + 1;
            $session->set('cart', $cart);            
        } else {
            $cartDb = $this->cartHandler->getCart($this->getUser());
            $existingLine = $this->cartHandler->findExistingCartLine($cartDb, $product->getId());
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

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clearAjax(SessionInterface $session, Request $request)
    {
        $session->remove('cart');

        return new JsonResponse(['success' => true]);    
    }

    private function removeFromCart(Product $product, SessionInterface $session): array
    {
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

    #[Route('/cart/decrease/{id<[0-9]+>}', name: 'app_cart_decrease', methods: ['POST'])]
    public function decrease(Product $product, SessionInterface $session, CartHandler $cartHandler): JsonResponse
    {
        $cart     = $this->removeFromCart($product, $session);
        $newQty   = $cart[$product->getId()] ?? 0;
        $cartData = $cartHandler->getCartDetails();

        return new JsonResponse([
            'success'    => true,
            'newQty'     => $newQty,
            'linePrice'  => round($newQty * $product->getPrice(), 2),
            'totalPrice' => $cartData['totalPrice'],
            'totalQty'   => array_sum($cart),
            'removed'    => $newQty === 0,
        ]);
    }
}
