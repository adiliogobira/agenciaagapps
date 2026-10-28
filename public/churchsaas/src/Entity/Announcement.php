<?php

namespace App\Entity;

use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * MÓDULO 8 — Comunicação. Aviso/comunicado enviado à igreja.
 *
 * O envio efetivo por e-mail/WhatsApp é feito por um serviço de integração
 * (ver .env: MAILER_DSN, WHATSAPP_API_*). Aqui guardamos o histórico dos avisos.
 */
#[ORM\Entity]
#[ORM\Table(name: 'announcement')]
class Announcement implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const CHANNELS = [
        'mural'    => 'Mural (interno)',
        'email'    => 'E-mail',
        'whatsapp' => 'WhatsApp',
    ];

    public const AUDIENCES = [
        'todos'     => 'Todos',
        'membros'   => 'Membros',
        'diaconos'  => 'Diáconos',
        'lideres'   => 'Líderes',
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $body = null;

    #[ORM\Column(length: 20, options: ['default' => 'mural'])]
    private string $channel = 'mural';

    #[ORM\Column(length: 20, options: ['default' => 'todos'])]
    private string $audience = 'todos';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $v): static { $this->title = $v; return $this; }
    public function getBody(): ?string { return $this->body; }
    public function setBody(string $v): static { $this->body = $v; return $this; }
    public function getChannel(): string { return $this->channel; }
    public function setChannel(string $v): static { $this->channel = $v; return $this; }
    public function getChannelLabel(): string { return self::CHANNELS[$this->channel] ?? $this->channel; }
    public function getAudience(): string { return $this->audience; }
    public function setAudience(string $v): static { $this->audience = $v; return $this; }
    public function getAudienceLabel(): string { return self::AUDIENCES[$this->audience] ?? $this->audience; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function setSentAt(?\DateTimeImmutable $v): static { $this->sentAt = $v; return $this; }
}
