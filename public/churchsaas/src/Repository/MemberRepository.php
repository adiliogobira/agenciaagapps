<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 * O filtro multi-tenant já restringe todas as consultas à igreja atual.
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function save(Member $m, bool $flush = true): void
    {
        $this->getEntityManager()->persist($m);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(Member $m, bool $flush = true): void
    {
        $this->getEntityManager()->remove($m);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /** Busca por nome/CPF/e-mail com filtro opcional de situação. */
    public function search(?string $term, ?string $status): array
    {
        $qb = $this->createQueryBuilder('m')->orderBy('m.fullName', 'ASC');

        if ($term) {
            $qb->andWhere('LOWER(m.fullName) LIKE :t OR m.cpf LIKE :t OR LOWER(m.email) LIKE :t')
               ->setParameter('t', '%'.mb_strtolower($term).'%');
        }
        if ($status) {
            $qb->andWhere('m.status = :s')->setParameter('s', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :s')->setParameter('s', $status)
            ->getQuery()->getSingleScalarResult();
    }

    /** Aniversariantes do mês (para pastoral/comunicação). */
    public function birthdaysInMonth(int $month): array
    {
        return $this->createQueryBuilder('m')
            ->where('MONTH(m.birthDate) = :mo')
            ->setParameter('mo', $month)
            ->orderBy('m.birthDate', 'ASC')
            ->getQuery()->getResult();
    }
}
