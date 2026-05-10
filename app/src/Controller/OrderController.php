<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Form\AddressType;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository
        ) {}

    #[Route('/order/address', name: 'app_order_address')]
    public function address(Request $request): Response
    {
        $order = $this->orderRepository->findOneWithAddress($this->getUser()) 
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
    public function validate(Request $request): Response
    {
        return $this->render('order/validate.html.twig', [
        ]);
    }
}
