<?php

namespace App\Repository;

use App\Entity\ProgressPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgressPost>
 *
 * @method ProgressPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgressPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgressPost[]    findAll()
 * @method ProgressPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProgressPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgressPost::class);
    }

    public function save(ProgressPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProgressPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ProgressPost[] Returns an array of public ProgressPost objects
     */
    public function findAllPublic(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublic = :val')
            ->setParameter('val', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return ProgressPost[] Returns an array of ProgressPost objects for a specific user
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :val')
            ->setParameter('val', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
