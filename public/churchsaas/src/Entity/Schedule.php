<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Escala de diaconia.
 * Um evento de serviço (culto, Santa Ceia, recepção, estacionamento, limpeza)
 * para o qual diáconos são escalados.
 */
#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
#[ORM\Table(name: 'schedule')]
class Schedule implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const TYPES = [
        'culto'          => 'Culto',
        'santa_ceia'     => 'Santa Ceia',
        'recepcao'       => 'Recepção',
        'estacionamento' => 'Estacionamento',
        'limpeza'        => 'Limpeza',
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, ScheduleAssignment> */
    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: ScheduleAssignment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $assignments;

    /** @var Collection<int, ServiceSlot> Vagas de serviço abertas para auto-inscrição. */
    #[ORM\OneToMany(mappedBy: 'schedule', targetEntity: ServiceSlot::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['activity' => 'ASC'])]
    private Collection $slots;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->slots = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getTypeLabel(): string { return self::TYPES[$this->type] ?? $this->type; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $v): static { $this->title = $v; return $this; }
    public function getScheduledAt(): ?\DateTimeImmutable { return $this->scheduledAt; }
    public function setScheduledAt(\DateTimeImmutable $v): static { $this->scheduledAt = $v; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $v): static { $this->location = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }

    /** @return Collection<int, ScheduleAssignment> */
    public function getAssignments(): Collection { return $this->assignments; }

    public function addAssignment(ScheduleAssignment $a): static
    {
        if (!$this->assignments->contains($a)) {
            $this->assignments->add($a);
            $a->setSchedule($this);
        }
        return $this;
    }

    public function removeAssignment(ScheduleAssignment $a): static
    {
        if ($this->assignments->removeElement($a) && $a->getSchedule() === $this) {
            $a->setSchedule(null);
        }
        return $this;
    }

    public function countPresent(): int
    {
        return $this->assignments->filter(fn(ScheduleAssignment $a) => $a->isPresent())->count();
    }

    /** @return Collection<int, ServiceSlot> */
    public function getSlots(): Collection { return $this->slots; }

    public function addSlot(ServiceSlot $s): static
    {
        if (!$this->slots->contains($s)) {
            $this->slots->add($s);
            $s->setSchedule($this);
        }
        return $this;
    }

    public function removeSlot(ServiceSlot $s): static
    {
        if ($this->slots->removeElement($s) && $s->getSchedule() === $this) {
            $s->setSchedule(null);
        }
        return $this;
    }

    public function countOpenSlots(): int
    {
        return $this->slots->filter(fn(ServiceSlot $s) => $s->isOpen())->count();
    }

    public function countFilledSlots(): int
    {
        return $this->slots->filter(fn(ServiceSlot $s) => $s->isFilled())->count();
    }

    public function __toString(): string { return $this->title ?? 'Escala'; }
}
