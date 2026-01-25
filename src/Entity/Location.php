<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idLocation')]
    private ?int $idLocation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    // Getters and setters
    public function getIdLocation(): ?int
    {
        return $this->idLocation;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }
}