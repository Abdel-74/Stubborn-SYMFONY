<?php

namespace App\Controller;

use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartItemRepository $cartItemRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder au panier.');
        }

        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item->getSweatshirt()->getPrice() * $item->getQuantity();
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
        ]);
    }


    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove(
        CartItemRepository $cartItemRepository, 
        EntityManagerInterface $entityManager,
        int $id
    ): Response{
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier le panier.');
        }

        $cartItem = $cartItemRepository->find($id);
        if (!$cartItem || $cartItem->getUser() !== $user) {
            throw $this->createNotFoundException('Article introuvable dans le panier.');
        }

        $entityManager->remove($cartItem);
        $entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }

}
