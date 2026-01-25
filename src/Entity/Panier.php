<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idPanier')]
    private ?int $idPanier = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idClient', referencedColumnName: 'idUser', nullable: false, onDelete: 'CASCADE')]
    private ?User $idClient = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'idProduit', referencedColumnName: 'idProduit', onDelete: 'CASCADE')]
    private ?Produit $idProduit = null;

    #[ORM\Column(options: ['default' => 1])]
    private int $quantite = 1;

    #[ORM\Column(name: 'prixTotal', type: 'float', nullable: true)]
    private ?float $prixTotal = null;

    #[ORM\Column(name: 'calculerPrix', options: ['default' => 0])]
    private int $calculerPrix = 0;

    public function getIdPanier(): ?int
    {
        return $this->idPanier;
    }

    public function getIdClient(): ?User
    {
        return $this->idClient;
    }

    public function setIdClient(?User $idClient): static
    {
        $this->idClient = $idClient;
        return $this;
    }

    public function getIdProduit(): ?Produit
    {
        return $this->idProduit;
    }

    public function setIdProduit(?Produit $idProduit): static
    {
        $this->idProduit = $idProduit;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(?float $prixTotal): static
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }

    public function getCalculerPrix(): int
    {
        return $this->calculerPrix;
    }

    public function setCalculerPrix(int $calculerPrix): static
    {
        $this->calculerPrix = $calculerPrix;
        return $this;
    }
}