<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Lugar/papel de alguém na Mesa da Ceia.
 *
 * Papéis possíveis:
 *   - presidente  → preside a ordenança
 *   - oração      → ora pelos elementos (pão e cálice)
 *   - consagração → consagra (distribui) os elementos
 *   - composicao  → compõe a mesa (pastores/missionários convidados)
 */
#[ORM\Entity]
#[ORM\Table(name: 'communion_seat')]
class CommunionSeat implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const ROLES = [
        'presidente'  => 'Presidente da Mesa',
        'oracao'      => 'Oração pelos elementos',
        'consagracao' => 'Consagração / Distribuição',
        'composicao'  => 'Composição da Mesa',
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CommunionTable::class, inversedBy: 'seats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommunionTable $table = null;

    /** Papel desta pessoa na mesa. */
    #[ORM\Column(length: 30)]
    private string $role = 'composicao';

    /**
     * Nome livre: permite convocar missionários/pastores convidados que não
     * estão cadastrados como membros.
     */
    #[ORM\Column(length: 150)]
    private ?string $personName = null;

    /**
     * Vínculo opcional a um membro cadastrado (para notificações).
     * Quando preenchido, o membro é notificado automaticamente.
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Member $member = null;

    /** Observação individual (ex.: "Pr. João vem de Belo Horizonte"). */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int { return $this->id; }
    public function getTable(): ?CommunionTable { return $this->table; }
    public function setTable(?CommunionTable $v): static { $this->table = $v; return $this; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $v): static { $this->role = $v; return $this; }
    public function getRoleLabel(): string { return self::ROLES[$this->role] ?? $this->role; }
    public function getPersonName(): ?string { return $this->personName; }
    public function setPersonName(string $v): static { $this->personName = $v; return $this; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }
}
