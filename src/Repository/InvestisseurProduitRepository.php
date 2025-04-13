<?php

namespace App\Repository;

use App\Entity\InvestisseurProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvestisseurProduit>
 *
 * @method InvestisseurProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvestisseurProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvestisseurProduit[]    findAll()
 * @method InvestisseurProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvestisseurProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvestisseurProduit::class);
    }

//    /**
//     * @return InvestisseurProduit[] Returns an array of InvestisseurProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvestisseurProduit
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
