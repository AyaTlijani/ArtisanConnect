<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;

class SignUpController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/signup', name: 'app_sign_up', methods: ['GET', 'POST'])]
    public function signUp(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Manually validate the email field
            $email = $form->get('email')->getData();
            if (empty(trim($email))) {
                $form->get('email')->addError(new FormError('Please enter your email'));
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $form->get('email')->addError(new FormError('Please enter a valid email address'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Start transaction
                $this->entityManager->beginTransaction();
                
                // Get the selected role from the form
                $role = $form->get('accountType')->getData();
                
                // Debug log the role
                error_log('Selected role: ' . $role);
                
                // Set basic user properties with the role
                $user->setRole($role);
                $user->setCreatedAt(new \DateTime());
                $user->setUpdatedAt(new \DateTime());
                
                // Debug the final role being set
                error_log('User role after set: ' . $user->getRole());
                
                // Encode and set the password
                $plainPassword = $form->get('plainPassword')->getData();
                if (empty($plainPassword)) {
                    throw new \Exception('Password cannot be empty');
                }
                
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                
                // Debug log user data before save
                error_log('User data before save: ' . print_r([
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'roles' => $user->getRoles()
                ], true));
                
                // Save the user
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                
                // Commit transaction
                $this->entityManager->commit();
                
                // Set success message and redirect to check email page
                $request->getSession()->set('signup_email', $user->getEmail());
                return $this->render('Security/CheckMail.html.twig', [
                    'email' => $user->getEmail()
                ]);
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                if ($this->entityManager->getConnection()->isTransactionActive()) {
                    $this->entityManager->rollback();
                }
                
                // Log the error
                $errorMessage = sprintf(
                    'Registration error: %s in %s:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                );
                error_log($errorMessage);
                error_log('Stack trace: ' . $e->getTraceAsString());
                
                // Add flash message with more details in development
                $this->addFlash('error', 'An error occurred during registration: ' . $e->getMessage());
            }
        }

        return $this->render('Security/signup.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(Request $request): Response
    {
        $email = $request->getSession()->get('signup_email');
        if (!$email) {
            return $this->redirectToRoute('app_sign_up');
        }

        return $this->render('check_email.html.twig', [
            'email' => $email,
        ]);
    }
}