<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Commande ID cannot be empty.')]
    private ?string $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Client name cannot be empty.')]
    private ?string $client = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    private ?string $email = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Address cannot be empty.')]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank(message: 'Phone number cannot be empty.')]
    #[Assert\Length(max: 20, maxMessage: 'Phone number cannot be longer than {{ limit }} characters.')]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Status cannot be empty.')]
    private ?string $statut = null;

    #[ORM\ManyToOne(targetEntity: Livreur::class)]
    #[ORM\JoinColumn(name: 'livreur_id', referencedColumnName: 'id', nullable: true)]
    private ?Livreur $livreur = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'commande', targetEntity: Orders::class)]
    #[ORM\JoinColumn(name: 'id', referencedColumnName: 'order_id', nullable: false)]
    private ?Orders $order = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }

    public function setLivreur(?Livreur $livreur): self
    {
        $this->livreur = $livreur;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
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
}