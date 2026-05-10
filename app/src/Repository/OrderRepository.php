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


    public function findOneWithAddress(User $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.address', 'a')
            ->addSelect('a')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
