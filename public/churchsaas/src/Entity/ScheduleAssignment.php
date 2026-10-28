<?php

namespace App\Entity;

use App\Repository\ScheduleAssignmentRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Vínculo Diácono ↔ Escala, com posição e controle de presença.
 * Serve também de histórico de serviços prestados por diácono.
 */
#[ORM\Entity(repositoryClass: ScheduleAssignmentRepository::class)]
#[ORM\Table(name: 'schedule_assignment')]
class ScheduleAssignment implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const PRESENCE_ESCALADO  = 'escalado';
    public const PRESENCE_CONFIRMADO = 'confirmado';
    public const PRESENCE_PRESENTE  = 'presente';
    public const PRESENCE_AUSENTE   = 'ausente';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Schedule::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: Deacon::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Deacon $deacon = null;

    /** Posto/função na escala (ex.: "porta principal", "berçário", "ceia"). */
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 20, options: ['default' => self::PRESENCE_ESCALADO])]
    private string $presence = self::PRESENCE_ESCALADO;

    public function getId(): ?int { return $this->id; }
    public function getSchedule(): ?Schedule { return $this->schedule; }
    public function setSchedule(?Schedule $v): static { $this->schedule = $v; return $this; }
    public function getDeacon(): ?Deacon { return $this->deacon; }
    public function setDeacon(?Deacon $v): static { $this->deacon = $v; return $this; }
    public function getPosition(): ?string { return $this->position; }
    public function setPosition(?string $v): static { $this->position = $v; return $this; }
    public function getPresence(): string { return $this->presence; }
    public function setPresence(string $v): static { $this->presence = $v; return $this; }
    public function isPresent(): bool { return $this->presence === self::PRESENCE_PRESENTE; }
    public function __toString(): string { return (string) $this->deacon; }
}
