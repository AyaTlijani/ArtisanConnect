<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\OrderItems;
use App\Entity\Orders;
use App\Entity\Paiement;
use App\Form\PaymentMethodType;
use App\Form\CreditCardType;
use App\Repository\PanierRepository;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('', name: 'app_checkout', methods: ['GET'])]
    public function index(PanierRepository $panierRepo, PaymentService $paymentService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_sign_in');
        }

        $panierItems = $panierRepo->findBy(['idClient' => $user->getIdUser()]);
        if (empty($panierItems)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $total = array_sum(array_map(fn($item) => $item->getPrixTotal(), $panierItems));
        $paymentMethods = $paymentService->getAvailablePaymentMethods();

        // Create payment method form
        $paymentForm = $this->createForm(PaymentMethodType::class);
        $creditCardForm = $this->createForm(CreditCardType::class);

        return $this->render('checkout/index.html.twig', [
            'panierItems' => $panierItems,
            'total' => $total,
            'payment_methods' => $paymentMethods,
            'payment_form' => $paymentForm->createView(),
            'credit_card_form' => $creditCardForm->createView(),
        ]);
    }

    #[Route('/process', name: 'app_checkout_process', methods: ['POST'])]
    public function processCheckout(
        Request $request,
        PanierRepository $panierRepo,
        EntityManagerInterface $entityManager,
        PaymentService $paymentService
    ): Response {
        $user = $this->getUser();
        $isAjax = $request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest';

        $this->logger->info('Form data received', $request->request->all());

        if (!$user) {
            $errorMessage = 'Vous devez être connecté pour passer une commande.';
            $this->logger->warning($errorMessage);
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $errorMessage], 401);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_sign_in');
        }

        $panierItems = $panierRepo->findBy(['idClient' => $user->getIdUser()]);
        $this->logger->info('Cart items found', ['count' => count($panierItems)]);

        if (empty($panierItems)) {
            $errorMessage = 'Votre panier est vide.';
            $this->logger->warning($errorMessage, ['user_id' => $user->getIdUser()]);
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $errorMessage], 400);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_cart_index');
        }

        // Validate form data
        $requiredFields = ['full_name', 'email', 'phone', 'address', 'delivery_date'];
        $formData = [];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            $value = $request->request->get($field);
            if (empty($value)) {
                $missingFields[] = $field;
            }
            $formData[$field] = $value;
        }

        if (!empty($missingFields)) {
            $errorMessage = 'Les champs suivants sont obligatoires : ' . implode(', ', $missingFields);
            $this->logger->warning($errorMessage, ['missing_fields' => $missingFields]);
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $errorMessage], 400);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_checkout');
        }

        // Validate delivery date
        try {
            $deliveryDate = new \DateTime($formData['delivery_date']);
            $today = new \DateTime('today');
            if ($deliveryDate < $today) {
                throw new \Exception('Delivery date cannot be in the past.');
            }
        } catch (\Exception $e) {
            $errorMessage = 'Date de livraison invalide.';
            $this->logger->warning($errorMessage, ['delivery_date' => $formData['delivery_date']]);
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $errorMessage], 400);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_checkout');
        }

        $entityManager->beginTransaction();

        try {
            $this->logger->info('Starting order processing for user: ' . $user->getEmail());

            // Calculate total
            $total = array_sum(array_map(fn($item) => $item->getPrixTotal(), $panierItems));
            $orderId = 'ORD-' . time() . '-' . bin2hex(random_bytes(4));
            $this->logger->debug('Order details', [
                'order_id' => $orderId,
                'total' => $total,
                'item_count' => count($panierItems)
            ]);

            // Create Orders entity
            $order = new Orders();
            $order->setOrderId($orderId);
            $order->setClient($user);
            $order->setStatus(Orders::STATUS_PENDING);
            $order->setPaymentStatus('pending');
            $order->setTotalPrice($total);
            $order->setAddress($formData['address']);
            $order->setCreatedAt(new \DateTime());
            $order->setFullName($formData['full_name']);
            $order->setEmail($formData['email']);
            $order->setPhone($formData['phone']);
            $order->setPaymentMethod($formData['payment_method'] ?? 'cash');
            $order->setDeliveryDate($deliveryDate);

            // Create Commande entity
            $commande = new Commande();
            $commande->setId($orderId);
            $commande->setClient($formData['full_name']);
            $commande->setEmail($formData['email']);
            $commande->setAdresse($formData['address']);
            $commande->setTelephone($formData['phone']);
            $commande->setStatut('En préparation');
            $commande->setCreatedAt(new \DateTime());
            $commande->setOrder($order); // Set the relationship

            // Persist Orders and Commande
            $entityManager->persist($order);
            $entityManager->persist($commande);
            $entityManager->flush(); // Flush to ensure Orders and Commande are saved

            // Create OrderItems
            foreach ($panierItems as $item) {
                $produit = $item->getIdProduit();
                if (!$produit) {
                    throw new \Exception('Invalid product for cart item ID: ' . $item->getIdPanier());
                }
                $artisan = $produit->getIdArtisan();
                if (!$artisan) {
                    throw new \Exception('Invalid artisan for product ID: ' . $produit->getIdProduit());
                }

                $orderItem = new OrderItems();
                $orderItem->setOrder($order);
                $orderItem->setProduit($produit);
                $orderItem->setArtisan($artisan);
                $orderItem->setNomProduit($produit->getNom());
                $orderItem->setPrix($produit->getPrix());
                $orderItem->setQuantite($item->getQuantite());
                $entityManager->persist($orderItem);
            }

            // Create Paiement
            $paiement = new Paiement();
            $paiement->setClient($user);
            $paiement->setOrder($order);
            $paiement->setMontant($total);
            $paiement->setStatut('En attente');
            $paiement->setDatePaiement(new \DateTime());
            $paiement->setPaymentMethod($formData['payment_method'] ?? 'cash');
            $paiement->setTransactionId('TXN-' . strtoupper(uniqid()));
            $paiement->setRemboursement(false);
            $entityManager->persist($paiement);

            // Clear cart
            foreach ($panierItems as $item) {
                $entityManager->remove($item);
            }

            // Flush and commit
            $entityManager->flush();
            $entityManager->commit();
            $this->logger->info('Order saved successfully', ['order_id' => $orderId]);

            $responseData = [
                'success' => true,
                'order_id' => $orderId,
                'message' => 'Votre commande a été passée avec succès !',
                'redirect' => $this->generateUrl('app_order_show', ['order_id' => $orderId], UrlGeneratorInterface::ABSOLUTE_URL)
            ];

            if ($isAjax) {
                return $this->json($responseData);
            }

            $this->addFlash('success', $responseData['message']);
            return $this->redirectToRoute('app_order_show', ['order_id' => $orderId]);

        } catch (\Exception $e) {
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->rollback();
                $this->logger->warning('Transaction rolled back');
            }

            $errorDetails = [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => $user ? $user->getIdUser() : 'anonymous'
            ];
            $this->logger->error('Checkout process failed', $errorDetails);

            $errorMessage = 'Une erreur est survenue lors du traitement de votre commande. Veuillez réessayer.';
            if (str_contains($e->getMessage(), 'SQLSTATE') || $e instanceof \Doctrine\DBAL\Exception) {
                $errorMessage = 'Erreur de base de données. Veuillez contacter le support.';
            } elseif ($e instanceof \Doctrine\ORM\OptimisticLockException) {
                $errorMessage = 'Conflit de données. Veuillez réessayer.';
            }

            if ($isAjax) {
                $response = ['success' => false, 'error' => $errorMessage];
                if ($this->getParameter('kernel.debug')) {
                    $response['debug'] = $errorDetails;
                }
                return $this->json($response, 500);
            }

            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_checkout');
        }
    }
}