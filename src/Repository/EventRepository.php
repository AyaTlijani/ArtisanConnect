<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUpcomingEvents(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->andWhere('e.date >= CURRENT_DATE()')
            ->setParameter('status', 'Upcoming')
            ->orderBy('e.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySearchCriteria(
        ?string $name,
        ?string $date,
        ?float $price,
        ?string $status,
        ?int $organizer,
        ?int $atelier,
        ?int $location,
        ?string $startTime,
        ?int $capacity
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.organizer', 'o')
            ->leftJoin('e.atelier', 'a')
            ->leftJoin('e.location', 'l');

        if ($name) {
            $qb->andWhere('e.name LIKE :name OR e.description LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        if ($date) {
            $qb->andWhere('e.date = :date')
               ->setParameter('date', new \DateTime($date));
        }

        if ($price !== null && $price !== '') {
            $qb->andWhere('e.price <= :price')
               ->setParameter('price', $price);
        }

        if ($status) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $status);
        }

        if ($organizer) {
            $qb->andWhere('e.organizer = :organizer')
               ->setParameter('organizer', $organizer);
        }

        if ($atelier) {
            $qb->andWhere('a.idAtelier = :atelier') // Explicitly use idAtelier
               ->setParameter('atelier', $atelier);
        }

        if ($location) {
            $qb->andWhere('e.location = :location')
               ->setParameter('location', $location);
        }

        if ($startTime) {
            $qb->andWhere('e.startTime = :startTime')
               ->setParameter('startTime', new \DateTime($startTime));
        }

        if ($capacity !== null && $capacity !== '') {
            $qb->andWhere('e.capacity >= :capacity')
               ->setParameter('capacity', $capacity);
        }

        return $qb->orderBy('e.date', 'ASC')
                  ->setMaxResults(20)
                  ->getQuery()
                  ->getResult();
    }
}