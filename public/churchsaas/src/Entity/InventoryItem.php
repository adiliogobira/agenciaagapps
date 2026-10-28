<?php

namespace App\Entity;

use App\Repository\InventoryItemRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO Estoque de Doações — item doado (mantimento, material de limpeza, etc.).
 * Controla apenas NOME e QUANTIDADE, sem qualquer valor monetário.
 */
#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
#[ORM\Table(name: 'inventory_item')]
class InventoryItem implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const CATEGORIES = [
        'mantimento' => 'Mantimento',
        'limpeza'    => 'Material de limpeza',
        'higiene'    => 'Higiene',
        'outro'      => 'Outro',
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 20, options: ['default' => 'mantimento'])]
    private string $category = 'mantimento';

    /** Unidade de medida (un, kg, L, pacote, fardo...). */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $unit = 'un';

    /** Quantidade atual em estoque. */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0'])]
    private string $quantity = '0';

    /** Quantidade mínima desejada (para alerta de estoque baixo). */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $minQuantity = null;

    /** @var Collection<int, InventoryMovement> */
    #[ORM\OneToMany(mappedBy: 'item', targetEntity: InventoryMovement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $movements;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $v): static { $this->name = $v; return $this; }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $v): static { $this->category = $v; return $this; }
    public function getCategoryLabel(): string { return self::CATEGORIES[$this->category] ?? $this->category; }
    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $v): static { $this->unit = $v; return $this; }
    public function getQuantity(): float { return (float) $this->quantity; }
    public function setQuantity(float $v): static { $this->quantity = (string) max(0, $v); return $this; }
    public function getMinQuantity(): ?float { return $this->minQuantity !== null ? (float) $this->minQuantity : null; }
    public function setMinQuantity(?float $v): static { $this->minQuantity = $v !== null ? (string) $v : null; return $this; }

    public function addQuantity(float $amount): static
    {
        return $this->setQuantity($this->getQuantity() + $amount);
    }

    public function isLow(): bool
    {
        return $this->minQuantity !== null && $this->getQuantity() <= (float) $this->minQuantity;
    }

    /** @return Collection<int, InventoryMovement> */
    public function getMovements(): Collection { return $this->movements; }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
