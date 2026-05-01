<?php

namespace App\Controller\AdminController;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Service\ImageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminProductController extends AbstractController
{
    public function __construct(private ImageHandler $imageHandler)
    {
    }
    
    #[Route('/admin/product', name: 'app_admin_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/admin/product/save/{id<[0-9]+>?}', name: 'app_admin_product_save')]
    public function save(?Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $imageFiles = $form->get('images')->getData();
            if ($imageFiles) {
                foreach($imageFiles as $imageFile) {
                    $image = $this->imageHandler->uploadImage($imageFile, $product);
                    if (!$image) return $this->redirectToRoute('app_admin_product_save', ['id' => $product->getId()]);

                    //$em->persist($image);
                    $product->addImage($image);
                }
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Le produit a été enregistré avec succès.');

            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/save.html.twig', [
            'form' => $form->createView(),
            'isEdit' => (bool)$product,
            'product' => $product
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

        $this->imageHandler->deleteFiles($product);

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Le produit a été supprimé avec succès.');

        return $this->redirectToRoute('app_admin_product_index');
    }

    #[Route('/admin/product/image/delete/{id<[0-9]+>}', name: 'app_admin_image_delete')]
    public function deleteImageAjax(Image $image, Request $request, EntityManagerInterface $em): Response
    {
        if($image->isPrincipal()) return $this->json(['error' => true]);
        
        $productId = $image->getProduct()->getId();

        $submittedToken = $request->getPayload()->get('token');
        if (!$this->isCsrfTokenValid('delete-image-' . $image->getId(), $submittedToken)) {
            $this->addFlash('danger', 'Token invalide.');

            return $this->redirectToRoute('app_admin_product_save', ['id' => $productId]);
        }

        $filePath = $image->getPath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $em->remove($image);
        $em->flush();

        return $this->json(['success' => true]);
    }
    
    #[Route('/admin/product/image/principal/{id<[0-9]+>}', name: 'app_admin_image_principal')]
    public function setImagePrincipalAjax(Image $image, Request $request, EntityManagerInterface $em): Response
    {
        $product = $image->getProduct();
        $submittedToken = $request->getPayload()->get('token');
        if (!$this->isCsrfTokenValid('principal-image-' . $image->getId(), $submittedToken)) {
            $this->addFlash('danger', 'Token invalide.');
            return $this->redirectToRoute('app_admin_product_save', ['id' => $product->getId()]);
        }

        // Retirer isPrincipal sur toutes les images du produit
        foreach ($product->getImages() as $img) {
            $img->setIsPrincipal(false);
        }

        // Définir la nouvelle principale
        $image->setIsPrincipal(true);

        $em->flush();

        return $this->json(['success' => true]);
    }
}
