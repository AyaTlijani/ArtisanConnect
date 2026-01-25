<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Rating;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
#[Vich\Uploadable]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idProduit')]
    private ?int $idProduit = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "The product name is required")]
    #[Assert\Length(max: 100, maxMessage: "The name cannot be longer than {{ limit }} characters")]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 65535, maxMessage: "The description cannot be longer than {{ limit }} characters")]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "The price is required")]
    #[Assert\PositiveOrZero(message: "The price must be a positive number or zero")]
    #[Assert\LessThanOrEqual(1000000, message: "The price cannot exceed {{ limit }}")]
    private ?float $prix = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $disponibilite = true;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idArtisan', referencedColumnName: 'idUser', onDelete: 'CASCADE', nullable: true)]
    private ?User $idArtisan = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "The category cannot be longer than {{ limit }} characters")]
    #[Assert\Choice(
        choices: [
            'Clothing & Textiles',
            'Home Décor',
            'Jewelry & Fashion Accessories',
            'Handicrafts & Pottery',
            'Beauty & Wellness',
            'Gifts & Souvenirs'
        ],
        message: "Please select a valid category"
    )]
    private ?string $categorie = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\NotBlank(message: "The quantity is required")]
    #[Assert\PositiveOrZero(message: "The quantity must be a positive number or zero")]
    private int $quantite = 0;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "The image filename cannot be longer than {{ limit }} characters")]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'produit_image', fileNameProperty: 'image')]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Please upload a valid image (JPEG, PNG, or GIF)"
    )]
    private ?File $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: Rating::class, orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $averageRating = null;

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function isDisponibilite(): bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getIdArtisan(): ?User
    {
        return $this->idArtisan;
    }

    public function setIdArtisan(?User $idArtisan): static
    {
        $this->idArtisan = $idArtisan;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }



    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setProduit($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getProduit() === $this) {
                $rating->setProduit(null);
            }
        }

        return $this;
    }

    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    public function setAverageRating(?float $averageRating): self
    {
        $this->averageRating = $averageRating;
        return $this;
    }

    public function calculateAverageRating(): void
    {
        if ($this->ratings->isEmpty()) {
            $this->averageRating = null;
            return;
        }

        $sum = 0;
        foreach ($this->ratings as $rating) {
            $sum += $rating->getRating();
        }

        $this->averageRating = round($sum / $this->ratings->count(), 1);
    }
}