<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notificação/alerta destinada a um usuário específico. Aparece na barra do topo
 * (sino) apenas para o destinatário.
 */
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
#[ORM\Index(name: 'idx_notif_user', columns: ['user_id', 'is_read'])]
class Notification implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    /** Destinatário. */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $message = null;

    /** Ícone Bootstrap (sem o prefixo "bi-"). */
    #[ORM\Column(length: 40, options: ['default' => 'bell'])]
    private string $icon = 'bell';

    /** Link opcional para onde o usuário é levado ao clicar. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(name: 'is_read', options: ['default' => false])]
    private bool $read = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $v): static { $this->user = $v; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $v): static { $this->title = $v; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $v): static { $this->message = $v; return $this; }
    public function getIcon(): string { return $this->icon; }
    public function setIcon(string $v): static { $this->icon = $v; return $this; }
    public function getLink(): ?string { return $this->link; }
    public function setLink(?string $v): static { $this->link = $v; return $this; }
    public function isRead(): bool { return $this->read; }
    public function setRead(bool $v): static { $this->read = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
