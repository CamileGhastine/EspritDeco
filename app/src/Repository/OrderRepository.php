<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }


    public function findOnePendingWithAddress(User $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.address', 'a')
            ->addSelect('a')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::PENDING_PAYMENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOnePendingWithLine(User $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.orderLines', 'ol')
            ->addSelect('ol')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::PENDING_PAYMENT)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
