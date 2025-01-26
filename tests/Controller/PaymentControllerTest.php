<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Sweatshirt;
use App\Entity\CartItem;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PaymentControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

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

    public function testPaymentSuccess(): void
    {
        // Ajouter un utilisateur et un article au panier
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('test@gmail.com');

        $this->client->loginUser($user);

        // Ajouter un sweatshirt au panier
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

        $this->client->request('GET', '/payment/success');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('h1:contains("Merci pour votre achat !")');

        $cartItems = $entityManager->getRepository(CartItem::class)->findBy(['user' => $user]);
        $this->assertCount(0, $cartItems);

        $updatedSweatshirt = $entityManager->getRepository(Sweatshirt::class)->find($sweatshirt->getId());
        $this->assertEquals(9, $updatedSweatshirt->getStock()[2]);
    }

    public function testPaymentFailure(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('test@gmail.com');

        $this->client->loginUser($user);

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

        $this->client->request('GET', '/payment/failure');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('h1:contains("Le paiement a échoué")');

        $cartItems = $entityManager->getRepository(CartItem::class)->findBy(['user' => $user]);
        $this->assertCount(1, $cartItems);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }
}
