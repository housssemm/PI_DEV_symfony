<?php

namespace App\Repository;

use App\Entity\Offre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offre>
 */
class OffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offre::class);
    }

//    /**
//     * @return Offre[] Returns an array of Offre objects
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

//    public function findOneBySomeField($value): ?Offre
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    // OffreRepository.php
    public function findByFilters($nom = null, $date = null, $tri = null)
    {
        $queryBuilder = $this->createQueryBuilder('o');

        // Appliquer les filtres pour nom et date
        if ($nom) {
            $queryBuilder->andWhere('o.nom LIKE :nom')
                ->setParameter('nom', '%' . $nom . '%');
        }

        if ($date) {
            $queryBuilder->andWhere('o.duree_validite >= :date')
                ->setParameter('date', new \DateTime($date));
        }

        // Appliquer le tri
        if ($tri) {
            switch ($tri) {
                case 'nom_asc':
                    $queryBuilder->orderBy('o.nom', 'ASC');
                    break;
                case 'nom_desc':
                    $queryBuilder->orderBy('o.nom', 'DESC');
                    break;
                case 'date_asc':
                    $queryBuilder->orderBy('o.duree_validite', 'ASC');
                    break;
                case 'date_desc':
                    $queryBuilder->orderBy('o.duree_validite', 'DESC');
                    break;
                default:
                    break;
            }
        } else {
            $queryBuilder->orderBy('o.duree_validite', 'DESC'); // Si aucun tri, par défaut par date décroissante
        }

        return $queryBuilder->getQuery()->getResult();
    }



}
