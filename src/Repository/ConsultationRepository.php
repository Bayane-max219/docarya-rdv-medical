<?php

namespace App\Repository;

use App\Entity\Consultation;
use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consultation>
 */
class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    /**
     * Trouve toutes les consultations d'un patient
     */
    public function findByPatient(Patient $patient): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.rendezVous', 'r')
            ->where('r.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les consultations partagées avec un professionnel
     * (soit il est l'auteur, soit il est dans la liste des professionnels autorisés)
     */
    public function findSharedWithProfessionnel(ProfessionnelDeSante $professionnel): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.rendezVous', 'r')
            ->leftJoin('c.professionnelsAutorises', 'p')
            ->where('r.professionnel = :professionnel OR p.id = :professionnel')
            ->setParameter('professionnel', $professionnel)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les consultations confirmées d'un professionnel
     */
    public function findConfirmedByProfessionnel(ProfessionnelDeSante $professionnel): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.rendezVous', 'r')
            ->where('r.professionnel = :professionnel')
            ->andWhere('r.statut = :statut')
            ->setParameter('professionnel', $professionnel)
            ->setParameter('statut', RendezVous::STATUT_CONFIRME)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Ajoutez cette méthode si vous en avez besoin pour d'autres fonctionnalités
    public function findConsultationsWithAccess(ProfessionnelDeSante $professionnel): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.professionnelsAutorises', 'p')
            ->where('p.id = :professionnel')
            ->setParameter('professionnel', $professionnel)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
