<?php

namespace App\Controller;

use App\Entity\Atelier;
use App\Form\AtelierType;
use App\Repository\AtelierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/atelier')]
final class AtelierController extends AbstractController
{
    #[Route(name: 'app_atelier_index', methods: ['GET'])]
    public function index(AtelierRepository $atelierRepository): Response
    {
        return $this->render('atelier/index.html.twig', [
            'ateliers' => $atelierRepository->findAll(),
        ]);
    }

    #[Route('/user', name: 'app_atelier_index_user', methods: ['GET'])]
    public function indexUser(AtelierRepository $atelierRepository): Response
    {
        return $this->render('atelier/index_user.html.twig', [
            'ateliers' => $atelierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_atelier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $atelier = new Atelier();
        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($atelier);
                $entityManager->flush();
                $this->addFlash('success', 'L\'atelier a été créé avec succès.');
                return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'atelier.');
            }
        }

        return $this->render('atelier/new.html.twig', [
            'atelier' => $atelier,
            'form' => $form,
        ]);
    }

    #[Route('/{idAtelier}', name: 'app_atelier_show', methods: ['GET'])]
    public function show(Request $request, AtelierRepository $atelierRepository): Response
    {
        $idAtelier = $request->attributes->get('idAtelier');
        $atelier = $atelierRepository->find($idAtelier);

        if (!$atelier) {
            throw $this->createNotFoundException('Atelier not found');
        }

        return $this->render('atelier/show.html.twig', [
            'atelier' => $atelier,
        ]);
    }

    #[Route('/{idAtelier}/edit', name: 'app_atelier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AtelierRepository $atelierRepository, EntityManagerInterface $entityManager): Response
    {
        $idAtelier = $request->attributes->get('idAtelier');
        $atelier = $atelierRepository->find($idAtelier);

        if (!$atelier) {
            throw $this->createNotFoundException('Atelier not found');
        }

        $form = $this->createForm(AtelierType::class, $atelier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'L\'atelier a été modifié avec succès.');
                return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'atelier.');
            }
        }

        return $this->render('atelier/edit.html.twig', [
            'atelier' => $atelier,
            'form' => $form,
        ]);
    }

    #[Route('/{idAtelier}', name: 'app_atelier_delete', methods: ['POST'])]
    public function delete(Request $request, AtelierRepository $atelierRepository, EntityManagerInterface $entityManager): Response
    {
        $idAtelier = $request->attributes->get('idAtelier');
        $atelier = $atelierRepository->find($idAtelier);

        if (!$atelier) {
            throw $this->createNotFoundException('Atelier not found');
        }

        if ($this->isCsrfTokenValid('delete' . $idAtelier, $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($atelier);
                $entityManager->flush();
                $this->addFlash('success', 'L\'atelier a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'atelier.');
            }
        }

        return $this->redirectToRoute('app_atelier_index', [], Response::HTTP_SEE_OTHER);
    }
}