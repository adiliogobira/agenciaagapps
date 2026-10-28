<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function save(Schedule $s, bool $flush = true): void
    {
        $this->getEntityManager()->persist($s);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(Schedule $s, bool $flush = true): void
    {
        $this->getEntityManager()->remove($s);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /** Próximas escalas, opcionalmente por tipo. */
    public function upcoming(?string $type = null, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.scheduledAt >= :now')->setParameter('now', new \DateTimeImmutable('today'))
            ->orderBy('s.scheduledAt', 'ASC')
            ->setMaxResults($limit);

        if ($type) {
            $qb->andWhere('s.type = :t')->setParameter('t', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
