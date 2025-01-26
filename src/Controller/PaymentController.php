<?php

namespace App\Controller;

use App\Service\StripeService;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/create-intent', name: 'app_payment_create_intent', methods: ['GET', 'POST'])]
    public function createPaymentIntent(StripeService $stripeService, CartItemRepository $cartItemRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour continuer votre achat.');
        }

        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->getSweatshirt()->getPrice() * $item->getQuantity();
        }

        $totalPriceInCents = (int) round($totalPrice * 100);

        $intent = $stripeService->createPaymentIntent($totalPriceInCents);

        // Passer la clientSecret au template
        return $this->render('payment/payment.html.twig', [
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
            'clientSecret' => $intent->client_secret, 
        ]);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
    
        $cartItems = $cartItemRepository->findBy(['user' => $user]);
    
        foreach ($cartItems as $cartItem) {
            $sweatshirt = $cartItem->getSweatshirt();
        
            $stock = $sweatshirt->getStock();
            $size = $cartItem->getSize();   

            $sizeMapping = ['XS', 'S', 'M', 'L', 'XL'];
            $sizeIndex = array_search($size, $sizeMapping);
        
            if ($sizeIndex === false || !isset($stock[$sizeIndex])) {
                throw $this->createNotFoundException('Taille non disponible pour l\'article : ' . $sweatshirt->getName());
            }
        
            // Calculer le nouveau stock pour cette taille
            $currentStock = $stock[$sizeIndex];
            $newStock = $currentStock - $cartItem->getQuantity();
        
            if ($newStock < 0) {
                throw $this->createNotFoundException('Stock insuffisant pour l\'article : ' . $sweatshirt->getName() . ', taille : ' . $size);
            }
        
            $stock[$sizeIndex] = $newStock;
            $sweatshirt->setStock($stock);
        
            $entityManager->remove($cartItem);
        }     
    
        $entityManager->flush();
    
        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/failure', name: 'app_payment_failure')]
    public function failure(): Response
    {
        return $this->render('payment/failure.html.twig');
    }
}
