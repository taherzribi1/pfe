<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByFirstName(int $id): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id LIKE :id')
            ->setParameter('id', '%' . $id . '%')
            ->getQuery()
            ->getResult();
    }

    public function getMonthlyRevenue(): array
    {
        return $this->createQueryBuilder('o')
            ->select('SUBSTRING(o.createAt, 6, 2) as month, SUM(o.totalPrice) as total')
            ->where('o.isCompleted = true')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function getTotalRevenue(): float
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(o.totalPrice) as total')
            ->where('o.isCompleted = true') 
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
// src/Repository/OrderRepository.php


// src/Repository/OrderRepository.php


    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}