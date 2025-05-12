<?php

namespace App\Repository;

use App\Entity\OrderProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderProducts>
 */
class OrderProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderProducts::class);
    }


    public function findMostPurchasedProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('op')
            ->select('p.id, p.name, SUM(op.qte) as totalQuantity')
            ->join('op.product', 'p')
            ->groupBy('p.id')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    

    //    /**
    //     * @return OrderProducts[] Returns an array of OrderProducts objects
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

    //    public function findOneBySomeField($value): ?OrderProducts
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
