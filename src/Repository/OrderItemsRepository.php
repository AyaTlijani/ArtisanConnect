<?php

namespace App\Repository;

use App\Entity\OrderItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItems::class);
    }

    public function findItemsByOrderAndArtisan($order, $artisan)
    {
        return $this->createQueryBuilder('i')
            ->join('i.order', 'o')
            ->join('i.artisan', 'a')
            ->where('o = :order')
            ->andWhere('a = :artisan')
            ->setParameter('order', $order)
            ->setParameter('artisan', $artisan)
            ->getQuery()
            ->getResult();
    }
}
