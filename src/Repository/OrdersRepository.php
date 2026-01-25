<?php

namespace App\Repository;

use App\Entity\Orders;
use App\Entity\User;
use App\Entity\Produit;
use App\Entity\OrderItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }
    
    /**
     * Find an order by its order_id
     */
    public function findOneByOrderId(string $orderId): ?Orders
    {
        $entityManager = $this->getEntityManager();
        $conn = $entityManager->getConnection();
        
        try {
            // First, get the basic order data
            $sql = 'SELECT * FROM orders WHERE order_id = :orderId';
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery(['orderId' => $orderId]);
            $data = $result->fetchAssociative();
            
            if (!$data) {
                return null;
            }
            
            // Find the client
            $client = null;
            if (!empty($data['idClient'])) {
                $client = $entityManager->getReference(User::class, $data['idClient']);
            }
            
            // Create and populate the order
            $order = new Orders();
            $order->setOrderId($data['order_id']);
            $order->setTotalPrice($data['total_price']);
            $order->setStatus($data['status']);
            $order->setFullName($data['full_name']);
            $order->setAddress($data['address']);
            $order->setEmail($data['email']);
            $order->setPhone($data['phone']);
            $order->setPaymentMethod($data['payment_method']);
            $order->setPaymentStatus($data['payment_status']);
            $order->setStripePaymentId($data['stripe_payment_id']);
            
            if ($client) {
                $order->setClient($client);
            }
            
            if ($data['delivery_date']) {
                $order->setDeliveryDate(new \DateTime($data['delivery_date']));
            }
            
            if ($data['created_at']) {
                $order->setCreatedAt(new \DateTime($data['created_at']));
            }
            
            // Now fetch the order items
            $itemsSql = 'SELECT * FROM order_items WHERE order_id = :orderId';
            $itemsStmt = $conn->prepare($itemsSql);
            $itemsResult = $itemsStmt->executeQuery(['orderId' => $orderId]);
            $itemsData = $itemsResult->fetchAllAssociative();
            
            foreach ($itemsData as $itemData) {
                $product = $entityManager->getReference(Produit::class, $itemData['idProduit']);
                $artisan = $entityManager->getReference(User::class, $itemData['idArtisan']);
                
                $orderItem = new OrderItems();
                $orderItem->setIdItem($itemData['idItem']);
                $orderItem->setProduit($product);
                $orderItem->setArtisan($artisan);
                $orderItem->setNomProduit($itemData['nomProduit']);
                $orderItem->setPrix($itemData['prix']);
                $orderItem->setQuantite($itemData['quantite']);
                $orderItem->setOrder($order);
                
                $order->addItem($orderItem);
            }
            
            return $order;
            
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            error_log('Error in findOneByOrderId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Creates a new QueryBuilder instance with required joins and selects.
     *
     * @param string $alias
     * @param string|null $indexBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilder(string $alias, ?string $indexBy = null): \Doctrine\ORM\QueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy)
            ->select($alias, 'i', 'c')
            ->leftJoin($alias . '.items', 'i')
            ->leftJoin($alias . '.client', 'c');

        return $qb;
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orders for a specific artisan
     *
     * @param User $artisan The artisan to find orders for
     * @return Orders[]
     */
    public function findByArtisan(User $artisan): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('DISTINCT o')
            ->innerJoin('o.items', 'i2') // Use a unique alias 'i2' instead of 'i'
            ->andWhere('i2.artisan = :artisan')
            ->setParameter('artisan', $artisan)
            ->orderBy('o.createdAt', 'DESC'); // Changed to createdAt for consistency with index

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all orders for a specific user.
     *
     * @param User $user
     * @param string|null $status Optional status filter
     * @param array $orderBy Sorting criteria
     * @return Orders[]
     */
    public function findByUser(User $user, ?string $status = null, array $orderBy = ['createdAt' => 'DESC']): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'i', 'c')
            ->leftJoin('o.items', 'i')
            ->leftJoin('o.client', 'c')
            ->addSelect('i.id as item_id', 'i.nomProduit', 'i.prix', 'i.quantite')
            ->andWhere('o.client = :user')
            ->setParameter('user', $user);

        if ($status !== null) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }

        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy("o.$field", $direction);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find orders within a date range.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param string|null $status Optional status filter
     * @return Orders[]
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'i', 'c')
            ->leftJoin('o.items', 'i')
            ->leftJoin('o.client', 'c')
            ->addSelect('i.id as item_id', 'i.nomProduit', 'i.prix', 'i.quantite')
            ->andWhere('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);

        if ($status !== null) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find the most recent orders.
     *
     * @param int $limit Maximum number of orders to return
     * @return Orders[]
     */
    public function findRecentOrders(int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->select('o', 'i', 'c')
            ->leftJoin('o.items', 'i')
            ->leftJoin('o.client', 'c')
            ->addSelect('i.id as item_id', 'i.nomProduit', 'i.prix', 'i.quantite')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count orders by status.
     *
     * @param User|null $user Optional user filter
     * @return array<string, int> Array mapping status to count
     */
    public function countByStatus(?User $user = null): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.status, COUNT(o.order_id) as count')
            ->groupBy('o.status');

        if ($user !== null) {
            $qb->andWhere('o.client = :user')
               ->setParameter('user', $user);
        }

        $results = $qb->getQuery()->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['status']] = (int) $result['count'];
        }

        return $counts;
    }
}