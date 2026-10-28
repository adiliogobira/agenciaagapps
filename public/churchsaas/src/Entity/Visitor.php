<?php

namespace App\Entity;

use App\Repository\VisitorRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 2 — Congregados e Visitantes.
 * Cadastro simplificado, registro de visitas, acompanhamento de integração
 * e conversão para membro.
 */
#[ORM\Entity(repositoryClass: VisitorRepository::class)]
#[ORM\Table(name: 'visitor')]
class Visitor implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const TYPE_VISITANTE = 'visitante';
    public const TYPE_CONGREGADO = 'congregado';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $fullName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20, options: ['default' => self::TYPE_VISITANTE])]
    private string $type = self::TYPE_VISITANTE;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $firstVisitDate;

    /** Quem convidou / trouxe o visitante. */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $invitedBy = null;

    /** Estágio de integração: novo, contatado, discipulado, integrado. */
    #[ORM\Column(length: 30, options: ['default' => 'novo'])]
    private string $integrationStage = 'novo';

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $visitCount = 1;

    /** Se convertido em membro, guarda a referência. */
    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Member $convertedMember = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->firstVisitDate = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $v): static { $this->fullName = $v; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $v): static { $this->phone = $v; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): static { $this->email = $v; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $v): static { $this->type = $v; return $this; }
    public function getFirstVisitDate(): \DateTimeImmutable { return $this->firstVisitDate; }
    public function setFirstVisitDate(\DateTimeImmutable $v): static { $this->firstVisitDate = $v; return $this; }
    public function getInvitedBy(): ?Member { return $this->invitedBy; }
    public function setInvitedBy(?Member $v): static { $this->invitedBy = $v; return $this; }
    public function getIntegrationStage(): string { return $this->integrationStage; }
    public function setIntegrationStage(string $v): static { $this->integrationStage = $v; return $this; }
    public function getVisitCount(): int { return $this->visitCount; }
    public function setVisitCount(int $v): static { $this->visitCount = $v; return $this; }
    public function getConvertedMember(): ?Member { return $this->convertedMember; }
    public function setConvertedMember(?Member $v): static { $this->convertedMember = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }
    public function __toString(): string { return $this->fullName ?? 'Visitante'; }
}
