<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 6 — Eventos: congressos, vigílias, retiros, conferências. */
#[ORM\Entity]
#[ORM\Table(name: 'event')]
class Event implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const TYPES = ['congresso' => 'Congresso', 'vigilia' => 'Vigília', 'retiro' => 'Retiro', 'conferencia' => 'Conferência'];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $fee = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $capacity = null;

    /** @var Collection<int, EventRegistration> */
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventRegistration::class, cascade: ['persist', 'remove'])]
    private Collection $registrations;

    public function __construct() { $this->registrations = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $v): static { $this->name = $v; return $this; }
    public function getStartsAt(): ?\DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(\DateTimeImmutable $v): static { $this->startsAt = $v; return $this; }
    public function getEndsAt(): ?\DateTimeImmutable { return $this->endsAt; }
    public function setEndsAt(?\DateTimeImmutable $v): static { $this->endsAt = $v; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $v): static { $this->location = $v; return $this; }
    public function getFee(): ?string { return $this->fee; }
    public function setFee(?string $v): static { $this->fee = $v; return $this; }
    public function getCapacity(): ?int { return $this->capacity; }
    public function setCapacity(?int $v): static { $this->capacity = $v; return $this; }
    /** @return Collection<int, EventRegistration> */
    public function getRegistrations(): Collection { return $this->registrations; }
    public function __toString(): string { return $this->name ?? 'Evento'; }
}
