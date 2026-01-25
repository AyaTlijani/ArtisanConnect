<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Please log in to view your cart.');
            return $this->redirectToRoute('app_sign_in');
        }

        $paniers = $panierRepository->findBy(['idClient' => $user]);

        return $this->render('cart/index.html.twig', [
            'paniers' => $paniers,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('add_to_cart_' . $produit->getIdProduit(), $submittedToken)) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 400);
            }
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('app_shop_index');
        }

        // Get requested quantity
        $requestedQuantity = $request->request->getInt('quantite', 1);

        // Validate stock availability
        if ($requestedQuantity <= 0) {
            $errorMessage = 'Requested quantity must be greater than zero.';
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $errorMessage], 400);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_shop_index');
        }

        if (!$produit->isDisponibilite()) {
            $errorMessage = 'Product is not available.';
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $errorMessage], 400);
            }
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_shop_index');
        }

        $availableQuantity = $produit->getQuantite();
        if ($requestedQuantity > $availableQuantity) {
            $this->addFlash('error', "Requested quantity ($requestedQuantity) is not available. Only $availableQuantity items in stock.");
            return $this->redirectToRoute('app_shop_index');
        }

        // Begin transaction for stock update and cart addition
        $entityManager->beginTransaction();
        try {
            // Reduce the stock
            $newQuantity = $availableQuantity - $requestedQuantity;
            $produit->setQuantite($newQuantity);

            // Update availability if quantity reaches zero
            if ($newQuantity === 0) {
                $produit->setDisponibilite(false);
            }

            // Persist product changes
            $entityManager->persist($produit);

            // Get current user
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to add items to cart.');
                return $this->redirectToRoute('app_sign_in');
            }

            // Check if product already exists in user's cart
            $existingCartItem = $entityManager->getRepository(Panier::class)->findOneBy([
                'idClient' => $user,
                'idProduit' => $produit
            ]);

            if ($existingCartItem) {
                // Update quantity if item already in cart
                $newQuantity = $existingCartItem->getQuantite() + $requestedQuantity;
                $existingCartItem->setQuantite($newQuantity);
                $existingCartItem->setPrixTotal($produit->getPrix() * $newQuantity);
                $panier = $existingCartItem;
            } else {
                // Create new cart item
                $panier = new Panier();
                $panier->setIdClient($user);
                $panier->setIdProduit($produit);
                $panier->setQuantite($requestedQuantity);
                $panier->setPrixTotal($produit->getPrix() * $requestedQuantity);
                $panier->setCalculerPrix(1);
            }

            // Persist cart entry
            $entityManager->persist($panier);

            // Commit changes to database
            $entityManager->flush();
            $entityManager->commit();

            $successMessage = 'Product added to cart!';
            $cartCount = $entityManager->getRepository(Panier::class)->count([]);
            
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => $successMessage,
                    'cartCount' => $cartCount,
                    'productName' => $produit->getNom()
                ]);
            }
            
            $this->addFlash('success', $successMessage);
        } catch (\Exception $e) {
            $entityManager->rollback();
            $errorMessage = 'An error occurred while adding the product to the cart: ' . $e->getMessage();
            
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => $errorMessage], 500);
            }
            
            $this->addFlash('error', $errorMessage);
            return $this->redirectToRoute('app_shop_index');
        }

        // Redirect back to the shop page instead of the cart page
        return $this->redirectToRoute('app_shop_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        // Verify CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('update' . $panier->getIdPanier(), $submittedToken)) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Invalid CSRF token.'], 400);
            }
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('app_cart_index');
        }

        $newQuantity = $request->request->getInt('quantity', 1);
        $produit = $panier->getIdProduit();

        // Validate quantity
        if ($newQuantity <= 0) {
            return $this->redirectToRoute('app_cart_remove', ['id' => $panier->getIdPanier()]);
        }

        // Check stock
        $availableQuantity = $produit->getQuantite() + $panier->getQuantite();
        if ($newQuantity > $availableQuantity) {
            $this->addFlash('error', sprintf('Only %d items available in stock.', $availableQuantity));
            return $this->redirectToRoute('app_cart_index');
        }

        // Calculate stock difference
        $quantityDifference = $newQuantity - $panier->getQuantite();
        
        // Update product stock
        $produit->setQuantite($produit->getQuantite() - $quantityDifference);
        
        // Update cart item
        $panier->setQuantite($newQuantity);
        $panier->setPrixTotal($produit->getPrix() * $newQuantity);
        
        $entityManager->persist($produit);
        $entityManager->persist($panier);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'quantity' => $newQuantity,
                'itemTotal' => $panier->getPrixTotal(),
                'cartTotal' => $this->getTotalCartAmount($entityManager),
                'productStock' => $produit->getQuantite()
            ]);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    /**
     * Calculate the total amount of items in the cart for the current user
     */
    private function getTotalCartAmount(EntityManagerInterface $entityManager): float
    {
        $user = $this->getUser();
        if (!$user) {
            return 0.0;
        }

        $panierRepository = $entityManager->getRepository(Panier::class);
        return $panierRepository->getCartTotal($user);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Panier $panier, EntityManagerInterface $entityManager, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$panier->getIdPanier(), $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid CSRF token.'
                ], 400);
            }
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_cart_index');
        }

        // Begin transaction for stock restoration and cart removal
        $entityManager->beginTransaction();
        try {
            // Save product name for the response
            $productName = $panier->getIdProduit()->getNom();
            
            // Restore stock to the product
            $produit = $panier->getIdProduit();
            $quantityToRestore = $panier->getQuantite();
            $currentQuantity = $produit->getQuantite();
            $produit->setQuantite($currentQuantity + $quantityToRestore);

            // Restore availability if product was unavailable
            if (!$produit->isDisponibilite() && $produit->getQuantite() > 0) {
                $produit->setDisponibilite(true);
            }

            // Persist product changes
            $entityManager->persist($produit);

            // Remove cart entry
            $entityManager->remove($panier);


            // Commit changes to database
            $entityManager->flush();
            $entityManager->commit();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Product removed from cart!',
                    'productName' => $productName,
                    'cartCount' => $entityManager->getRepository(Panier::class)->count([])
                ]);
            }
            
            $this->addFlash('success', 'Product removed from cart!');
        } catch (\Exception $e) {
            $entityManager->rollback();
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'An error occurred while removing the product from the cart.'
                ], 500);
            }
            $this->addFlash('error', 'An error occurred while removing the product from the cart.');
        }

        return $this->redirectToRoute('app_cart_index');
    }
}