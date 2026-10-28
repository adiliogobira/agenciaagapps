<?php

namespace App\Repository;

use App\Entity\InventoryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryItem>
 */
class InventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryItem::class);
    }

    public function save(InventoryItem $i, bool $flush = true): void
    {
        $this->getEntityManager()->persist($i);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InventoryItem $i, bool $flush = true): void
    {
        $this->getEntityManager()->remove($i);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** Itens abaixo (ou no limite) da quantidade mínima. @return InventoryItem[] */
    public function lowStock(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.minQuantity IS NOT NULL')
            ->andWhere('i.quantity <= i.minQuantity')
            ->orderBy('i.name', 'ASC')
            ->getQuery()->getResult();
    }
}
