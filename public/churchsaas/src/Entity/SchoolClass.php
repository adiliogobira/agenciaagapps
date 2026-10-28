<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 7 — Turma da Escola Bíblica (EBD). */
#[ORM\Entity]
#[ORM\Table(name: 'school_class')]
class SchoolClass implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    /** Faixa etária / público (ex.: Jovens, Adultos, Infantil). */
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $ageGroup = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $teacher = null;

    /** @var Collection<int, Student> */
    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'classes')]
    private Collection $students;

    public function __construct() { $this->students = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $v): static { $this->name = $v; return $this; }
    public function getAgeGroup(): ?string { return $this->ageGroup; }
    public function setAgeGroup(?string $v): static { $this->ageGroup = $v; return $this; }
    public function getTeacher(): ?Member { return $this->teacher; }
    public function setTeacher(?Member $v): static { $this->teacher = $v; return $this; }
    /** @return Collection<int, Student> */
    public function getStudents(): Collection { return $this->students; }
    public function __toString(): string { return $this->name ?? 'Turma'; }
}
