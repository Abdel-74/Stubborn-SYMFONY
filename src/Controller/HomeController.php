<?php

namespace App\Controller;

use App\Repository\SweatshirtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SweatshirtRepository $sweatshirtRepository): Response
    {
        $user = $this->getUser();

        $highlightedProducts = $sweatshirtRepository->findBy(['is_featured' => true], null, 3);

        return $this->render('home/index.html.twig', [
            'products' => $highlightedProducts,
            'user' => $user,
        ]);
    }
}
