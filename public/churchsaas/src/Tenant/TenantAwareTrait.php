<?php

namespace App\Tenant;

use App\Entity\Church;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait que dá a uma entidade o relacionamento com a igreja (tenant).
 * Combine com "implements TenantAwareInterface".
 */
trait TenantAwareTrait
{
    #[ORM\ManyToOne(targetEntity: Church::class)]
    #[ORM\JoinColumn(name: 'church_id', nullable: false)]
    protected ?Church $church = null;

    public function getChurch(): ?Church
    {
        return $this->church;
    }

    public function setChurch(?Church $church): static
    {
        $this->church = $church;

        return $this;
    }
}
