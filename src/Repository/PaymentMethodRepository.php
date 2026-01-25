<?php

namespace App\Repository;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentMethod[]    findAll()
 * @method PaymentMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    /**
     * Find all active payment methods
     *
     * @return PaymentMethod[]
     */
    public function findActivePaymentMethods(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a payment method by its code
     */
    public function findOneByCode(string $code): ?PaymentMethod
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.code = :code')
            ->andWhere('p.isActive = :active')
            ->setParameter('code', $code)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find payment methods by codes
     *
     * @param string[] $codes
     * @return PaymentMethod[]
     */
    public function findByCodes(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.code IN (:codes)')
            ->andWhere('p.isActive = :active')
            ->setParameter('codes', $codes)
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
