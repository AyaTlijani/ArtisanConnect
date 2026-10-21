<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Commande;
use App\Entity\Paiement;
use App\Repository\CommandeRepository;
use App\Repository\LivreurRepository;
use App\Repository\OrdersRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/my-orders', name: 'app_client_orders', methods: ['GET'])]
    public function clientOrders(CommandeRepository $commandeRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Get the current user's email
        $userEmail = $this->getUser()->getUserIdentifier();
        
        // Get orders for the current user, ordered by creation date (newest first)
        $commandes = $commandeRepository->findBy(
            ['client' => $userEmail],
            ['createdAt' => 'DESC']
        );
        
        // Format orders for the template
        $formattedOrders = [];
        foreach ($commandes as $commande) {
            $formattedOrders[] = [
                'id' => $commande->getId(),
                'status' => $commande->getStatut(),
                'createdAt' => $commande->getCreatedAt(),
                'totalPrice' => $commande->getOrder() ? $commande->getOrder()->getTotalPrice() : 0,
                'livreur' => $commande->getLivreur() ? $commande->getLivreur()->getNom() : 'Not assigned',
                'trackingNumber' => 'TRK' . str_pad($commande->getId(), 6, '0', STR_PAD_LEFT)
            ];
        }
        
        return $this->render('order/client_orders.html.twig', [
            'orders' => $formattedOrders,
            'currentDate' => new \DateTime()
        ]);
    }
    #[Route('/', name: 'app_orders', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository, LivreurRepository $livreurRepository): Response
    {
        // Get all commandes ordered by creation date (newest first)
        $commandes = $commandeRepository->findBy([], ['createdAt' => 'DESC']);

        // Get only available livreurs
        $livreurs = $livreurRepository->findBy(['disponible' => true]);

        // Format the data for the template
        $formattedOrders = [];
        foreach ($commandes as $commande) {
            $formattedOrders[] = [
                'orderId' => $commande->getId(),
                'fullName' => $commande->getClient(),
                'status' => strtolower($commande->getStatut()),
                'totalPrice' => $commande->getOrder() ? $commande->getOrder()->getTotalPrice() : 0,
                'createdAt' => $commande->getCreatedAt(),
                'livreur' => $commande->getLivreur()
            ];
        }

        return $this->render('order/commandes.html.twig', [
            'recentOrders' => $formattedOrders,
            'livreurs' => $livreurs
        ]);
    }
    #[Route('/{order_id}', name: 'app_order_show', methods: ['GET'])]
    public function show(string $order_id, OrdersRepository $ordersRepository, CommandeRepository $commandeRepository, EntityManagerInterface $em): Response
    {
        // Try to find in Commande table first (since we're using commande IDs in the URL)
        $commande = $commandeRepository->find($order_id);

        if ($commande) {
            // If we found the commande, try to find related order
            $order = $commande->getOrder();

            // If no related order, create a simplified order object
            if (!$order) {
                $order = (object) [
                    'id' => $commande->getId(),
                    'orderId' => $commande->getId(),
                    'status' => $commande->getStatut(),
                    'createdAt' => $commande->getCreatedAt(),
                    'client' => $commande->getClient(),
                    'email' => $commande->getEmail(),
                    'telephone' => $commande->getTelephone(),
                    'adresse' => $commande->getAdresse(),
                    'totalPrice' => 0 // Default value
                ];
            }

            return $this->render('order/show.html.twig', [
                'order' => $order,
                'commande' => $commande
            ]);
        }

        // If not found in Commande, try to find in Orders table as fallback
        $order = $ordersRepository->findOneBy(['order_id' => $order_id]);

        if ($order) {
            return $this->render('order/show.html.twig', [
                'order' => $order,
                'commande' => null
            ]);
        }

        // If we get here, the order doesn't exist in either table
        $this->addFlash('error', sprintf('La commande avec l\'ID %s n\'a pas été trouvée.', $order_id));
        return $this->redirectToRoute('app_orders');
    }

    #[Route('/assign-livreur', name: 'app_order_assign_livreur', methods: ['POST'])]
    public function assignLivreur(
        Request $request,
        CommandeRepository $commandeRepository,
        LivreurRepository $livreurRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        // Get JSON data from request
        $data = json_decode($request->getContent(), true);
        $orderId = $data['orderId'] ?? null;
        $livreurId = $data['livreurId'] ?? null;

        if (!$orderId || !$livreurId) {
            return new JsonResponse(['success' => false, 'message' => 'Paramètres manquants'], 400);
        }

        // Find the commande and livreur
        $commande = $commandeRepository->find($orderId);
        $livreur = $livreurRepository->find($livreurId);

        if (!$commande) {
            return new JsonResponse(['success' => false, 'message' => 'Commande non trouvée'], 404);
        }

        if (!$livreur) {
            return new JsonResponse(['success' => false, 'message' => 'Livreur non trouvé'], 404);
        }

        // Check if livreur is available
        if (!$livreur->isDisponible()) {
            return new JsonResponse(['success' => false, 'message' => 'Ce livreur n\'est pas disponible'], 400);
        }

        // Check if a livreur is already assigned
        if ($commande->getLivreur()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Un livreur est déjà assigné à cette commande'
            ], 400);
        }

        // Check if commande is in a valid state for assignment
        if ($commande->getStatut() === 'Livrée' || $commande->getStatut() === 'Annulée') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Impossible d\'assigner un livreur à une commande livrée ou annulée'
            ], 400);
        }

        try {
            // Assign the livreur
            $commande->setLivreur($livreur);
            // Update commande status to "En livraison"
            $commande->setStatut('En livraison');
            // Optionally, mark livreur as unavailable
            // $livreur->setDisponible(false);

            $em->persist($commande);
            // $em->persist($livreur); // Uncomment if updating livreur availability
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'livreurName' => $livreur->getNom(),
                'newStatus' => 'En livraison'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation du livreur : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{order_id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(Request $request, string $order_id, OrdersRepository $ordersRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $ordersRepository->findOneByOrderId($order_id);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        if ($order->getStatus() === Orders::STATUS_CANCELLED) {
            $this->addFlash('warning', 'This order is already cancelled.');
            return $this->redirectToRoute('app_order_show', ['order_id' => $order->getOrderId()]);
        }

        if (!$this->isCsrfTokenValid('cancel' . $order->getOrderId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_order_show', ['order_id' => $order->getOrderId()]);
        }

        $order->setStatus(Orders::STATUS_CANCELLED);
        if ($order->getCommande()) {
            $order->getCommande()->setStatut('Annulée');
        }
        // Get the related Paiement through the commande
        $paiement = $entityManager->getRepository(Paiement::class)->findOneBy(['id_commande' => $order->getCommande()]);
        if ($paiement) {
            $paiement->setStatut('Annulé');
        }
        $entityManager->flush();

        $this->addFlash('success', 'Your order has been cancelled successfully.');
        return $this->redirectToRoute('app_order_show', ['order_id' => $order->getOrderId()]);
    }

    #[Route('/commandes', name: 'app_commandes_list', methods: ['GET'], priority: 1)]
    public function listCommandes(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('order/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}