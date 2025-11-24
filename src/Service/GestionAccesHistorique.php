<?php

namespace App\Service;

use App\Entity\Consultation;
use App\Entity\DemandeAccesHistorique;
use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use App\Entity\RendezVous;
use Doctrine\ORM\EntityManagerInterface;

class GestionAccesHistorique
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function creerDemandeAcces(
        ProfessionnelDeSante $demandeur,
        Patient $patient,
        ?RendezVous $rendezVousLie = null
    ): DemandeAccesHistorique {
        $demande = new DemandeAccesHistorique();
        $demande->setDemandeur($demandeur);
        $demande->setPatient($patient);
        $demande->setRendezVousLie($rendezVousLie);

        $this->em->persist($demande);
        $this->em->flush();

        return $demande;
    }

    public function traiterDemandeAcces(
        DemandeAccesHistorique $demande,
        string $statut,
        array $consultationsASelectionner = []
    ): void {
        $demande->setStatut($statut);
        $demande->setUpdatedAt(new \DateTimeImmutable());

        if ($statut === 'acceptee') {
            $patient = $demande->getPatient();
            $professionnel = $demande->getDemandeur();

            foreach ($consultationsASelectionner as $consultation) {
                if ($consultation->getRendezVous()->getPatient() === $patient) {
                    $consultation->addProfessionnelAutorise($professionnel);
                }
            }
        }

        $this->em->flush();
    }
}