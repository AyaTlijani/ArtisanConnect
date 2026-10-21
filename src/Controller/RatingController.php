<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Produit;
use App\Entity\Atelier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;

class RatingController extends AbstractController
{
    private $entityManager;
    private $security;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, Security $security, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
    }

    #[Route('/rate', name: 'app_rate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rate(Request $request): JsonResponse
    {
        // Only allow AJAX requests
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        // Get current user
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Get request data
        $data = json_decode($request->getContent(), true);
        $entityId = $data['entityId'] ?? null;
        $entityType = $data['entityType'] ?? null;
        $ratingValue = (int) ($data['rating'] ?? 0);

        // Validate data
        if (!$entityId || !$entityType || $ratingValue < 1 || $ratingValue > 5) {
            return $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        // Get the entity to rate
        $entity = $this->getEntity($entityType, $entityId);
        if (!$entity) {
            return $this->json(['success' => false, 'message' => 'Entity not found'], 404);
        }

        // Check if user already rated this entity
        $existingRating = $this->entityManager->getRepository(Rating::class)->findOneBy([
            'user' => $user,
            $entityType => $entity,
        ]);

        if ($existingRating) {
            return $this->json([
                'success' => false,
                'message' => 'You have already rated this item.',
                'averageRating' => $entity->getAverageRating(),
                'ratingCount' => $entity->getRatings()->count()
            ]);
        }

        // Create new rating
        $rating = new Rating();
        $rating->setUser($user);
        $rating->setRating($ratingValue);
        
        if ($entityType === 'produit') {
            $rating->setProduit($entity);
        } else {
            $rating->setAtelier($entity);
        }

        // Save the rating
        $this->entityManager->persist($rating);
        $this->entityManager->flush();

        // Update average rating
        $this->updateAverageRating($entity, $entityType);

        return $this->json([
            'success' => true,
            'averageRating' => $entity->getAverageRating(),
            'ratingCount' => $entity->getRatings()->count()
        ]);
    }

    private function getEntity(string $type, int $id)
    {
        try {
            $repository = $type === 'produit' 
                ? $this->entityManager->getRepository(Produit::class)
                : $this->entityManager->getRepository(Atelier::class);
                
            return $repository->find($id);
        } catch (\Exception $e) {
            $this->logger->error('Error getting entity: ' . $e->getMessage());
            return null;
        }
    }

    private function updateAverageRating($entity, string $entityType): void
    {
        $ratings = $entity->getRatings();
        
        if ($ratings->isEmpty()) {
            $entity->setAverageRating(null);
        } else {
            $total = 0;
            foreach ($ratings as $rating) {
                $total += $rating->getRating();
            }
            $average = $total / $ratings->count();
            $entity->setAverageRating(round($average, 1));
        }

        // Update the entity's average rating
        $entity->calculateAverageRating();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
