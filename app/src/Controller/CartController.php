<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{ 
    #[Route('/cart/add/{id<[0-9]+>}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);

        $productId = $product->getId();
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $session->set('cart', $cart);

        $this->addFlash(
            'success',
            $product->getTitle() . ' a été ajouté à votre panier.'
        );

        return $this->redirectToRoute('app_product_index');    
    }

    #[Route('/cart/increase/{id<[0-9]+>}', name: 'app_cart_increase', methods: ['POST'])]
    public function add(Product $product, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);

        $productId = $product->getId();
        $cart[$productId] = ($cart[$productId] ?? 0) + 1;

        $session->set('cart', $cart);

        $this->addFlash(
            'success',
            $product->getTitle() . ' a été ajouté à votre panier.'
        );

        return $this->redirectToRoute('app_product_index');    
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clearAjax(SessionInterface $session, Request $request)
    {
        $session->remove('cart');

        return new JsonResponse(['success' => true]);    
    }
}
