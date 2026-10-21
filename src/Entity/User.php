<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'idUser')]
    private ?int $idUser = null;

    #[ORM\Column(type: 'string', length: 8, unique: true, name: 'username')]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true, name: 'fullName')]
    private ?string $fullName = null;

    #[ORM\Column(type: 'string', length: 100, unique: true, name: 'email', nullable: false)]
    private string $email = ''; // Initialize with empty string to prevent NULL

    #[ORM\Column(type: 'string', length: 15, name: 'phoneNumber')]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 255, name: 'password')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', name: 'role', columnDefinition: "ENUM('ARTISAN', 'CLIENT', 'ADMIN')", options: ['default' => 'CLIENT'])]
    private ?string $role = 'CLIENT';

    private ?string $plainPassword = null;

    #[ORM\Column(type: 'datetime', name: 'createdAt', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', name: 'updatedAt', options: ['default' => 'CURRENT_TIMESTAMP', 'onUpdate' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTime $updatedAt = null;
    #[ORM\Column(type: 'string', length: 255, name: 'location', nullable: true)]
    private ?string $location = null;

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function getId(): ?int
    {
        return $this->idUser;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        if (empty(trim($email))) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }
        $this->email = trim($email);
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        // Remove ROLE_ prefix if present for validation
        $roleBase = str_starts_with($role, 'ROLE_') ? substr($role, 5) : $role;
        $validRoles = ['ARTISAN', 'CLIENT', 'ADMIN'];

        if (!in_array(strtoupper($roleBase), $validRoles)) {
            throw new \InvalidArgumentException(sprintf('Invalid role: %s. Must be one of: %s', $role, implode(', ', $validRoles)));
        }

        // Store without ROLE_ prefix to match the enum
        $this->role = strtoupper($roleBase);
        return $this;
    }

    public function getRoles(): array
    {
        // Always include ROLE_USER for all users
        $roles = ['ROLE_USER'];

        // Add the specific role from the role field if set
        if ($this->role) {
            $roles[] = 'ROLE_' . $this->role;
        }

        return array_unique($roles);
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // No sensitive data to erase
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }
}