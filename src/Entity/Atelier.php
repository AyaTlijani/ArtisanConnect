<?php

namespace App\Entity;

use App\Repository\AtelierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AtelierRepository::class)]
#[ORM\Table(name: 'atelier')]
#[Vich\Uploadable]
class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idAtelier')]
    private ?int $id = null;

    public function getIdAtelier(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'atelier est obligatoire")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le nom doit contenir au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $nom = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(min: 10, minMessage: "La description doit contenir au moins {{ limit }} caractères")]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix est obligatoire")]
    #[Assert\Positive(message: "Le prix doit être positif")]
    private ?float $prix = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: "La quantité est obligatoire")]
    #[Assert\PositiveOrZero(message: "La quantité doit être positive ou nulle")]
    private ?int $quantite = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private ?bool $disponibilite = true;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: "La gouvernorat est obligatoire")]
    #[Assert\Choice([
        'Ariana', 'Béja', 'Ben Arous', 'Bizerte', 'Gabès', 'Gafsa', 'Jendouba', 'Kairouan',
        'Kasserine', 'Kébili', 'Le Kef', 'Mahdia', 'La Manouba', 'Médenine', 'Monastir',
        'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana', 'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'
    ], message: "Veuillez sélectionner une gouvernorat valide")]
    private ?string $location = null;

    #[ORM\OneToMany(mappedBy: 'atelier', targetEntity: Rating::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ratings;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $averageRating = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'fournisseur', referencedColumnName: 'idUser', nullable: false)]
    #[Assert\NotNull(message: "Le fournisseur est obligatoire")]
    private ?User $fournisseur = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire")]
    #[Assert\Length(max: 50, maxMessage: "La catégorie ne peut pas dépasser {{ limit }} caractères")]
    private ?string $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'atelier_image', fileNameProperty: 'image')]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPEG, PNG ou GIF)",
        maxSizeMessage: "L'image ne peut pas dépasser {{ limit }}"
    )]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
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

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): static
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getFournisseur(): ?User
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?User $fournisseur): static
    {
        $this->fournisseur = $fournisseur;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setAtelier($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getAtelier() === $this) {
                $rating->setAtelier(null);
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
        $total = 0;
        $count = $this->ratings->count();

        if ($count === 0) {
            $this->averageRating = null;
            return;
        }

        foreach ($this->ratings as $rating) {
            $total += $rating->getRating();
        }

        $this->averageRating = round($total / $count, 1);
    }
}