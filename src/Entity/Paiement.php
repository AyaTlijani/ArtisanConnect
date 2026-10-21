<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiement')]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idPaiement = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idClient', referencedColumnName: 'idUser', nullable: false)]
    #[Assert\NotNull(message: 'Client cannot be empty.')]
    private ?User $client = null;

    #[ORM\ManyToOne(targetEntity: Orders::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(name: 'idCommande', referencedColumnName: 'order_id', nullable: false)]
    #[Assert\NotBlank(message: 'Order cannot be empty.')]
    private ?Orders $order = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Amount cannot be zero or negative.')]
    private ?float $montant = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Status cannot be empty.')]
    private ?string $statut = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: 'Payment date cannot be empty.')]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $paymentStatus = 'pending';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentId = null;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $transactionId = null;
    
    // Kept for backward compatibility
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $methode = null;
    
    // Getters and setters for new fields
    
    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }
    
    public function setPaymentStatus(?string $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }
    
    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }
    
    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $remboursement = false;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $reservation = null;

    public function getIdPaiement(): ?int
    {
        return $this->idPaiement;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;
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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
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

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        // For backward compatibility
        $this->methode = $paymentMethod;
        return $this;
    }

    public function getMethode(): ?string
    {
        return $this->methode;
    }

    public function setMethode(?string $methode): self
    {
        $this->methode = $methode;
        return $this;
    }

    public function getNumeroTransaction(): ?string
    {
        return $this->transactionId;
    }

    public function setNumeroTransaction(?string $transactionId): self
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getRemboursement(): bool
    {
        return $this->remboursement;
    }

    public function setRemboursement(bool $remboursement): self
    {
        $this->remboursement = $remboursement;
        return $this;
    }

    public function getReservation(): ?string
    {
        return $this->reservation;
    }

    public function setReservation(?string $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }
}