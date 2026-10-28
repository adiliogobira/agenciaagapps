<?php

namespace App\Tenant;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Filtro SQL Doctrine que adiciona automaticamente
 * "WHERE church_id = :tenant_id" em toda entidade TenantAware.
 * Isolamento de dados por igreja no nível do banco.
 */
class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->reflClass->implementsInterface(TenantAwareInterface::class)) {
            return '';
        }

        if (!$this->hasParameter('tenant_id')) {
            return '';
        }

        $tenantId = $this->getParameter('tenant_id');

        return sprintf('%s.church_id = %s', $targetTableAlias, $tenantId);
    }
}
