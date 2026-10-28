<?php

namespace App\Service;

use App\Entity\Church;
use App\Entity\Member;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Cria notificações para usuários. Centraliza a lógica para que qualquer módulo
 * possa alertar as pessoas certas (ex.: avisar o diácono que foi escalado).
 */
class Notifier
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $users,
    ) {
    }

    /** Notifica um usuário específico. */
    public function notify(User $to, string $title, string $message, ?string $link = null, string $icon = 'bell', bool $flush = true): void
    {
        $n = (new Notification())
            ->setUser($to)
            ->setTitle($title)
            ->setMessage($message)
            ->setLink($link)
            ->setIcon($icon)
            ->setChurch($to->getChurch());
        $this->em->persist($n);
        if ($flush) {
            $this->em->flush();
        }
    }

    /** Notifica o usuário vinculado a um membro (se houver). */
    public function notifyMember(?Member $member, string $title, string $message, ?string $link = null, string $icon = 'bell'): void
    {
        if ($member === null) {
            return;
        }
        $user = $this->users->findOneBy(['member' => $member]);
        if ($user !== null) {
            $this->notify($user, $title, $message, $link, $icon);
        }
    }

    /**
     * Notifica todos os usuários da igreja que tenham pelo menos um dos papéis.
     * (A lista de usuários por igreja é pequena; filtramos em PHP.)
     */
    public function notifyChurchRoles(Church $church, array $roles, string $title, string $message, ?string $link = null, string $icon = 'bell'): void
    {
        $sent = false;
        foreach ($this->users->findBy(['church' => $church, 'active' => true]) as $user) {
            if (array_intersect($roles, $user->getRoles())) {
                $this->notify($user, $title, $message, $link, $icon, false);
                $sent = true;
            }
        }
        if ($sent) {
            $this->em->flush();
        }
    }
}
