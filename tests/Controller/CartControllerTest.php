<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Sweatshirt;
use App\Entity\CartItem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use App\Repository\CartItemRepository;
use Symfony\Component\HttpFoundation\Client;

class CartControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient(); 

        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneByEmail('test@gmail.com');
    
        if (!$user) {
            $user = new User();
            $user->setEmail('test@gmail.com');
            $user->setName('Test User');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->client->getContainer()
                    ->get(UserPasswordHasherInterface::class)
                    ->hashPassword($user, 'password123')
            );
    
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function testCartIndex(): void
    {

        $userRepository = $this->client->getContainer()->get(UserRepository::class); 
        $user = $userRepository->findOneByEmail('test@gmail.com');

        $this->client->loginUser($user); 
        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful(); 
        $this->assertSelectorExists('h1:contains("Mon panier")'); 

    }

    public function testRemoveCartItem(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('test@gmail.com');

        $this->client->loginUser($user);

        // Simulez un article dans le panier
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $sweatshirt = new Sweatshirt();
        $sweatshirt->setPrice(20.00);
        $sweatshirt->setName('Test Sweatshirt');
        $sweatshirt->setStock([10, 10, 10, 10, 10]);
        $entityManager->persist($sweatshirt);

        $cartItem = new CartItem();
        $cartItem->setUser($user);
        $cartItem->setSweatshirt($sweatshirt);
        $cartItem->setQuantity(1);
        $cartItem->setSize('M');
        $entityManager->persist($cartItem);

        $entityManager->flush();

        $this->client->request('GET', '/cart/remove/' . $cartItem->getId());

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/cart');

    }

    public function testCheckout(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('test@gmail.com');

        $this->client->loginUser($user);

        // Simulez un article dans le panier
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $sweatshirt = new Sweatshirt();
        $sweatshirt->setPrice(20.00);
        $sweatshirt->setName('Test Sweatshirt');
        $sweatshirt->setStock([10, 10, 10, 10, 10]);
        $entityManager->persist($sweatshirt);

        $cartItem = new CartItem();
        $cartItem->setUser($user);
        $cartItem->setSweatshirt($sweatshirt);
        $cartItem->setQuantity(1);
        $cartItem->setSize('M');
        $entityManager->persist($cartItem);

        $entityManager->flush();

        $this->client->request('GET', '/payment/create-intent');

        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('h1:contains("Finaliser le paiement")');
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }
}