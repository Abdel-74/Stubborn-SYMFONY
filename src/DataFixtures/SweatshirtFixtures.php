<?php

namespace App\DataFixtures;

use App\Entity\Sweatshirt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SweatshirtFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des sweatshirts à ajouter
        $sweatshirts = [
            ['name' => 'Blackbelt', 'price' => 29.90, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [1, 2, 5, 2, 2], 'is_featured' => true],
            ['name' => 'BlueBelt', 'price' => 29.90, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [2, 1, 11, 13, 3], 'is_featured' => false],
            ['name' => 'Street', 'price' => 34.50, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [10, 1, 6, 1, 4], 'is_featured' => false],
            ['name' => 'Pokeball', 'price' => 45.00, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [1, 3, 2, 26, 1], 'is_featured' => true],
            ['name' => 'PinkLady', 'price' => 29.90, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [1, 6, 5, 2, 16], 'is_featured' => false],
            ['name' => 'Snow', 'price' => 32.00, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [12, 71, 33, 21, 7], 'is_featured' => false],
            ['name' => 'Greyback', 'price' => 28.50, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [1, 6, 1, 18, 56], 'is_featured' => false],
            ['name' => 'BlueCloud', 'price' => 45.00, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [9, 46, 91, 12, 3], 'is_featured' => false],
            ['name' => 'BornInUsa', 'price' => 59.90, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [7, 3, 3, 4, 1], 'is_featured' => true],
            ['name' => 'GreenSchool', 'price' => 42.20, 'size' => ['XS', 'S', 'M', 'L', 'XL'], 'stock' => [10, 1, 1, 14, 44], 'is_featured' => false],
        ];

        foreach ($sweatshirts as $data) {
            $sweatshirt = new Sweatshirt();
            $sweatshirt->setName($data['name']);
            $sweatshirt->setPrice($data['price']);
            $sweatshirt->setSize($data['size']);
            $sweatshirt->setStock($data['stock']); 
            $sweatshirt->setFeatured($data['is_featured']);

            $manager->persist($sweatshirt);
        }

        $manager->flush();
    }
}

