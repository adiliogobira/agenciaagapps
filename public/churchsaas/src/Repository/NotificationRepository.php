<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function unreadCountFor(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :u')->setParameter('u', $user)
            ->andWhere('n.read = false')
            ->getQuery()->getSingleScalarResult();
    }

    /** @return Notification[] */
    public function recentFor(User $user, int $limit = 8): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :u')->setParameter('u', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return Notification[] */
    public function allFor(User $user, int $limit = 100): array
    {
        return $this->findBy(['user' => $user], ['createdAt' => 'DESC'], $limit);
    }

    public function markAllRead(User $user): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.read', ':val')->setParameter('val', true)
            ->where('n.user = :u')->setParameter('u', $user)
            ->andWhere('n.read = false')
            ->getQuery()->execute();
    }
}
