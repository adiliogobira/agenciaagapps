<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Controla acesso por MÓDULO conforme o perfil, mapeando os perfis de acesso
 * exigidos (Admin, Pastor, Secretário, Tesoureiro, Diácono, Líder de Ministério).
 */
class ModuleVoter extends Voter
{
    // Atributos: MODULE_<nome>
    private const MAP = [
        'MODULE_MEMBERS'    => ['ROLE_SECRETARIO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_VISITORS'   => ['ROLE_SECRETARIO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_DIACONIA'   => ['ROLE_DIACONO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_PASTORAL'   => ['ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_TREASURY'   => ['ROLE_TESOUREIRO', 'ROLE_ADMIN'],
        'MODULE_EVENTS'     => ['ROLE_SECRETARIO', 'ROLE_PASTOR', 'ROLE_LIDER_MINISTERIO', 'ROLE_ADMIN'],
        'MODULE_SCHOOL'     => ['ROLE_SECRETARIO', 'ROLE_LIDER_MINISTERIO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_COMMS'      => ['ROLE_SECRETARIO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_REPORTS'    => ['ROLE_PASTOR', 'ROLE_TESOUREIRO', 'ROLE_ADMIN'],
        'MODULE_INVENTORY'  => ['ROLE_DIACONO', 'ROLE_SECRETARIO', 'ROLE_PASTOR', 'ROLE_ADMIN'],
        'MODULE_ADMIN'      => ['ROLE_ADMIN'],
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return isset(self::MAP[$attribute]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $roles = $token->getRoleNames();
        if (in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_SUPER_ADMIN', $roles, true)) {
            return true;
        }

        foreach (self::MAP[$attribute] as $required) {
            if (in_array($required, $roles, true)) {
                return true;
            }
        }

        return false;
    }
}
