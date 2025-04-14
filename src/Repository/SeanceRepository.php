<?php

namespace App\Repository;

use App\Entity\Adherent;
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
    public function findByDateCoach(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.coach', 'c')
            ->addSelect('c')
            ->andWhere('s.Date = :date')
            ->setParameter('date', $date->format('Y-m-d')) // Formatage explicite
            ->getQuery()
            ->getResult();
    }
    public function findByDateAndAdherent(\DateTimeInterface $date, Adherent $adherent): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.Date = :date')
            ->andWhere('s.adherent = :adherent')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('adherent', $adherent)
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
