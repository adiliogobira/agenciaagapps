<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Usuário do sistema. Pertence a UMA igreja (tenant) e carrega os perfis
 * de acesso: Admin, Pastor, Secretário, Tesoureiro, Diácono, Líder de Ministério.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank, Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private ?string $fullName = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /** A igreja não é filtrada aqui pois é usada JUSTAMENTE para resolver o tenant. */
    #[ORM\ManyToOne(targetEntity: Church::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Church $church = null;

    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Member $member = null;

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $fullName): static { $this->fullName = $fullName; return $this; }

    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): static { $this->active = $active; return $this; }

    public function getChurch(): ?Church { return $this->church; }
    public function setChurch(?Church $church): static { $this->church = $church; return $this; }

    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $member): static { $this->member = $member; return $this; }

    public function eraseCredentials(): void {}

    public function __toString(): string { return $this->fullName ?? $this->email ?? 'Usuário'; }
}
