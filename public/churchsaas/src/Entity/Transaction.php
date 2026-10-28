<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MÓDULO 5 — Tesouraria. Lançamento financeiro (entrada/saída),
 * incluindo dízimos, ofertas e contribuições de campanha.
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transaction')]
#[ORM\Index(name: 'idx_tx_date', columns: ['occurred_at'])]
class Transaction implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const IN = 'entrada';
    public const OUT = 'saida';

    public const KIND_DIZIMO = 'dizimo';
    public const KIND_OFERTA = 'oferta';
    public const KIND_CAMPANHA = 'campanha';
    public const KIND_DESPESA = 'despesa';
    public const KIND_OUTRO = 'outro';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $direction = null;

    #[ORM\Column(length: 20)]
    private string $kind = self::KIND_OUTRO;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    #[Assert\Positive(message: 'O valor deve ser maior que zero')]
    private string $amount = '0.00';

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: FinancialCategory::class)]
    private ?FinancialCategory $category = null;

    /** Dizimista/ofertante (opcional — permite relatório por membro). */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    private ?Campaign $campaign = null;

    /** dinheiro, pix, cartao, transferencia, cheque */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getDirection(): ?string { return $this->direction; }
    public function setDirection(string $v): static { $this->direction = $v; return $this; }
    public function getKind(): string { return $this->kind; }
    public function setKind(string $v): static { $this->kind = $v; return $this; }
    public function getAmount(): string { return $this->amount; }
    public function setAmount(string $v): static { $this->amount = $v; return $this; }
    public function getOccurredAt(): ?\DateTimeImmutable { return $this->occurredAt; }
    public function setOccurredAt(\DateTimeImmutable $v): static { $this->occurredAt = $v; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): static { $this->description = $v; return $this; }
    public function getCategory(): ?FinancialCategory { return $this->category; }
    public function setCategory(?FinancialCategory $v): static { $this->category = $v; return $this; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    public function getCampaign(): ?Campaign { return $this->campaign; }
    public function setCampaign(?Campaign $v): static { $this->campaign = $v; return $this; }
    public function getPaymentMethod(): ?string { return $this->paymentMethod; }
    public function setPaymentMethod(?string $v): static { $this->paymentMethod = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isIncome(): bool { return $this->direction === self::IN; }
    public function signedAmount(): float
    {
        return $this->isIncome() ? (float) $this->amount : -(float) $this->amount;
    }
}
