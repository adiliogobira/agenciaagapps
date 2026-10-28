<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 4 — Agenda pastoral: visitas domiciliares, aconselhamentos e compromissos. */
#[ORM\Entity]
#[ORM\Table(name: 'pastoral_appointment')]
class PastoralAppointment implements TenantAwareInterface
{
    use TenantAwareTrait;

    /** visita | aconselhamento | reuniao | acompanhamento */
    public const TYPES = ['visita' => 'Visita domiciliar', 'aconselhamento' => 'Aconselhamento', 'reuniao' => 'Reunião', 'acompanhamento' => 'Acompanhamento espiritual'];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $pastor = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $subject = null;

    /** Registro reservado do aconselhamento (confidencial). */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $confidentialNotes = null;

    #[ORM\Column(length: 20, options: ['default' => 'agendado'])]
    private string $status = 'agendado';

    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getScheduledAt(): ?\DateTimeImmutable { return $this->scheduledAt; }
    public function setScheduledAt(\DateTimeImmutable $v): static { $this->scheduledAt = $v; return $this; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    public function getPastor(): ?User { return $this->pastor; }
    public function setPastor(?User $v): static { $this->pastor = $v; return $this; }
    public function getSubject(): ?string { return $this->subject; }
    public function setSubject(?string $v): static { $this->subject = $v; return $this; }
    public function getConfidentialNotes(): ?string { return $this->confidentialNotes; }
    public function setConfidentialNotes(?string $v): static { $this->confidentialNotes = $v; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $v): static { $this->status = $v; return $this; }
}
