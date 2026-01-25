<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Repository\AtelierRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(
        Request $request,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        AtelierRepository $atelierRepository,
        LocationRepository $locationRepository
    ): Response {
        $name = $request->query->get('name');
        $date = $request->query->get('date');
        $price = $request->query->get('price', null);
        $status = $request->query->get('status');
        $organizer = $request->query->get('organizer', null);
        $atelier = $request->query->get('atelier', null);
        $location = $request->query->get('location', null);
        $startTime = $request->query->get('startTime');
        $capacity = $request->query->get('capacity', null);

        $events = $eventRepository->findBySearchCriteria(
            $name,
            $date,
            $price ? (float) $price : null,
            $status,
            $organizer ? (int) $organizer : null,
            $atelier ? (int) $atelier : null,
            $location ? (int) $location : null,
            $startTime,
            $capacity ? (int) $capacity : null
        );

        $organizers = $userRepository->findAll();
        $ateliers = $atelierRepository->findAll();
        $locations = $locationRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'organizers' => $organizers,
            'ateliers' => $ateliers,
            'locations' => $locations,
        ]);
    }

    #[Route('/{idEvent}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/search/live', name: 'app_event_search_live', methods: ['GET'])]
    public function liveSearch(
        Request $request,
        EventRepository $eventRepository
    ): JsonResponse {
        try {
            $name = $request->query->get('name');
            $date = $request->query->get('date');
            $price = $request->query->get('price', null);
            $status = $request->query->get('status');
            $organizer = $request->query->get('organizer', null);
            $atelier = $request->query->get('atelier', null);
            $location = $request->query->get('location', null);
            $startTime = $request->query->get('startTime');
            $capacity = $request->query->get('capacity', null);

            $events = $eventRepository->findBySearchCriteria(
                $name,
                $date,
                $price ? (float) $price : null,
                $status,
                $organizer ? (int) $organizer : null,
                $atelier ? (int) $atelier : null,
                $location ? (int) $location : null,
                $startTime,
                $capacity ? (int) $capacity : null
            );

            $results = array_map(function (Event $event) {
                return [
                    'title' => $event->getName(),
                    'description' => $event->getDescription() ?: 'Aucune description disponible',
                    'type' => 'Événement',
                    'url' => $this->generateUrl('app_event_show', ['idEvent' => $event->getIdEvent()]),
                    'date' => $event->getDate() ? $event->getDate()->format('Y-m-d') : null,
                    'price' => $event->getPrice() !== null ? $event->getPrice() . ' TND' : 'Gratuit'
                ];
            }, $events);

            return new JsonResponse([
                'success' => true,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Event search error: ' . $e->getMessage(), [
                'exception' => $e,
                'query' => $request->query->all()
            ]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Une erreur est survenue lors de la recherche.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}