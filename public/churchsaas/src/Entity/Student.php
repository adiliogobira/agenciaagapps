<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 7 — Aluno da Escola Bíblica. */
#[ORM\Entity]
#[ORM\Table(name: 'student')]
class Student implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $fullName = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $member = null;

    /** @var Collection<int, SchoolClass> */
    #[ORM\ManyToMany(targetEntity: SchoolClass::class, inversedBy: 'students')]
    #[ORM\JoinTable(name: 'student_class')]
    private Collection $classes;

    public function __construct() { $this->classes = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $v): static { $this->fullName = $v; return $this; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    /** @return Collection<int, SchoolClass> */
    public function getClasses(): Collection { return $this->classes; }
    public function addClass(SchoolClass $c): static { if (!$this->classes->contains($c)) { $this->classes->add($c); } return $this; }
    public function __toString(): string { return $this->fullName ?? 'Aluno'; }
}
