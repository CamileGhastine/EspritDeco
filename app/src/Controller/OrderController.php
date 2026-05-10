<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressType;
use App\Repository\OrderRepository;
use App\Service\Cart\CartHandler;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository,
        private CartHandler $cartHandler
        ) {}

    #[Route('/order/address', name: 'app_order_address')]
    #[IsGranted('ROLE_USER')]
    public function address(Request $request): Response
    {
        $order = $this->orderRepository->findOnePendingWithAddress($this->getUser())
            ?? new Order;
        $address = $order->getAddress();

        if (!$address) {
            $address = new Address();
            $order->setAddress($address);
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUser($this->getUser());
            if (!$order->getCreatedAt()) {
                $order->setCreatedAt(new DateTimeImmutable());
            }

            $this->em->persist($order);
            $this->em->flush();

            return $this->redirectToRoute('app_order_validate');
        }

        return $this->render('order/address.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/order/validate', name: 'app_order_validate')]
    #[IsGranted('ROLE_USER')]
    public function validate(Request $request): Response
    {
        $order = $this->orderRepository->findOnePendingWithAddress($this->getUser());

        if (!$order) {
            $this->addFlash('danger', 'Constituez votre panier pour passer commande.');

            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('order/validate.html.twig', [
            'cart' => $this->cartHandler->getCartDetails(),
            'address' => $order->getAddress()
        ]);
    }
}
