<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shop')]
class ProduitController extends AbstractController
{
    #[Route('/{idProduit}', name: 'app_shop_show', methods: ['GET'])]
    public function show(int $idProduit, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($idProduit);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}
