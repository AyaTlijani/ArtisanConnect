<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class PasswordController extends AbstractController
{
    #[Route('/create-new-password', name: 'app_create_new_password', methods: ['GET', 'POST'])]
    public function createNewPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $email = $request->getSession()->get('reset_email');
        if (!$email) {
            $this->addFlash('error', 'Session expired. Please request a new password reset.');
            return $this->redirectToRoute('app_reset_password');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_reset_password');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Password must be at least 8 characters long.');
            } elseif ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
            } else {
                $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                $user->setUpdatedAt(new \DateTime());
                $entityManager->persist($user);
                $entityManager->flush();
                $request->getSession()->remove('reset_email');
                $this->addFlash('success', message: 'Your password has been successfully updated.');
                return $this->redirectToRoute('app_sign_in');
            }
        }

        return $this->render('Security/2Pass.html.twig');
    }
}