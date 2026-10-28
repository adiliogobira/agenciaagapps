<?php

namespace App\Entity;

use App\Repository\FinancialCategoryRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/** Categoria de lançamento financeiro (ex.: Dízimos, Ofertas, Luz, Água). */
#[ORM\Entity(repositoryClass: FinancialCategoryRepository::class)]
#[ORM\Table(name: 'financial_category')]
class FinancialCategory implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /** entrada | saida */
    #[ORM\Column(length: 10)]
    private ?string $direction = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $v): static { $this->name = $v; return $this; }
    public function getDirection(): ?string { return $this->direction; }
    public function setDirection(string $v): static { $this->direction = $v; return $this; }
    public function __toString(): string { return $this->name ?? 'Categoria'; }
}
