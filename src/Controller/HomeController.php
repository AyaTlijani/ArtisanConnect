<?php

namespace App\Controller;

use App\Repository\AtelierRepository;
use App\Repository\ProduitRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        AtelierRepository $atelierRepository,
        ProduitRepository $produitRepository,
        EventRepository $eventRepository
    ): Response {
        return $this->render('home/index.html.twig', [
            'ateliers' => $atelierRepository->findBy([], ['id' => 'DESC'], 10), // Get latest 10 ateliers
            'produits' => $produitRepository->findBy(['disponibilite' => true], ['idProduit' => 'DESC'], 10), // Get latest 10 available products
            'events' => $eventRepository->findUpcomingEvents(10), // Get upcoming events (up to 10)
        ]);
    }
}