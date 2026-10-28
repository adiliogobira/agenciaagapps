<?php

namespace App\Entity;

use App\Repository\MinistryRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** Ministério / departamento da igreja (Louvor, Infantil, Diaconia, etc.). */
#[ORM\Entity(repositoryClass: MinistryRepository::class)]
#[ORM\Table(name: 'ministry')]
class Ministry implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /**
     * Membros que participam deste ministério (lado inverso).
     * @var Collection<int, Member>
     */
    #[ORM\ManyToMany(targetEntity: Member::class, mappedBy: 'ministries')]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $leader = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }
    public function getLeader(): ?Member { return $this->leader; }
    public function setLeader(?Member $l): static { $this->leader = $l; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $v): static { $this->active = $v; return $this; }

    /** @return Collection<int, Member> */
    public function getMembers(): Collection { return $this->members; }

    public function __toString(): string { return $this->name ?? 'Ministério'; }
}
