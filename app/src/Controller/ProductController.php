<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[Route('/', name: 'app_product_index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $this->productRepository->findAllWithCategoryAndPrincipalImage()
        ]);
    }

   #[Route('/show/{id<[0-9]+>}', name: 'app_product_show')]
    public function show($id): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $this->productRepository->findWithCategoryAndImages((int)$id)
        ]);
    }
}
