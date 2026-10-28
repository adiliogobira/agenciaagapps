<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 7 — Frequência do aluno em uma aula da EBD. */
#[ORM\Entity]
#[ORM\Table(name: 'class_attendance')]
class ClassAttendance implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SchoolClass::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?SchoolClass $schoolClass = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $present = true;

    public function getId(): ?int { return $this->id; }
    public function getSchoolClass(): ?SchoolClass { return $this->schoolClass; }
    public function setSchoolClass(?SchoolClass $v): static { $this->schoolClass = $v; return $this; }
    public function getStudent(): ?Student { return $this->student; }
    public function setStudent(?Student $v): static { $this->student = $v; return $this; }
    public function getDate(): ?\DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $v): static { $this->date = $v; return $this; }
    public function isPresent(): bool { return $this->present; }
    public function setPresent(bool $v): static { $this->present = $v; return $this; }
}
