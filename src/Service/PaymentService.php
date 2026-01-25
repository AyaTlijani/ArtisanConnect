<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentService
{
    private $entityManager;
    private $requestStack;

    // Define available payment methods
    private const PAYMENT_METHODS = [
        'cash' => [
            'name' => 'Paiement à la livraison',
            'description' => 'Payer à la réception de votre commande',
            'icon' => 'fa-money-bill-wave',
            'processor' => 'static'
        ]
    ];

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Process a payment for an order
     */
    public function processPayment(Orders $order, string $paymentMethod, array $paymentData = []): array
    {
        if (!array_key_exists($paymentMethod, self::PAYMENT_METHODS)) {
            return [
                'success' => false,
                'message' => 'Invalid payment method'
            ];
        }

        // Create payment record
        $paiement = new Paiement();
        $paiement->setOrder($order);
        $paiement->setClient($order->getClient());
        $paiement->setMontant($order->getTotalPrice());
        $paiement->setPaymentMethod($paymentMethod);
        $paiement->setDatePaiement(new \DateTime());

        // Process based on payment method
        $methodName = 'process' . str_replace('_', '', ucwords($paymentMethod, '_')) . 'Payment';
        if (method_exists($this, $methodName)) {
            return $this->$methodName($paiement, $paymentData);
        }

        // Default processing for unknown methods
        return $this->processDefaultPayment($paiement);
    }

    /**
     * Get all available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        return self::PAYMENT_METHODS;
    }

    /**
     * Get payment method details by code
     */
    public function getPaymentMethodDetails(string $code): ?array
    {
        return self::PAYMENT_METHODS[$code] ?? null;
    }

    private function processCreditCardPayment(Paiement $paiement, array $data): array
    {
        // Process credit card payment (static processing)
        $paiement->setPaymentStatus('completed');
        $paiement->setStatut('completed');
        $paiement->setTransactionId('PAY-' . strtoupper(uniqid()));
        
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'status' => 'completed',
            'message' => 'Payment processed successfully',
            'payment_id' => $paiement->getIdPaiement(),
            'redirect' => 'app_order_show'
        ];
    }

    private function processPaypalPayment(Paiement $paiement, array $data): array
    {
        // Process PayPal payment
        $paiement->setPaymentStatus('pending');
        $paiement->setStatut('pending');
        $paiement->setTransactionId(uniqid('paypal_'));
        
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        
        // In a real app, you'd redirect to PayPal here
        return [
            'success' => true,
            'status' => 'pending',
            'message' => 'Redirecting to PayPal',
            'redirect' => 'app_paypal_redirect',
            'payment_id' => $paiement->getIdPaiement()
        ];
    }

    private function processBankTransferPayment(Paiement $paiement): array
    {
        // Process bank transfer
        $paiement->setPaymentStatus('pending');
        $paiement->setStatut('pending');
        $paiement->setTransactionId(uniqid('bank_'));
        
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'status' => 'pending',
            'message' => 'Please transfer the amount to our bank account',
            'bank_details' => [
                'account_name' => 'Your Company Name',
                'account_number' => '1234567890',
                'bank_name' => 'Your Bank',
                'swift_code' => 'ABCDEF123'
            ],
            'payment_id' => $paiement->getIdPaiement(),
            'redirect' => 'app_order_show'
        ];
    }

    private function processCashPayment(Paiement $paiement): array
    {
        // Process cash on delivery
        $paiement->setPaymentStatus('completed');
        $paiement->setStatut('completed');
        $paiement->setTransactionId(uniqid('cash_'));
        
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'status' => 'completed',
            'message' => 'Order confirmed. Please prepare cash on delivery.',
            'payment_id' => $paiement->getIdPaiement(),
            'redirect' => 'app_order_show'
        ];
    }
    
    private function processDefaultPayment(Paiement $paiement, array $data = []): array
    {
        // Default processing for unknown payment methods
        $paiement->setPaymentStatus('pending');
        $paiement->setStatut('pending');
        $paiement->setTransactionId(uniqid('pay_'));
        
        $this->entityManager->persist($paiement);
        $this->entityManager->flush();
        
        return [
            'success' => true,
            'status' => 'pending',
            'message' => 'Payment is being processed',
            'payment_id' => $paiement->getIdPaiement(),
            'redirect' => 'app_order_show'
        ];
    }
}
