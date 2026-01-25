<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\GoogleUtilisateur;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SignInController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AuthenticationUtils $authenticationUtils;

    public function __construct(
        EntityManagerInterface $entityManager,
        AuthenticationUtils $authenticationUtils
    ) {
        $this->entityManager = $entityManager;
        $this->authenticationUtils = $authenticationUtils;
    }

    #[Route('/signin', name: 'app_sign_in', methods: ['GET', 'POST'])]
    public function signIn(Request $request, AuthenticationUtils $authenticationUtils, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('Security/signin.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfTokenManager->getToken('authenticate')->getValue()
        ]);
    }
}
