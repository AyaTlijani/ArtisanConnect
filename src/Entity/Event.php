<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEvent')]
    private ?int $idEvent = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Event name cannot be empty.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Event name cannot be longer than {{ limit }} characters."
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 65535,
        maxMessage: "Description cannot be longer than {{ limit }} characters."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Assert\NotBlank(message: "Date cannot be empty.")]
    private ?string $date = null;

    #[ORM\Column(type: Types::STRING, length: 5, name: 'startTime')]
    #[Assert\NotBlank(message: "Start time cannot be empty.")]
    private ?string $startTime = null;

    #[ORM\Column(type: Types::STRING, length: 5, name: 'endTime', nullable: true)]
    private ?string $endTime = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Capacity cannot be empty.")]
    #[Assert\Positive(message: "Capacity must be a positive number.")]
    private ?int $capacity = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Price cannot be empty.")]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: "Price must be zero or positive."
    )]
    private ?float $price = 0;

    #[ORM\Column(length: 50, options: ['default' => 'Upcoming'])]
    #[Assert\NotBlank(message: "Status cannot be empty.")]
    #[Assert\Choice(
        choices: ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'],
        message: "Please select a valid status: Upcoming, Ongoing, Completed, or Cancelled."
    )]
    private ?string $status = 'Upcoming';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'organizer', referencedColumnName: 'idUser', nullable: false)]
    #[Assert\NotNull(message: "Organizer cannot be empty.")]
    private ?User $organizer = null;

    #[ORM\ManyToOne(targetEntity: Atelier::class)]
    #[ORM\JoinColumn(name: 'atelier', referencedColumnName: 'idAtelier', nullable: true)]
    private ?Atelier $atelier = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'location', referencedColumnName: 'idLocation', nullable: true)]
    private ?Location $location = null;

    /**
     * Custom validation to ensure endTime is after startTime if provided.
     */
    #[Assert\Callback]
    public function validateEndTime(ExecutionContextInterface $context): void
    {
        if ($this->endTime !== null && $this->endTime !== '' && 
            $this->startTime !== null && $this->startTime !== '') {
            // Convert times to minutes for comparison
            list($startHour, $startMin) = explode(':', $this->startTime);
            $startMinutes = (int)$startHour * 60 + (int)$startMin;
            
            list($endHour, $endMin) = explode(':', $this->endTime);
            $endMinutes = (int)$endHour * 60 + (int)$endMin;
            
            if ($endMinutes <= $startMinutes) {
                $context->buildViolation('End time must be after start time.')
                    ->atPath('endTime')
                    ->addViolation();
            }
        }
    }

    public function getIdEvent(): ?int
    {
        return $this->idEvent;
    }

    public function getId(): ?int
    {
        return $this->idEvent;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(string $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;
        return $this;
    }

    public function getAtelier(): ?Atelier
    {
        return $this->atelier;
    }

    public function setAtelier(?Atelier $atelier): static
    {
        $this->atelier = $atelier;
        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;
        return $this;
    }
}