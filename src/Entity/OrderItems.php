<?php

namespace App\Entity;

use App\Repository\OrderItemsRepository;
use App\Entity\Orders;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'idItem')] // Already corrected to match 'idItem'
    private ?int $idItem = null;

    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'order_id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Order cannot be empty.')]
    private ?Orders $order = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'idProduit', referencedColumnName: 'idProduit', nullable: false)]
    #[Assert\NotNull(message: 'Product cannot be empty.')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idArtisan', referencedColumnName: 'idUser', nullable: false)]
    #[Assert\NotNull(message: 'Artisan cannot be empty.')]
    private ?User $artisan = null;

    #[ORM\Column(type: 'string', length: 100, name: 'nomProduit')] // Explicitly map to 'nomProduit'
    #[Assert\NotBlank(message: 'Product name cannot be empty.')]
    #[Assert\Length(max: 100, maxMessage: 'Product name cannot be longer than {{ limit }} characters.')]
    private ?string $nomProduit = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Price cannot be empty.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Price must be zero or positive.')]
    private ?float $prix = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'Quantity cannot be empty.')]
    #[Assert\Positive(message: 'Quantity must be a positive number.')]
    private ?int $quantite = null;

    public function getIdItem(): ?int
    {
        return $this->idItem;
    }

    public function setIdItem(int $idItem): self
    {
        $this->idItem = $idItem;
        return $this;
    }

    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    public function setOrder(?Orders $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        if ($produit) {
            $this->setIdProduit($produit->getIdProduit());
        }
        return $this;
    }
    
    public function setIdProduit(int $idProduit): self
    {
        if (!$this->produit) {
            // Instead of creating a new Produit, we'll let the repository handle the association
            // The actual Produit should be set via setProduit()
            throw new \LogicException('Cannot set product ID directly. Use setProduit() instead.');
        }
        // If we have a product, set its ID directly (assuming the entity has a setId method)
        if (method_exists($this->produit, 'setId')) {
            $this->produit->setId($idProduit);
        }
        return $this;
    }

    public function getArtisan(): ?User
    {
        return $this->artisan;
    }

    public function setArtisan(?User $artisan): self
    {
        $this->artisan = $artisan;
        return $this;
    }

    public function getNomProduit(): ?string
    {
        return $this->nomProduit;
    }

    public function setNomProduit(string $nomProduit): self
    {
        $this->nomProduit = $nomProduit;
        return $this;
    }
    
    public function getPrixUnitaire(): ?float
    {
        return $this->prix;
    }
    
    public function setPrixUnitaire(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }
    
    public function getPrixTotal(): ?float
    {
        return $this->prix * $this->quantite;
    }
    
    public function setPrixTotal(float $prixTotal): self
    {
        if ($this->quantite > 0) {
            $this->prix = $prixTotal / $this->quantite;
        }
        return $this;
    }
    
    // Alias for compatibility
    public function getPrix(): ?float
    {
        return $this->prix;
    }
    
    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getTotal(): float
    {
        return $this->prix * $this->quantite;
    }
}