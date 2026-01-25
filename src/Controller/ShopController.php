<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'app_shop_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('shop/index.html.twig', [
            'produits' => $produitRepository->findAvailableProducts(),
        ]);
    }
}