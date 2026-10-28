<?php

namespace App\Entity;

use App\Repository\ServiceSlotRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 3 — Vaga de serviço dentro de uma escala.
 *
 * O líder do diaconato ou o pastor "monta a escala de servir" criando vagas
 * (água, portaria, recepção, limpeza, som...). Cada vaga pode ser aceita por
 * um diácono (auto-inscrição). O diácono também pode se desmarcar informando
 * o motivo — nesse caso a vaga volta a ficar aberta e a saída fica registrada
 * em SlotWithdrawal para o líder acompanhar.
 */
#[ORM\Entity(repositoryClass: ServiceSlotRepository::class)]
#[ORM\Table(name: 'service_slot')]
class ServiceSlot implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const STATUS_ABERTA     = 'aberta';
    public const STATUS_PREENCHIDA = 'preenchida';
    public const STATUS_CANCELADA  = 'cancelada';

    /** Atividades comuns de serviço. Além destas, aceita texto livre. */
    public const ACTIVITIES = [
        'agua'           => 'Água / Santa Ceia',
        'portaria'       => 'Portaria / Recepção',
        'estacionamento' => 'Estacionamento',
        'limpeza'        => 'Limpeza',
        'som'            => 'Som / Mídia',
        'ofertas'        => 'Ofertas / Coleta',
        'infantil'       => 'Ministério Infantil',
        'seguranca'      => 'Segurança',
        'outro'          => 'Outro',
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Schedule::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    /** Atividade a ser exercida (chave de ACTIVITIES ou texto livre). */
    #[ORM\Column(length: 60)]
    private ?string $activity = null;

    /** Detalhe opcional (ex.: "porta lateral", "berçário", "1º turno"). */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $notes = null;

    /** Diácono que aceitou a vaga (nulo enquanto aberta). */
    #[ORM\ManyToOne(targetEntity: Deacon::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Deacon $deacon = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_ABERTA])]
    private string $status = self::STATUS_ABERTA;

    /** @var Collection<int, SlotWithdrawal> Histórico de desmarcações desta vaga. */
    #[ORM\OneToMany(mappedBy: 'slot', targetEntity: SlotWithdrawal::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $withdrawals;

    public function __construct()
    {
        $this->withdrawals = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getSchedule(): ?Schedule { return $this->schedule; }
    public function setSchedule(?Schedule $v): static { $this->schedule = $v; return $this; }
    public function getActivity(): ?string { return $this->activity; }
    public function setActivity(string $v): static { $this->activity = $v; return $this; }
    public function getActivityLabel(): string { return self::ACTIVITIES[$this->activity] ?? ucfirst((string) $this->activity); }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }
    public function getDeacon(): ?Deacon { return $this->deacon; }
    public function setDeacon(?Deacon $v): static { $this->deacon = $v; return $this; }
    public function getAcceptedAt(): ?\DateTimeImmutable { return $this->acceptedAt; }
    public function setAcceptedAt(?\DateTimeImmutable $v): static { $this->acceptedAt = $v; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $v): static { $this->status = $v; return $this; }

    public function isOpen(): bool { return $this->status === self::STATUS_ABERTA; }
    public function isFilled(): bool { return $this->status === self::STATUS_PREENCHIDA; }

    /** Diácono aceita a vaga (auto-inscrição). */
    public function assignTo(Deacon $deacon): static
    {
        $this->deacon = $deacon;
        $this->acceptedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_PREENCHIDA;
        return $this;
    }

    /** Libera a vaga (após desmarcação) para que outro diácono possa aceitá-la. */
    public function release(): static
    {
        $this->deacon = null;
        $this->acceptedAt = null;
        $this->status = self::STATUS_ABERTA;
        return $this;
    }

    /** @return Collection<int, SlotWithdrawal> */
    public function getWithdrawals(): Collection { return $this->withdrawals; }

    public function addWithdrawal(SlotWithdrawal $w): static
    {
        if (!$this->withdrawals->contains($w)) {
            $this->withdrawals->add($w);
            $w->setSlot($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->getActivityLabel().($this->notes ? ' — '.$this->notes : '');
    }
}
