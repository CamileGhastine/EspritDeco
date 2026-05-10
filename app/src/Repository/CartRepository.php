<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

        public function findOpenWithLineAndProduct($user): ?Cart
        {
            return $this->createQueryBuilder('c')
                ->leftJoin('c.cartLines', 'cl')
                ->addSelect('cl')
                ->leftJoin('cl.product', 'p')
                ->addSelect('p')
                ->andWhere('c.user = :user')
                ->setParameter('user', $user)
                ->andWhere('c.status = :status')
                ->setParameter('status', Cart::OPEN)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
    }
