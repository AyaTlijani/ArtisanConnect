<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;

final class CheckMailController extends AbstractController
{
    #[Route('/check-email', name: 'app_check_email', methods: ['GET'])]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $userEmail = $request->getSession()->get('signup_email');
        if (!$userEmail) {
            $this->addFlash('error', 'No email found. Please sign up again.');
            return $this->redirectToRoute('app_sign_up');
        }

        $user = $entityManager->getRepository(\App\Entity\User::class)->findOneBy(['email' => $userEmail]);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_sign_up');
        }

        $verificationLink = $this->generateUrl(
            'app_verify_success',
            ['email' => $userEmail],
            \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('onboarding@resend.dev')
            ->to($userEmail)
            ->subject('Verify your registration - ArtisanConnect')
            ->html('<p>Click the link below to verify your registration:</p>
                    <p><a href="' . $verificationLink . '">Verify Registration</a></p>');

        try {
            $mailer->send($email);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Failed to send email: ' . $e->getMessage());
            return $this->redirectToRoute('app_sign_up');
        }

        return $this->render('CheckMail.html.twig', [
            'userEmail' => $userEmail,
        ]);
    }

    #[Route('/verify-success', name: 'app_verify_success', methods: ['GET'])]
    public function verifySuccess(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userEmail = $request->query->get('email');
        if ($userEmail) {
            $user = $entityManager->getRepository(\App\Entity\User::class)->findOneBy(['email' => $userEmail]);
            if ($user && !$user->isVerified()) {
                $user->setIsVerified(true);
                $entityManager->flush();
            }
        }
        return $this->render('verify_success.html.twig');
    }
}