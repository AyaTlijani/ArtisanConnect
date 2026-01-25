<?php

namespace App\Repository;

use App\Entity\Panier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.idClient = :user')
            ->setParameter('user', $user)
            ->andWhere('p.idProduit IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
    
    public function getCartTotal($user): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.prixTotal) as total')
            ->andWhere('p.idClient = :user')
            ->setParameter('user', $user)
            ->andWhere('p.idProduit IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
            
        return (float) ($result ?? 0);
    }
}   