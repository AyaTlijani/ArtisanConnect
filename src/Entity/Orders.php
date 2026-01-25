<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ORM\Table(name: 'orders')]
class Orders
{
    final public const STATUS_PENDING = 'PENDING';
    final public const STATUS_PAID = 'PAID';
    final public const STATUS_SHIPPED = 'SHIPPED';
    final public const STATUS_DELIVERED = 'DELIVERED';
    final public const STATUS_CANCELLED = 'CANCELLED';

    #[ORM\Id]
    #[ORM\Column(name: 'order_id', type: 'string', length: 20)]
    private ?string $order_id = null;

    #[ORM\Column(name: 'total_price', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Total price must be zero or positive.')]
    private ?float $total_price = null;

    #[ORM\Column(name: 'status', type: 'string', length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Status cannot be empty.')]
    #[Assert\Choice(
        choices: [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ],
        message: 'Please select a valid status: PENDING, PAID, SHIPPED, DELIVERED, or CANCELLED.'
    )]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'full_name', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: 'Full name cannot be longer than {{ limit }} characters.')]
    private ?string $full_name = null;

    #[ORM\Column(name: 'address', type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(name: 'email', type: 'string', length: 100, nullable: true)]
    #[Assert\Email(message: 'Please enter a valid email address.')]
    private ?string $email = null;

    #[ORM\Column(name: 'phone', type: 'string', length: 15, nullable: true)]
    #[Assert\Length(max: 15, maxMessage: 'Phone number cannot be longer than {{ limit }} characters.')]
    private ?string $phone = null;

    #[ORM\Column(name: 'payment_method', type: 'string', length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: 'Payment method cannot be longer than {{ limit }} characters.')]
    private ?string $payment_method = null;

    #[ORM\Column(name: 'delivery_date', type: 'date', nullable: true)]
    #[Assert\Date(message: 'Please enter a valid delivery date.')]
    private ?\DateTimeInterface $delivery_date = null;

    #[ORM\Column(name: 'idClient', type: 'integer')]
    private int $idClient;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'payment_status', type: 'string', length: 20, nullable: true)]
    private ?string $payment_status = 'pending';

    #[ORM\Column(name: 'stripe_payment_id', type: 'string', length: 100, nullable: true)]
    private ?string $stripe_payment_id = null;

    #[ORM\Column(name: 'tracking_number', type: 'string', length: 100, nullable: true)]
    private ?string $tracking_number = null;

    #[ORM\Column(name: 'carrier', type: 'string', length: 50, nullable: true)]
    private ?string $carrier = null;

    #[ORM\Column(name: 'estimated_delivery_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $estimated_delivery_date = null;

    #[ORM\Column(name: 'shipped_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $shipped_at = null;

    #[ORM\Column(name: 'delivered_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $delivered_at = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'idClient', referencedColumnName: 'idUser', nullable: false)]
    #[Assert\NotNull(message: 'Client cannot be empty.')]
    private ?User $client = null;

    /**
     * @var Collection<int, OrderItems>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItems::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Paiement::class, cascade: ['persist', 'remove'])]
    private Collection $paiements;

    #[ORM\OneToOne(mappedBy: 'order', targetEntity: Commande::class, cascade: ['persist', 'remove'])]
    private ?Commande $commande = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->order_id === null) {
            $this->order_id = 'CMD-' . time() . random_int(1000, 9999);
        }
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
        if ($this->status === null) {
            $this->status = self::STATUS_PENDING;
        }
    }

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->status = self::STATUS_PENDING;
        $this->createdAt = new \DateTime();
    }

    /**
     * Alias for getOrderId() for compatibility with Symfony forms and other components
     * that expect an 'id' field.**/
    public function getId(): ?string
    {
        return $this->getOrderId();
    }

    public function getOrderId(): ?string
    {
        return $this->order_id;
    }

    public function setOrderId(string $order_id): self
    {
        $this->order_id = $order_id;
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->total_price;
    }

    public function setTotalPrice(?float $total_price): self
    {
        $this->total_price = $total_price;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(?string $full_name): self
    {
        $this->full_name = $full_name;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }
    
    // Alias for setAddress for compatibility
    public function setShippingAddress(?string $address): self
    {
        return $this->setAddress($address);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->payment_method;
    }

    public function setPaymentMethod(?string $payment_method): static
    {
        $this->payment_method = $payment_method;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(?string $payment_status): static
    {
        $this->payment_status = $payment_status;
        return $this;
    }

    public function getStripePaymentId(): ?string
    {
        return $this->stripe_payment_id;
    }

    public function setStripePaymentId(?string $stripe_payment_id): self
    {
        $this->stripe_payment_id = $stripe_payment_id;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->tracking_number;
    }

    public function setTrackingNumber(?string $tracking_number): self
    {
        $this->tracking_number = $tracking_number;
        return $this;
    }

    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    public function setCarrier(?string $carrier): self
    {
        $this->carrier = $carrier;
        return $this;
    }

    public function getEstimatedDeliveryDate(): ?\DateTimeInterface
    {
        return $this->estimated_delivery_date;
    }

    public function setEstimatedDeliveryDate(?\DateTimeInterface $estimated_delivery_date): self
    {
        $this->estimated_delivery_date = $estimated_delivery_date;
        return $this;
    }

    public function getShippedAt(): ?\DateTimeInterface
    {
        return $this->shipped_at;
    }

    public function setShippedAt(?\DateTimeInterface $shipped_at): self
    {
        $this->shipped_at = $shipped_at;
        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->delivered_at;
    }

    public function setDeliveredAt(?\DateTimeInterface $delivered_at): self
    {
        $this->delivered_at = $delivered_at;
        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->delivery_date;
    }

    public function setDeliveryDate(?\DateTimeInterface $delivery_date): self
    {
        $this->delivery_date = $delivery_date;
        return $this;
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

    /**
     * @return Collection<int, OrderItems>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItems $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItems $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): self
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements[] = $paiement;
            $paiement->setOrder($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): self
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getOrder() === $this) {
                $paiement->setOrder(null);
            }
        }

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        if ($commande === null && $this->commande !== null) {
            $this->commande->setOrder(null);
        }

        if ($commande !== null && $commande->getOrder() !== $this) {
            $commande->setOrder($this);
        }

        $this->commande = $commande;
        return $this;
    }

    public function getItemsByArtisan(User $artisan): Collection
    {
        return $this->items->filter(function (OrderItems $item) use ($artisan) {
            return $item->getArtisan() && $item->getArtisan()->getId() === $artisan->getId();
        });
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

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item->getPrix() * $item->getQuantite();
        }
        return $total;
    }

    public function getTotalForArtisan(User $artisan): float
    {
        $total = 0;
        foreach ($this->getItemsByArtisan($artisan) as $item) {
            $total += $item->getPrix() * $item->getQuantite();
        }
        return $total;
    }
}