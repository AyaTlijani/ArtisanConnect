<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('Security/reset_password.html.twig', [
                'error' => null,
                'emailValue' => '',
            ]);
        }

        $submittedEmail = $request->request->get('email');
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $submittedEmail]);

        if (!$user) {
            $this->addFlash('error', 'No account found with this email address.');
            return $this->render('Security/reset_password.html.twig', [
                'error' => 'No account found with this email address.',
                'emailValue' => $submittedEmail,
            ]);
        }

        $verificationCode = random_int(100000, 999999);
        $session = $request->getSession();
        $session->set('verification_code', $verificationCode);
        $session->set('reset_email', $submittedEmail);

        try {
            $email = (new Email())
                ->from('onboarding@resend.dev')
                ->to($submittedEmail)
                ->subject('Your Password Reset Code - ArtisanConnect')
                ->html('
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                        <h2 style="color: #333;">ArtisanConnect Password Reset</h2>
                        <p>Hello,</p>
                        <p>Your verification code is:</p>
                        <p style="font-size: 24px; font-weight: bold; color: #007bff; margin: 20px 0; text-align: center;">
                            ' . $verificationCode . '
                        </p>
                        <p>Please enter this code on the verification page to complete your password reset.</p>
                        <p>If you did not request this code, please ignore this email.</p>
                        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
                        <p style="font-size: 0.9em; color: #777;">Thanks,<br>The ArtisanConnect Team</p>
                    </div>
                ');

            $mailer->send($email);
            $this->addFlash('success', 'A verification code has been sent to your email.');
            return $this->redirectToRoute('app_verification');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send email: ' . $e->getMessage());
            return $this->render('Security/reset_password.html.twig', [
                'error' => 'Failed to send email: ' . $e->getMessage(),
                'emailValue' => $submittedEmail,
            ]);
        }
    }
}