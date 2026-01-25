<?php

namespace App\Command;

use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupPaymentMethodsCommand extends Command
{
    protected static $defaultName = 'app:setup-payment-methods';
    protected static $defaultDescription = 'Set up default payment methods';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $paymentMethods = [
            [
                'name' => 'Cash on Delivery',
                'code' => 'cash_on_delivery',
                'description' => 'Pay with cash upon delivery',
                'icon' => 'fa-money-bill-wave'
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'bank_transfer',
                'description' => 'Make a direct bank transfer',
                'icon' => 'fa-university'
            ],
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'description' => 'Pay with credit card',
                'icon' => 'fa-credit-card'
            ]
        ];

        foreach ($paymentMethods as $methodData) {
            $existingMethod = $this->entityManager->getRepository(PaymentMethod::class)->findOneBy(['code' => $methodData['code']]);
            
            if (!$existingMethod) {
                $method = new PaymentMethod();
                $method->setName($methodData['name']);
                $method->setCode($methodData['code']);
                $method->setDescription($methodData['description']);
                $method->setIcon($methodData['icon']);
                $method->setIsActive(true);
                
                $this->entityManager->persist($method);
                $io->success(sprintf('Added payment method: %s', $methodData['name']));
            } else {
                $io->note(sprintf('Payment method already exists: %s', $methodData['name']));
            }
        }

        $this->entityManager->flush();
        $io->success('Payment methods have been set up successfully!');

        return Command::SUCCESS;
    }
}
