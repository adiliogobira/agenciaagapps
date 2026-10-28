<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Escala da Mesa da Santa Ceia, elaborada pelo pastor.
 *
 * O pastor convoca quem compõe a mesa (missionários, pastores convidados),
 * define quem ora pelos elementos e quem os consagra.
 * Os membros da mesa recebem notificação automática.
 */
#[ORM\Entity]
#[ORM\Table(name: 'communion_table')]
class CommunionTable implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    /** Escala de Diaconia à qual esta mesa pertence (tipo santa_ceia). */
    #[ORM\OneToOne(targetEntity: Schedule::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    /** Observações gerais para a mesa (tema, liturgia, etc.). */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, CommunionSeat> */
    #[ORM\OneToMany(mappedBy: 'table', targetEntity: CommunionSeat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['role' => 'ASC'])]
    private Collection $seats;

    public function __construct()
    {
        $this->seats = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getSchedule(): ?Schedule { return $this->schedule; }
    public function setSchedule(?Schedule $v): static { $this->schedule = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }

    /** @return Collection<int, CommunionSeat> */
    public function getSeats(): Collection { return $this->seats; }

    public function addSeat(CommunionSeat $s): static
    {
        if (!$this->seats->contains($s)) {
            $this->seats->add($s);
            $s->setTable($this);
        }
        return $this;
    }

    public function removeSeat(CommunionSeat $s): static
    {
        if ($this->seats->removeElement($s) && $s->getTable() === $this) {
            $s->setTable(null);
        }
        return $this;
    }
}
