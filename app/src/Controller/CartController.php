<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{ 
    public function __construct(private CartHandler $cartHandler)
    {
    }

    #[Route('/cart/add/{id<[0-9]+>}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product): Response
    {
        $this->cartHandler->addToCart($product);

        $this->addFlash(
            'success',
            $product->getTitle() . ' a été ajouté à votre panier.'
        );

        return $this->redirectToRoute('app_product_index');    
    }

    #[Route('/cart/increase/{id<[0-9]+>}', name: 'app_cart_increase', methods: ['POST'])]
    public function increaseAjax(Product $product): JsonResponse 
    {
        $cart = $this->cartHandler->addToCart($product);

        $newQty      = $cart[$product->getId()];
        $linePrice   = $newQty * $product->getPrice();
        $cartData    = $this->cartHandler->getCartDetails();

        return new JsonResponse([
            'success'    => true,
            'newQty'     => $newQty,
            'linePrice'  => $linePrice,
            'totalPrice' => $cartData['totalPrice'],
            'totalQty'   => array_sum($cart),
        ]);
    }

    #[Route('/cart/decrease/{id<[0-9]+>}', name: 'app_cart_decrease', methods: ['POST'])]
    public function decrease(Product $product): JsonResponse
    {
        $cart     = $this->cartHandler->removeFromCart($product);

        $newQty   = $cart[$product->getId()] ?? 0;
        $cartData = $this->cartHandler->getCartDetails();

        return new JsonResponse([
            'success'    => true,
            'newQty'     => $newQty,
            'linePrice'  => round($newQty * $product->getPrice(), 2),
            'totalPrice' => $cartData['totalPrice'],
            'totalQty'   => array_sum($cart),
            'removed'    => $newQty === 0,
        ]);
    }

    #[Route('/cart/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clearAjax(): JsonResponse
    {
        $this->cartHandler->clearCart();

        return new JsonResponse(['success' => true]);    
    }
}
