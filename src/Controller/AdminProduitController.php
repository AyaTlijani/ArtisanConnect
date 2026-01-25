<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/produit')]
class AdminProduitController extends AbstractController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    #[Route('/', name: 'admin_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('admin_produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($produit);
                $entityManager->flush();
                $this->addFlash('success', 'Product created successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating product: ' . $e->getMessage());
            }

            return $this->redirectToRoute('admin_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate the entity using Symfony's validator
            $errors = $this->validator->validate($produit);

            // Custom validation for critical fields
            $validationErrors = [];
            if (empty($produit->getNom())) {
                $validationErrors[] = 'The product name is required.';
            }
            if ($produit->getPrix() === null) {
                $validationErrors[] = 'The price is required.';
            }
            if ($produit->getQuantite() === null) {
                $validationErrors[] = 'The quantity is required.';
            }

            // Combine validator errors with custom errors
            foreach ($errors as $error) {
                $validationErrors[] = $error->getMessage();
            }

            if (empty($validationErrors) && $form->isValid()) {
                $imageFile = $form->get('image')->getData();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $produit->setImage($newFilename);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Product updated successfully!');
                return $this->redirectToRoute('admin_produit_index');
            } else {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('admin_produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getIdProduit(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($produit);
                $entityManager->flush();
                $this->addFlash('success', 'L\'atelier a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'atelier.');
            }
        }

        return $this->redirectToRoute('admin_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'admin_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('admin_produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}