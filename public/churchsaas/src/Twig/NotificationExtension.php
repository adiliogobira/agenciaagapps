<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Expõe as notificações do usuário logado para o template base (barra do topo).
 */
class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly Security $security,
        private readonly NotificationRepository $repo,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_notifications_unread', [$this, 'unreadCount']),
            new TwigFunction('app_notifications_recent', [$this, 'recent']),
        ];
    }

    public function unreadCount(): int
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $this->repo->unreadCountFor($user) : 0;
    }

    /** @return \App\Entity\Notification[] */
    public function recent(int $limit = 8): array
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $this->repo->recentFor($user, $limit) : [];
    }
}
