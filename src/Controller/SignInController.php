<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SignInController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/signin', name: 'app_sign_in')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect based on role
        if ($this->getUser()) {
            $user = $this->getUser();
            
            // Check if user has ROLE_ARTISAN or ROLE_ADMIN
            if (in_array('ROLE_ARTISAN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('admin_event_index');
            }
            
            // Default redirect for other roles (CLIENT)
            return $this->redirectToRoute('app_home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('Security/signin.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error ? $error->getMessage() : null,
        ]);
    }


}