<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findAvailableProducts(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.disponibilite = :dispo')
            ->setParameter('dispo', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}