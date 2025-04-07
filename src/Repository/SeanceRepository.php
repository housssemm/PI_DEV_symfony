<?php

namespace App\Repository;

use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }
    public function findByCoach($coach)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('s.date', 'ASC') // Vous pouvez trier par date, ou un autre champ si nécessaire
            ->getQuery()
            ->getResult();
    }
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.coach', 'c')
            ->addSelect('c')
            ->andWhere('s.Date = :date')
            ->setParameter('date', $date->format('Y-m-d')) // Formatage explicite
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Seance[] Returns an array of Seance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Seance
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
