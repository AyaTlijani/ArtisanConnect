<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Atelier;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\AtelierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route('', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/new/{idAtelier}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $idAtelier, AtelierRepository $atelierRepository, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        $atelier = $atelierRepository->find($idAtelier);
        if (!$atelier) {
            $this->addFlash('error', 'Atelier non trouvé');
            return $this->redirectToRoute('app_atelier_index_user');
        }

        $availableQuantite = $atelier->getQuantite();
        if ($availableQuantite <= 0) {
            $this->addFlash('error', 'Désolé, il n\'y a plus de places disponibles pour cet atelier.');
            return $this->redirectToRoute('app_atelier_index_user');
        }

        $reservation = new Reservation();
        $reservation->setService($atelier);
        
        if ($this->getUser()) {
            $user = $this->getUser();
            $reservation->setNom($user->getFullName() ?? $user->getUsername());
            $reservation->setEmail($user->getEmail());
            if (method_exists($user, 'getPhoneNumber') && $user->getPhoneNumber()) {
                $reservation->setNumeroTelephone($user->getPhoneNumber());
            }
        }

        $reservation->setStatut('En attente');
        if ($atelier->getPrix() > 0) {
            $reservation->setMontant($atelier->getPrix());
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_quantity' => $availableQuantite,
            'action' => $this->generateUrl('app_reservation_new', ['idAtelier' => $idAtelier])
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nombrePersonnes = $reservation->getNombrePersonnes();
            if ($nombrePersonnes > $availableQuantite) {
                $this->addFlash('error', 'Le nombre de personnes demandé dépasse la quantité disponible.');
                return $this->redirectToRoute('app_reservation_new', ['idAtelier' => $idAtelier]);
            }
            
            $reservation->setMontant($reservation->getService()->getPrix() * $nombrePersonnes);
            $newQuantite = $availableQuantite - $nombrePersonnes;
            $atelier->setQuantite($newQuantite);
            
            $entityManager->persist($reservation);
            $entityManager->persist($atelier);
            
            // Enable logging to see the query
            $logger = $doctrine->getManager()->getConnection()->getConfiguration()->getSQLLogger();
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre réservation a été enregistrée avec succès !');
            return $this->redirectToRoute('app_reservation_show', ['idReservation' => $reservation->getIdReservation()]);
        } else if ($form->isSubmitted()) {
            $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }
        
        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'atelier' => $atelier,
            'available_quantity' => $availableQuantite,
        ]);
    }

    #[Route('/{idReservation}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{idReservation}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getIdReservation(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}