<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Form\LivreurType;
use App\Repository\LivreurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/artisan/livreur')]
class LivreurController extends AbstractController
{
    #[Route('/', name: 'app_livreur_index', methods: ['GET'])]
    public function index(LivreurRepository $livreurRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');
        
        return $this->render('livreur/index.html.twig', [
            'livreurs' => $livreurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_livreur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');
        
        $livreur = new Livreur();
        $form = $this->createForm(LivreurType::class, $livreur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livreur);
            $entityManager->flush();

            $this->addFlash('success', 'Livreur ajouté avec succès!');
            return $this->redirectToRoute('app_livreur_index');
        }

        return $this->render('livreur/new.html.twig', [
            'livreur' => $livreur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livreur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livreur $livreur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');
        
        $form = $this->createForm(LivreurType::class, $livreur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Livreur mis à jour avec succès!');
            return $this->redirectToRoute('app_livreur_index');
        }

        return $this->render('livreur/edit.html.twig', [
            'livreur' => $livreur,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_livreur_delete', methods: ['POST'])]
    public function delete(Request $request, Livreur $livreur, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ARTISAN');
        
        if ($this->isCsrfTokenValid('delete'.$livreur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livreur);
            $entityManager->flush();
            $this->addFlash('success', 'Livreur supprimé avec succès!');
        }

        return $this->redirectToRoute('app_livreur_index');
    }
}
