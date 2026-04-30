<?php

namespace App\Controller\AdminController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminPanelController extends AbstractController
{
    #[Route('/admin/panel', name: 'app_admin_panel')]
    public function index(): Response
    {
        $entities = [
            'product' => [
                'name' => 'Produit',
                'color' => 'primary',
                'path' => 'app_admin_product_index'
            ],
            'category' => [
                'name' => 'Catégorie',
                'color' => 'success',
                'path' => 'app_admin_panel'
            ],
            'User' => [
                'name' => 'Utilistateur',
                'color' => 'info',
                'path' => 'app_admin_panel'
            ],            
        ];

        return $this->render('admin/panel.html.twig', [
            'entities' => $entities,
        ]);
    }
}
