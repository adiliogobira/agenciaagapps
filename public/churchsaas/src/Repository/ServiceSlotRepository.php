<?php

namespace App\Repository;

use App\Entity\Deacon;
use App\Entity\ServiceSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceSlot>
 */
class ServiceSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceSlot::class);
    }

    public function save(ServiceSlot $s, bool $flush = true): void
    {
        $this->getEntityManager()->persist($s);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceSlot $s, bool $flush = true): void
    {
        $this->getEntityManager()->remove($s);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Vagas de escalas futuras (para a tela "Vagas para servir"), agrupadas por escala.
     *
     * @return ServiceSlot[]
     */
    public function upcoming(): array
    {
        return $this->createQueryBuilder('sl')
            ->join('sl.schedule', 's')->addSelect('s')
            ->where('s.scheduledAt >= :now')->setParameter('now', new \DateTimeImmutable('today'))
            ->andWhere('sl.status != :cancel')->setParameter('cancel', ServiceSlot::STATUS_CANCELADA)
            ->orderBy('s.scheduledAt', 'ASC')
            ->addOrderBy('sl.activity', 'ASC')
            ->getQuery()->getResult();
    }

    /**
     * Vagas que um diácono aceitou em escalas futuras ("Minhas escalas").
     *
     * @return ServiceSlot[]
     */
    public function upcomingForDeacon(Deacon $deacon): array
    {
        return $this->createQueryBuilder('sl')
            ->join('sl.schedule', 's')->addSelect('s')
            ->where('sl.deacon = :d')->setParameter('d', $deacon)
            ->andWhere('s.scheduledAt >= :now')->setParameter('now', new \DateTimeImmutable('today'))
            ->orderBy('s.scheduledAt', 'ASC')
            ->getQuery()->getResult();
    }
}
