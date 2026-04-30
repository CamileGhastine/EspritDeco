<?php

namespace App\Controller\AdminController;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ImageHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminProductController extends AbstractController
{
    #[Route('/admin/product', name: 'app_admin_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/admin/product/save', name: 'app_admin_product_save')]
    public function save(Request $request, EntityManagerInterface $em, ImageHandler $imageHandler): Response
    {
        $form = $this->createForm(ProductFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $image = $imageHandler->uploadImage($imageFile, $product);

                if (!$image) return $this->redirectToRoute('app_admin_product_save');

                $em->persist($image);
                $product->addImage($image);
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Le produit a été ajouté avec succès.');

            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/save.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/product/delete/{id<[0-9]+>}', name: 'app_admin_product_delete')]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $submittedToken = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid('delete-item-' . $product->getId(), $submittedToken)) {
            $this->addFlash('danger', 'Token invalide.');

            return $this->redirectToRoute('app_admin_product_index');
        }

        if (!$product) {
            $this->addFlash('danger', 'Ce produit n\'existe pas.');

            return $this->redirectToRoute('app_admin_product_index');
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Le produit a été supprimé avec succès.');

        return $this->redirectToRoute('app_admin_product_index');
    }
}
