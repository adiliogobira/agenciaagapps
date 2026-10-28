<?php

namespace App\Entity;

use App\Repository\DeaconRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Diaconia. Cadastro de diáconos (vinculado a um Membro).
 */
#[ORM\Entity(repositoryClass: DeaconRepository::class)]
#[ORM\Table(name: 'deacon')]
class Deacon implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $ordinationDate = null;

    /** Áreas de atuação preferenciais. @var string[] */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $areas = [];

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /** Marca o diácono como líder do diaconato (pode montar/gerir escalas). */
    #[ORM\Column(options: ['default' => false])]
    private bool $leader = false;

    /** @var Collection<int, ScheduleAssignment> */
    #[ORM\OneToMany(mappedBy: 'deacon', targetEntity: ScheduleAssignment::class)]
    private Collection $assignments;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    public function getOrdinationDate(): ?\DateTimeImmutable { return $this->ordinationDate; }
    public function setOrdinationDate(?\DateTimeImmutable $v): static { $this->ordinationDate = $v; return $this; }
    public function getAreas(): ?array { return $this->areas; }
    public function setAreas(?array $v): static { $this->areas = $v; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $v): static { $this->active = $v; return $this; }
    public function isLeader(): bool { return $this->leader; }
    public function setLeader(bool $v): static { $this->leader = $v; return $this; }

    /** @return Collection<int, ScheduleAssignment> */
    public function getAssignments(): Collection { return $this->assignments; }

    public function getName(): string { return (string) $this->member; }
    public function __toString(): string { return 'Diác. '.$this->member; }
}
