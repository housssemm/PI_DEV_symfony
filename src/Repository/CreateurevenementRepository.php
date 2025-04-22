<?php

namespace App\Repository;

use App\Entity\CreateurEvenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreateurEvenement>
 *
 * @method CreateurEvenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CreateurEvenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CreateurEvenement[]    findAll()
 * @method CreateurEvenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreateurevenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreateurEvenement::class);
    }
} 