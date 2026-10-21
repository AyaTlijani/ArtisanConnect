<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livreur;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/commande')]
class CommandeController extends AbstractController
{
    #[Route('/assign-livreur/{id}', name: 'app_commande_assign_livreur', methods: ['POST'])]
    public function assignLivreur(
        Commande $commande,
        Request $request,
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository
    ): Response {
        if (!$this->isCsrfTokenValid('assign_livreur' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_orders');
        }

        $livreurId = $request->request->get('livreur_id');
        $livreur = $entityManager->getRepository(Livreur::class)->find($livreurId);

        if (!$livreur) {
            $this->addFlash('error', 'Livreur not found.');
            return $this->redirectToRoute('app_orders');
        }

        $commande->setLivreur($livreur);
        $commande->setStatut('En livraison');
        $entityManager->flush();

        $this->addFlash('success', 'Livreur assigned and status updated to En livraison.');
        return $this->redirectToRoute('app_orders');
    }
}