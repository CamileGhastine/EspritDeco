<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    
    #[Route('/cart/add/{id<[0-9]+>}', name: 'app_cart_add')]
    public function add(Product $product, SessionInterface $session): Response
    {

        $this->addFlash('success', $product->getTitle() . ' a été ajouté à votre panier.');

        return $this->redirectToRoute('app_product_index');
    }
}
