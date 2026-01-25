<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/admin/event')]
class EventAdminController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'admin_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, Request $request, \Knp\Component\Pager\PaginatorInterface $paginator): Response
    {
        $query = $eventRepository->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC')
            ->addOrderBy('e.startTime', 'ASC')
            ->getQuery();
            
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        return $this->render('event_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'admin_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $eventRepository->save($event, true);
                    $this->addFlash('success', 'Événement créé avec succès !');
                    return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'Un événement avec ce nom existe déjà.');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'événement : ' . $e->getMessage());
                    $this->logger->error('Event creation failed', [
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire : ' . implode(' ', $errors));

                // Log form data for debugging (not exposed to user)
                $formData = $form->getData();
                $this->logger->debug('Form submission failed', [
                    'name' => $formData->getName(),
                    'date' => $formData->getDate() ? $formData->getDate()->format('Y-m-d') : null,
                    'startTime' => $formData->getStartTime() ? $formData->getStartTime()->format('H:i') : null,
                    'endTime' => $formData->getEndTime() ? $formData->getEndTime()->format('H:i') : null,
                    'capacity' => $formData->getCapacity(),
                    'price' => $formData->getPrice(),
                    'status' => $formData->getStatus(),
                    'location' => $formData->getLocation() ? $formData->getLocation()->getAdresse() : null,
                ]);
            }
        }

        return $this->render('event_admin/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event_admin/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $eventRepository->save($event, true);
                $this->addFlash('success', 'Événement mis à jour avec succès !');
                return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Un événement avec ce nom existe déjà.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'événement : ' . $e->getMessage());
                $this->logger->error('Event update failed', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $this->render('event_admin/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getIdEvent(), $request->request->get('_token'))) {
            try {
                $eventRepository->remove($event, true);
                $this->addFlash('success', 'Événement supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'événement : ' . $e->getMessage());
                $this->logger->error('Event deletion failed', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
    }
}