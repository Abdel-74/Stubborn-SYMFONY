<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\SweatshirtRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(SweatshirtRepository $sweatshirtRepository, Request $request): Response
    {
        // Récupération de la fourchette de prix depuis la requête
        $priceRange = $request->query->get('price_range', 'all');
        
        // Filtrage des produits selon la fourchette de prix
        $products = [];
        if ($priceRange === '10-29') {
            $products = $sweatshirtRepository->findByPriceRange(10, 29);
        } elseif ($priceRange === '29-35') {
            $products = $sweatshirtRepository->findByPriceRange(29, 35);
        } elseif ($priceRange === '35-50') {
            $products = $sweatshirtRepository->findByPriceRange(35, 50);
        } else {
            $products = $sweatshirtRepository->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'price_range' => $priceRange,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_detail')]
    public function detail(int $id, SweatshirtRepository $sweatshirtRepository): Response
    {
        $product = $sweatshirtRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $sizesWithStocks = [];
        $sizes = $product->getSize();
        $stocks = $product->getStock();

        foreach ($sizes as $index => $size) {
            $sizesWithStocks[] = [
                'size' => $size,
                'stock' => $stocks[$index] ?? 0,
            ];
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'sizesWithStocks' => $sizesWithStocks,
        ]);
    
    }

    #[Route('/product/{id}/add-to-cart', name: 'app_product_add_to_cart', methods: ['POST'])]
    public function addToCart(
        Request $request, 
        int $id, 
        SweatshirtRepository $sweatshirtRepository, 
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $sweatshirt = $sweatshirtRepository->find($id);
        if (!$sweatshirt) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter au panier.');
        }

        $size = $request->request->get('size');
        $quantity = $request->request->getInt('quantity', 1);

        // Vérifier si l'article existe déjà dans le panier
        $cartItem = $cartItemRepository->findOneBy([
            'sweatshirt' => $sweatshirt,
            'user' => $user,
            'size' => $size,
        ]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setSweatshirt($sweatshirt);
            $cartItem->setUser($user);
            $cartItem->setSize($size);
            $cartItem->setQuantity($quantity);
            
            $entityManager->persist($cartItem);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }



}
