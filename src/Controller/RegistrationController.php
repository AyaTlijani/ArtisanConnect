<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/signup', name: 'app_register')]
    public function register(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        $user = new User();
        $form = $formFactory->create(RegistrationFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRole($form->get('accountType')->getData());
            $user->setCreatedAt(new \DateTime());
            $user->setUpdatedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();

            // Add success flash message
            $this->addFlash('success', 'Your account has been created successfully!');

            // Redirect to login page
            return $this->redirectToRoute('app_sign_in');
        }

        return $this->render('Security/signup.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
