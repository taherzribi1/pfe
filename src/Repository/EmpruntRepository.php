<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }


    // src/Repository/EmpruntRepository.php

    public function findNextReturnDateForProduct(Product $product): \DateTime
    {
        return $this->createQueryBuilder('e')
            ->select('MIN(e.date_retour)')
            ->where('e.product = :product')
            ->andWhere('e.statut IN (:statuts)')
            ->setParameter('product', $product)
            ->setParameter('statuts', ['emprunté', 'reserved'])
            ->getQuery()
            ->getSingleScalarResult() ?? new \DateTime('+7 days');
    }
    //    /**
    //     * @return Emprunt[] Returns an array of Emprunt objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Emprunt
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}