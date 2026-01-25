<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
class GoogleUtilisateur implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $adresseMail = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nomUtilisateur = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $googleIdentifiant = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $typeUtilisateur = null;

    #[ORM\Column(type: 'boolean')]
    private bool $termsAccepted = false;

    #[ORM\Column(type: 'boolean')]
    private bool $newsletterSubscribed = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\PrePersist]
    public function setDateCreationValue(): void
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseMail(): ?string
    {
        return $this->adresseMail;
    }

    public function setAdresseMail(string $adresseMail): self
    {
        $this->adresseMail = $adresseMail;
        return $this;
    }

    public function getNomUtilisateur(): ?string
    {
        return $this->nomUtilisateur;
    }

    public function setNomUtilisateur(string $nomUtilisateur): self
    {
        $this->nomUtilisateur = $nomUtilisateur;
        return $this;
    }

    public function getGoogleIdentifiant(): ?string
    {
        return $this->googleIdentifiant;
    }

    public function setGoogleIdentifiant(?string $googleIdentifiant): self
    {
        $this->googleIdentifiant = $googleIdentifiant;
        return $this;
    }

    public function getTypeUtilisateur(): ?string
    {
        return $this->typeUtilisateur;
    }

    public function setTypeUtilisateur(?string $typeUtilisateur): self
    {
        $this->typeUtilisateur = $typeUtilisateur;
        return $this;
    }

    public function isTermsAccepted(): bool
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted(bool $termsAccepted): self
    {
        $this->termsAccepted = $termsAccepted;
        return $this;
    }

    public function isNewsletterSubscribed(): bool
    {
        return $this->newsletterSubscribed;
    }

    public function setNewsletterSubscribed(bool $newsletterSubscribed): self
    {
        $this->newsletterSubscribed = $newsletterSubscribed;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->adresseMail;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // No sensitive data to erase
    }

    public function getPassword(): ?string
    {
        return null; // Google users don't have a password
    }

    public function getSalt(): ?string
    {
        return null;
    }
}