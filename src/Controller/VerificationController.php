<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerificationController extends AbstractController
{
    #[Route('/verification', name: 'app_verification', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $correctCode = $session->get('verification_code');
        $email = $session->get('reset_email');

        if (!$correctCode || !$email) {
            $this->addFlash('error', 'Session expired. Please request a new password reset.');
            return $this->redirectToRoute('app_reset_password');
        }

        if ($request->isMethod('POST')) {
            $submittedCode = $request->request->get('code');
            if ($submittedCode == $correctCode) {
                return $this->redirectToRoute('app_create_new_password');
            } else {
                $this->addFlash('error', 'Invalid verification code.');
            }
        }

        return $this->render('Security/verification.html.twig', [
            'error' => $session->getFlashBag()->get('error'),
        ]);
    }
}