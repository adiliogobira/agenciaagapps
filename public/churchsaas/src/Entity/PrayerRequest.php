<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 4 — Pedidos de oração. */
#[ORM\Entity]
#[ORM\Table(name: 'prayer_request')]
class PrayerRequest implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $requesterName = null;

    #[ORM\Column(type: 'text')]
    private ?string $request = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $confidential = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $answered = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getRequesterName(): ?string { return $this->requesterName; }
    public function setRequesterName(string $v): static { $this->requesterName = $v; return $this; }
    public function getRequest(): ?string { return $this->request; }
    public function setRequest(string $v): static { $this->request = $v; return $this; }
    public function isConfidential(): bool { return $this->confidential; }
    public function setConfidential(bool $v): static { $this->confidential = $v; return $this; }
    public function isAnswered(): bool { return $this->answered; }
    public function setAnswered(bool $v): static { $this->answered = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
