<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em
    ) {}

    public function clearOrder(User $user)
    {
        if (!$user) return;

        $order = $this->orderRepository->findOnePendingWithLine($user);

        if (!$order) return;

        foreach ($order->getOrderLines() as $line) {
            $this->em->remove($line);
        }

        $this->em->remove($order);
        $this->em->flush();
    }
}