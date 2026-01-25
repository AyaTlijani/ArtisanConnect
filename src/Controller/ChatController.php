<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function index()
    {
        return $this->render('chat/index.html.twig');
    }

    #[Route('/chat/send', name: 'app_chat_send', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $message = $request->request->get('message', '');
        $response = $this->getBotResponse($message);
        
        return $this->json([
            'response' => $response
        ]);
    }

    private function getBotResponse(string $message): string
    {
        $message = strtolower($message);
        
        if (str_contains($message, 'hour') || str_contains($message, 'open')) {
            return "⏰ We're open from 9 AM to 6 PM, Monday to Saturday. We're closed on Sundays and public holidays.";
        } elseif (str_contains($message, 'contact') || str_contains($message, 'support')) {
            return "📞 You can reach us at:\n• Phone: +216 12 345 678\n• Email: support@artisant.tn\n• Facebook: /ArtisanTunisia";
        } elseif (str_contains($message, 'where') || str_contains($message, 'location') || str_contains($message, 'address')) {
            return "📍 Our shop is located at:\n123 Artisan Street\nLa Manouba, 2010\nTunisia\n\nWe also offer free delivery in La Manouba area!";
        } elseif (str_contains($message, 'custom') || str_contains($message, 'special order')) {
            return "🎨 Yes! We love custom orders. You can discuss your requirements with our artisans in person or send us details at custom@artisant.tn with your ideas.";
        } elseif (str_contains($message, 'payment') || str_contains($message, 'pay') || str_contains($message, 'credit')) {
            return "💳 We accept:\n• Cash on delivery\n• Credit/Debit cards (Visa, MasterCard)\n• Mobile payment (MyT-Money, Flouci)\n• Bank transfer";
        } else {
            return "🤔 I'm not sure I understand. Could you please rephrase your question? You can ask me about our working hours, location, or services.";
        }
    }
}
