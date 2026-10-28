<?php

namespace App\Tenant;

use App\Entity\Church;

/**
 * Toda entidade que pertence a uma igreja implementa esta interface.
 * O TenantSubscriber preenche a igreja automaticamente na persistência
 * e o TenantFilter garante que consultas só retornem dados da igreja atual.
 */
interface TenantAwareInterface
{
    public function getChurch(): ?Church;

    public function setChurch(?Church $church): static;
}
