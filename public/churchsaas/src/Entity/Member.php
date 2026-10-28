<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use App\Tenant\TenantAwareInterface;
use App\Tenant\TenantAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MÓDULO 1 — Cadastro de Membros.
 * Dados pessoais completos, batismo, estado civil, ministério, função,
 * situação (ativo/inativo) e anexos (fotos/documentos).
 */
#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: 'member')]
class Member implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const STATUS_ATIVO = 'ativo';
    public const STATUS_INATIVO = 'inativo';
    public const STATUS_DISCIPLINA = 'disciplina';
    public const STATUS_TRANSFERIDO = 'transferido';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    // ----- Dados pessoais -----
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private ?string $fullName = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $cpf = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $rg = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $gender = null;

    /** solteiro, casado, viuvo, divorciado, uniao_estavel */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $maritalStatus = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'E-mail inválido')]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    // ----- Vida eclesiástica -----
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $baptismDate = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $membershipDate = null;

    /** Como se tornou membro: batismo, transferencia, aclamacao, conversao */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $entryType = null;

    /** Função na igreja: membro, obreiro, diacono, presbitero, evangelista, pastor... */
    #[ORM\Column(length: 40, options: ['default' => 'membro'])]
    private string $churchRole = 'membro';

    /**
     * Ministérios em que o membro participa (pode ser mais de um).
     * @var Collection<int, Ministry>
     */
    #[ORM\ManyToMany(targetEntity: Ministry::class, inversedBy: 'members')]
    #[ORM\JoinTable(name: 'member_ministry')]
    private Collection $ministries;

    #[ORM\Column(length: 20, options: ['default' => self::STATUS_ATIVO])]
    private string $status = self::STATUS_ATIVO;

    // ----- Anexos -----
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    /** Lista de documentos anexados (nome + caminho). @var array<int,array{name:string,path:string}> */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $documents = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->ministries = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $v): static { $this->fullName = $v; return $this; }
    public function getCpf(): ?string { return $this->cpf; }
    public function setCpf(?string $v): static { $this->cpf = $v; return $this; }
    public function getRg(): ?string { return $this->rg; }
    public function setRg(?string $v): static { $this->rg = $v; return $this; }
    public function getBirthDate(): ?\DateTimeImmutable { return $this->birthDate; }
    public function setBirthDate(?\DateTimeImmutable $v): static { $this->birthDate = $v; return $this; }
    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $v): static { $this->gender = $v; return $this; }
    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function setMaritalStatus(?string $v): static { $this->maritalStatus = $v; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $v): static { $this->phone = $v; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $v): static { $this->email = $v; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $v): static { $this->address = $v; return $this; }
    public function getBaptismDate(): ?\DateTimeImmutable { return $this->baptismDate; }
    public function setBaptismDate(?\DateTimeImmutable $v): static { $this->baptismDate = $v; return $this; }
    public function getMembershipDate(): ?\DateTimeImmutable { return $this->membershipDate; }
    public function setMembershipDate(?\DateTimeImmutable $v): static { $this->membershipDate = $v; return $this; }
    public function getEntryType(): ?string { return $this->entryType; }
    public function setEntryType(?string $v): static { $this->entryType = $v; return $this; }
    public function getChurchRole(): string { return $this->churchRole; }
    public function setChurchRole(string $v): static { $this->churchRole = $v; return $this; }
    /** @return Collection<int, Ministry> */
    public function getMinistries(): Collection { return $this->ministries; }

    public function addMinistry(Ministry $m): static
    {
        if (!$this->ministries->contains($m)) {
            $this->ministries->add($m);
        }
        return $this;
    }

    public function removeMinistry(Ministry $m): static
    {
        $this->ministries->removeElement($m);
        return $this;
    }

    /** Atalho de compatibilidade: retorna o primeiro ministério (ou null). */
    public function getMinistry(): ?Ministry
    {
        return $this->ministries->first() ?: null;
    }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $v): static { $this->status = $v; return $this; }
    public function getPhotoPath(): ?string { return $this->photoPath; }
    public function setPhotoPath(?string $v): static { $this->photoPath = $v; return $this; }
    public function getDocuments(): ?array { return $this->documents; }
    public function setDocuments(?array $v): static { $this->documents = $v; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $v): static { $this->notes = $v; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function isActive(): bool { return $this->status === self::STATUS_ATIVO; }

    public function getAge(): ?int
    {
        return $this->birthDate?->diff(new \DateTimeImmutable())->y;
    }

    public function __toString(): string { return $this->fullName ?? 'Membro'; }
}
