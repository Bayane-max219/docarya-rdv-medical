<?php

namespace App\Repository;

use App\Entity\DemandeAccesHistorique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeAccesHistorique>
 *
 * @method DemandeAccesHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeAccesHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeAccesHistorique[]    findAll()
 * @method DemandeAccesHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeAccesHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeAccesHistorique::class);
    }

    //    /**
    //     * @return DemandeAccesHistorique[] Returns an array of DemandeAccesHistorique objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DemandeAccesHistorique
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
