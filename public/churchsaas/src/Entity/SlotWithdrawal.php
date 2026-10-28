<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Registro de desmarcação de uma vaga de serviço.
 *
 * Quando um diácono sai de uma escala que havia aceitado, guarda-se aqui quem
 * saiu e o motivo, para o líder/pastor acompanhar. Mantemos o nome do diácono
 * em texto (snapshot) para preservar o histórico mesmo que a vaga seja
 * reaproveitada por outra pessoa.
 */
#[ORM\Entity]
#[ORM\Table(name: 'slot_withdrawal')]
class SlotWithdrawal implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceSlot::class, inversedBy: 'withdrawals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceSlot $slot = null;

    /** Diácono que se desmarcou (pode ficar nulo se o cadastro for removido). */
    #[ORM\ManyToOne(targetEntity: Deacon::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Deacon $deacon = null;

    /** Snapshot do nome, preservado para o histórico. */
    #[ORM\Column(length: 150)]
    private ?string $deaconName = null;

    #[ORM\Column(type: 'text')]
    private ?string $reason = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getSlot(): ?ServiceSlot { return $this->slot; }
    public function setSlot(?ServiceSlot $v): static { $this->slot = $v; return $this; }
    public function getDeacon(): ?Deacon { return $this->deacon; }
    public function setDeacon(?Deacon $v): static { $this->deacon = $v; return $this; }
    public function getDeaconName(): ?string { return $this->deaconName; }
    public function setDeaconName(string $v): static { $this->deaconName = $v; return $this; }
    public function getReason(): ?string { return $this->reason; }
    public function setReason(string $v): static { $this->reason = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
