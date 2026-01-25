<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\OrderItems;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Find commandes that contain products from a specific artisan
     */
    public function findCommandesByArtisan(User $artisan): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.order', 'o')
            ->join('o.items', 'i')
            ->join('i.artisan', 'a')
            ->where('a.id = :artisanId')
            ->setParameter('artisanId', $artisan->getId())
            ->orderBy('o.deliveryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find commandes with their items for a specific artisan
     */
    public function findCommandesWithItemsByArtisan(User $artisan): array
    {
        $commandes = $this->createQueryBuilder('c')
            ->join('c.order', 'o')
            ->join('o.items', 'i')
            ->join('i.artisan', 'a')
            ->where('a.id = :artisanId')
            ->setParameter('artisanId', $artisan->getId())
            ->orderBy('o.deliveryDate', 'DESC')
            ->getQuery()
            ->getResult();

        // Eager load items for each commande
        $this->getEntityManager()->createQuery(
            'SELECT i, p, a FROM ' . OrderItems::class . ' i
             JOIN i.produit p
             JOIN i.artisan a
             WHERE i.order IN (:commandes) AND a.id = :artisanId'
        )->setParameter('commandes', $commandes)
         ->setParameter('artisanId', $artisan->getId())
         ->getResult();

        return $commandes;
    }
}
