<?php

namespace App\Entity;

use App\Repository\SweatshirtRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SweatshirtRepository::class)]
class Sweatshirt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $size = [];

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private array $stock = [];

    #[ORM\Column]
    private ?bool $is_featured = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): array
    {
        return $this->size;
    }

    public function setSize(array $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): array
    {
        return $this->stock;
    }

    public function setStock(array $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->is_featured;
    }

    // Accès simplifié pour chaque taille
    public function getStockForSize(string $size): ?int
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        $index = array_search($size, $sizes);

        return $index !== false && isset($this->stock[$index]) ? $this->stock[$index] : null;
    }

    public function setStockForSize(string $size, int $quantity): self
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        $index = array_search($size, $sizes);

        if ($index !== false) {
            $this->stock[$index] = $quantity;
        }

        return $this;
    }

    public function setFeatured(bool $is_featured): static
    {
        $this->is_featured = $is_featured;

        return $this;
    }

    public function __construct()
    {
        $this->size = ['XS', 'S', 'M', 'L', 'XL'];
        $this->stock = [0, 0, 0, 0, 0];
    }
}
