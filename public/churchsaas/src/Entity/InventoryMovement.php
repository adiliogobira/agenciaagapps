<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO Estoque de Doações — movimentação (entrada por doação, saída por uso).
 * Sem valores: apenas quantidade, doador (opcional) e observação.
 */
#[ORM\Entity]
#[ORM\Table(name: 'inventory_movement')]
class InventoryMovement implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const IN = 'entrada';
    public const OUT = 'saida';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InventoryItem::class, inversedBy: 'movements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InventoryItem $item = null;

    #[ORM\Column(length: 10)]
    private string $direction = self::IN;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $quantity = '0';

    /** Quem doou (opcional, para entradas). */
    #[ORM\Column(length: 120, nullable: true)]
    private ?string $donor = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $notes = null;

    /** Quem registrou (opcional). */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $registeredBy = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getItem(): ?InventoryItem { return $this->item; }
    public function setItem(?InventoryItem $v): static { $this->item = $v; return $this; }
    public function getDirection(): string { return $this->direction; }
    public function setDirection(string $v): static { $this->direction = $v; return $this; }
    public function isIncoming(): bool { return $this->direction === self::IN; }
    public function getQuantity(): float { return (float) $this->quantity; }
    public function setQuantity(float $v): static { $this->quantity = (string) $v; return $this; }
    public function getDonor(): ?string { return $this->donor; }
    public function setDonor(?string $v): static { $this->donor = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }
    public function getRegisteredBy(): ?User { return $this->registeredBy; }
    public function setRegisteredBy(?User $v): static { $this->registeredBy = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
