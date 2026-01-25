<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livreur;
use App\Entity\Orders;
use App\Repository\LivreurRepository;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artisan/orders')]
class ArtisanOrderController extends AbstractController
{
    #[Route('/', name: 'app_artisan_orders_index', methods: ['GET'])]
    public function index(OrdersRepository $ordersRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');

        $artisan = $this->getUser();
        $orders = $ordersRepository->findByArtisan($artisan);

        $this->addFlash('info', sprintf('Found %d orders', count($orders)));

        return $this->render('artisan/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_artisan_order_show', methods: ['GET'])]
    public function show(string $id, OrdersRepository $ordersRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');

        // Fetch the order with Commande relationship eagerly loaded
        $order = $ordersRepository->createQueryBuilder('o')
            ->leftJoin('o.commande', 'c')
            ->where('o.order_id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée pour l\'ID ' . $id);
        }

        // Verify that the current user is the artisan of this order
        $isArtisan = false;
        foreach ($order->getItems() as $item) {
            if ($item->getArtisan() === $this->getUser()) {
                $isArtisan = true;
                break;
            }
        }

        if (!$isArtisan) {
            throw $this->createAccessDeniedException('You are not authorized to view this order.');
        }

        return $this->render('artisan/order/show.html.twig', [
            'order' => $order,
            'livreurs' => $this->getDoctrine()->getRepository(Livreur::class)->findAll(),
        ]);
    }

    #[Route('/{id}/assign-livreur', name: 'app_artisan_order_assign_livreur', methods: ['POST'])]
    public function assignLivreur(
        Request $request,
        string $id,
        EntityManagerInterface $entityManager,
        LivreurRepository $livreurRepository,
        OrdersRepository $ordersRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');

        $order = $ordersRepository->findOneBy(['order_id' => $id]);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée pour l\'ID ' . $id);
        }

        $isArtisan = false;
        foreach ($order->getItems() as $item) {
            if ($item->getArtisan() === $this->getUser()) {
                $isArtisan = true;
                break;
            }
        }

        if (!$isArtisan) {
            throw $this->createAccessDeniedException('You are not authorized to modify this order.');
        }

        $livreurId = $request->request->get('livreur');
        $livreur = $livreurRepository->find($livreurId);

        if (!$livreur) {
            $this->addFlash('error', 'Livreur non trouvé.');
            return $this->redirectToRoute('app_artisan_order_show', ['id' => $id]);
        }

        $commande = $order->getCommande();
        if (!$commande) {
            $commande = new Commande();
            $commande->setId($order->getOrderId());
            $commande->setClient($order->getFullName() ?? 'Client inconnu');
            $commande->setEmail($order->getEmail());
            $commande->setAdresse($order->getAddress());
            $commande->setTelephone($order->getPhone());
            $commande->setStatut('En préparation');
            $commande->setOrder($order);
            $entityManager->persist($commande);
        }

        $commande->setLivreur($livreur);
        $order->setStatus(Orders::STATUS_SHIPPED);
        $commande->setStatut('En cours de livraison');

        $entityManager->flush();

        $this->addFlash('success', 'Livreur assigné avec succès à la commande.');
        return $this->redirectToRoute('app_artisan_order_show', ['id' => $id]);
    }

    #[Route('/{id}/update-status', name: 'app_artisan_order_update_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        string $id,
        EntityManagerInterface $entityManager,
        OrdersRepository $ordersRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');

        $order = $ordersRepository->findOneBy(['order_id' => $id]);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée pour l\'ID ' . $id);
        }

        $isArtisan = false;
        foreach ($order->getItems() as $item) {
            if ($item->getArtisan() === $this->getUser()) {
                $isArtisan = true;
                break;
            }
        }

        if (!$isArtisan) {
            throw $this->createAccessDeniedException('You are not authorized to modify this order.');
        }

        $status = $request->request->get('status');
        $commande = $order->getCommande();

        if (!$commande) {
            $commande = new Commande();
            $commande->setId($order->getOrderId());
            $commande->setClient($order->getFullName() ?? 'Client inconnu');
            $commande->setEmail($order->getEmail());
            $commande->setAdresse($order->getAddress());
            $commande->setTelephone($order->getPhone());
            $commande->setOrder($order);
            $entityManager->persist($commande);
        }

        $commande->setStatut($status);

        switch ($status) {
            case 'En préparation':
                $order->setStatus(Orders::STATUS_PAID);
                break;
            case 'En cours de livraison':
                $order->setStatus(Orders::STATUS_SHIPPED);
                break;
            case 'Livrée':
                $order->setStatus(Orders::STATUS_DELIVERED);
                break;
            case 'Annulée':
                $order->setStatus(Orders::STATUS_CANCELLED);
                break;
        }

        $entityManager->flush();

        $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
        return $this->redirectToRoute('app_artisan_order_show', ['id' => $id]);
    }
}