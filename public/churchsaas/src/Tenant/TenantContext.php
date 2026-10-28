<?php

namespace App\Tenant;

use App\Entity\Church;

/**
 * Guarda a igreja (tenant) ativa durante o ciclo da requisição.
 */
class TenantContext
{
    private ?Church $church = null;

    public function getChurch(): ?Church
    {
        return $this->church;
    }

    public function setChurch(?Church $church): void
    {
        $this->church = $church;
    }

    public function hasChurch(): bool
    {
        return $this->church !== null;
    }

    public function getChurchId(): ?int
    {
        return $this->church?->getId();
    }
}
