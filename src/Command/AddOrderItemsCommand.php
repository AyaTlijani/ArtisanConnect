<?php

namespace App\Command;

use App\Entity\OrderItems;
use App\Entity\Orders;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddOrderItemsCommand extends Command
{
    protected static $defaultName = 'app:add-order-items';
    protected static $defaultDescription = 'Add sample order items to the database';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Get existing orders and products
        $orders = $this->entityManager->getRepository(Orders::class)->findAll();
        $products = $this->entityManager->getRepository(Produit::class)->findAll();
        $artisans = $this->entityManager->getRepository(User::class)->findBy(['role' => 'ARTISAN']);

        if (empty($orders) || empty($products) || empty($artisans)) {
            $io->error('You need to have at least one order, product, and artisan in the database');
            return Command::FAILURE;
        }

        // Sample order items data
        $orderItemsData = [
            ['order_index' => 0, 'product_index' => 0, 'quantity' => 2],
            ['order_index' => 0, 'product_index' => 1, 'quantity' => 1],
            ['order_index' => 1, 'product_index' => 2, 'quantity' => 1],
            ['order_index' => 1, 'product_index' => 3, 'quantity' => 2],
            ['order_index' => 2, 'product_index' => 0, 'quantity' => 1],
        ];

        $added = 0;
        foreach ($orderItemsData as $itemData) {
            if (!isset($orders[$itemData['order_index']]) || !isset($products[$itemData['product_index']])) {
                continue;
            }

            $order = $orders[$itemData['order_index']];
            $product = $products[$itemData['product_index']];
            $artisan = $artisans[0]; // Use the first artisan for simplicity

            $orderItem = new OrderItems();
            $orderItem->setOrder($order);
            $orderItem->setProduit($product);
            $orderItem->setArtisan($artisan);
            $orderItem->setNomProduit($product->getNom());
            $orderItem->setPrix($product->getPrix());
            $orderItem->setQuantite($itemData['quantity']);

            $this->entityManager->persist($orderItem);
            $added++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully added %d order items to the database', $added));
        return Command::SUCCESS;
    }
}
