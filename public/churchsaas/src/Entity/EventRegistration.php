<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/** MÓDULO 6 — Inscrição em evento. */
#[ORM\Entity]
#[ORM\Table(name: 'event_registration')]
class EventRegistration implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 150)]
    private ?string $participantName = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $member = null;

    /** pendente | pago | isento */
    #[ORM\Column(length: 20, options: ['default' => 'pendente'])]
    private string $paymentStatus = 'pendente';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getEvent(): ?Event { return $this->event; }
    public function setEvent(?Event $v): static { $this->event = $v; return $this; }
    public function getParticipantName(): ?string { return $this->participantName; }
    public function setParticipantName(string $v): static { $this->participantName = $v; return $this; }
    public function getMember(): ?Member { return $this->member; }
    public function setMember(?Member $v): static { $this->member = $v; return $this; }
    public function getPaymentStatus(): string { return $this->paymentStatus; }
    public function setPaymentStatus(string $v): static { $this->paymentStatus = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
