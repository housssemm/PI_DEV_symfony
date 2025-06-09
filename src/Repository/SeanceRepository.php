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
    public function findByDateCoach(\DateTimeInterface $date,int $coachId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.coach', 'c')
            ->addSelect('c')
            ->andWhere('s.Date = :date')
            ->andWhere('c.id = :coachId')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('coachId', $coachId)
            ->getQuery()
            ->getResult();
    }
    public function findByCoachId(int $coachId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.coach', 'c')
            ->addSelect('c')
            ->andWhere('c.id = :coachId')
            ->setParameter('coachId', $coachId)
            ->getQuery()
            ->getResult();
    }

    public function findByDateAndAdherent(\DateTimeInterface $date, int $adherentId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.Date = :date')
            ->andWhere('s.adherent = :adherentId')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('adherentId', $adherentId)
            ->getQuery()
            ->getResult();
    }
    public function findByAdherentId(int $adherentId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.adherent = :adherentId')
            ->setParameter('adherentId', $adherentId)
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
