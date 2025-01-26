<?php

namespace App\Controller;

use App\Entity\Sweatshirt;
use App\Form\SweatshirtType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sweatshirt = new Sweatshirt();
        $form = $this->createForm(SweatshirtType::class, $sweatshirt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        
            foreach ($sizes as $size) {
                $stockValue = $form->get('stock' . $size)->getData();
        
                $sweatshirt->setStockForSize($size, $stockValue);
            }
        
            $sweatshirt->setSize($sizes);
            
            $entityManager->persist($sweatshirt);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index');
        }

        $sweatshirts = $entityManager->getRepository(Sweatshirt::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'form' => $form->createView(),
            'sweatshirts' => $sweatshirts,
        ]);
    }

    #[Route('/edit/{id}', name: '_edit', methods: ['POST'])]
    public function edit(Sweatshirt $sweatshirt, Request $request, EntityManagerInterface $entityManager): Response
    {
        $name = $request->request->get('name');
        $price = $request->request->get('price');
        $stocks = [
            'XS' => $request->request->get('stockXS'),
            'S' => $request->request->get('stockS'),
            'M' => $request->request->get('stockM'),
            'L' => $request->request->get('stockL'),
            'XL' => $request->request->get('stockXL'),
        ];

        $sweatshirt->setName($name);
        $sweatshirt->setPrice($price);
        foreach ($stocks as $size => $value) {
            $sweatshirt->setStockForSize($size, $value);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_index');
    }


    #[Route('/delete/{id}', name: '_delete')]
    public function delete(Sweatshirt $sweatshirt, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($sweatshirt);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_index');
    }
}
