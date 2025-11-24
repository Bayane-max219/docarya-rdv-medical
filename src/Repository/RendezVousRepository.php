<?php

namespace App\Repository;

use App\Entity\RendezVous;
use App\Entity\ProfessionnelDeSante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Patient;

/**
 * @extends ServiceEntityRepository<RendezVous>
 *
 * @method RendezVous|null find($id, $lockMode = null, $lockVersion = null)
 * @method RendezVous|null findOneBy(array $criteria, array $orderBy = null)
 * @method RendezVous[]    findAll()
 * @method RendezVous[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    public function findForUser(User $user)
    {
        $qb = $this->createQueryBuilder('r');

        if ($user instanceof Patient) {
            $qb->where('r.patient = :user')
                ->setParameter('user', $user);
        } elseif ($user instanceof ProfessionnelDeSante) {
            $qb->where('r.professionnel = :user')
                ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }
    // Dans App\Repository\RendezVousRepository.php
    public function countDistinctPatientsByProfessionnel(ProfessionnelDeSante $professionnel): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(DISTINCT r.patient)')
            ->where('r.professionnel = :professionnel')
            ->setParameter('professionnel', $professionnel)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function estDisponible(\DateTimeInterface $dateHeure, ProfessionnelDeSante $professionnel): bool
    {
        // Vérifier les rendez-vous existants
        $rendezVousExistants = $this->createQueryBuilder('r')
            ->where('r.professionnel = :professionnel')
            ->andWhere('r.dateHeure = :dateHeure')
            ->setParameter('professionnel', $professionnel)
            ->setParameter('dateHeure', $dateHeure)
            ->getQuery()
            ->getResult();

        // Vérifier les indisponibilités
        foreach ($professionnel->getIndisponibilites() as $indispo) {
            if ($dateHeure >= $indispo->getDateDebutIndispo() && $dateHeure <= $indispo->getDateFinIndispo()) {
                return false;
            }
        }

        return count($rendezVousExistants) === 0;
    }
}
