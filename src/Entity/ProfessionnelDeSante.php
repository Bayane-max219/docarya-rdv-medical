<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProfessionnelDeSanteRepository;
use App\Repository\AvisRepository;

#[ORM\Entity(repositoryClass: ProfessionnelDeSanteRepository::class)]
class ProfessionnelDeSante extends User
{
    #[ORM\Column]
    private ?float $tarif = null;

    #[ORM\OneToMany(mappedBy: 'professionnel', targetEntity: HoraireTravail::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $horairesTravail;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\ManyToOne(targetEntity: Specialite::class, inversedBy: 'professionnelsDeSante')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Specialite $specialite = null;

    #[ORM\OneToMany(targetEntity: GestionAgenda::class, mappedBy: 'professionnel', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $indisponibilites;

    public function __construct()
    {
        $this->indisponibilites = new ArrayCollection();
        $this->horairesTravail = new ArrayCollection();
    }

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): static
    {
        $this->tarif = $tarif;

        return $this;
    } // Dans App\Entity\ProfessionnelDeSante.php
    public function getNombrePatientsUniques(RendezVousRepository $rendezVousRepo): int
    {
        return $rendezVousRepo->countDistinctPatientsByProfessionnel($this);
    }

    public function getMoyenneAvis(AvisRepository $avisRepo): float
    {
        return $avisRepo->calculateAverageRating($this);
    }
    /**
     * @return Collection<int, HoraireTravail>
     */
    public function getHorairesTravail(): Collection
    {
        return $this->horairesTravail;
    }

    public function addHoraireTravail(HoraireTravail $horaire): self
    {
        if (!$this->horairesTravail->contains($horaire)) {
            $this->horairesTravail->add($horaire);
            $horaire->setProfessionnel($this);
        }

        return $this;
    }

    public function removeHoraireTravail(HoraireTravail $horaire): self
    {
        if ($this->horairesTravail->removeElement($horaire)) {
            if ($horaire->getProfessionnel() === $this) {
                $horaire->setProfessionnel(null);
            }
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    // Ajoutez ces méthodes
    public function getIndisponibilites(): Collection
    {
        return $this->indisponibilites;
    }

    public function addIndisponibilite(GestionAgenda $indisponibilite): self
    {
        if (!$this->indisponibilites->contains($indisponibilite)) {
            $this->indisponibilites[] = $indisponibilite;
            $indisponibilite->setProfessionnel($this);
        }
        return $this;
    }

    public function removeIndisponibilite(GestionAgenda $indisponibilite): self
    {
        if ($this->indisponibilites->removeElement($indisponibilite)) {
            if ($indisponibilite->getProfessionnel() === $this) {
                $indisponibilite->setProfessionnel(null);
            }
        }
        return $this;
    }
    public function estDisponible(\DateTimeInterface $dateHeure, RendezVousRepository $rendezVousRepo): bool
    {
        // Vérifier d'abord les horaires de travail
        if (!$this->travailleALaDate($dateHeure)) {
            return false;
        }
        // Vérifier les rendez-vous existants
        if (!$rendezVousRepo->estDisponible($dateHeure, $this)) {
            return false;
        }
        // Vérifier les indisponibilités
        if ($this->estEnIndisponibilite($dateHeure)) {
            return false;
        }
        return true;
    }
    public function travailleALaDate(\DateTimeInterface $dateHeure): bool
    {
        $jourSemaine = strtolower($dateHeure->format('l'));
        $heure = $dateHeure->format('H:i:s');
        foreach ($this->getHorairesTravail() as $horaire) {
            $jourHoraire = $this->convertirJourFrancaisVersAnglais($horaire->getJour());
            if (strtolower($jourHoraire) === $jourSemaine) {
                $heureDebut = $horaire->getHeureDebut()->format('H:i:s');
                $heureFin = $horaire->getHeureFin()->format('H:i:s');
                if ($heure >= $heureDebut && $heure <= $heureFin) {
                    return true;
                }
            }
        }
        return false;
    }
    public function convertirJourAnglaisVersFrancais(string $jourAnglais): string
    {
        $jours = [
            'monday' => 'lundi',
            'tuesday' => 'mardi',
            'wednesday' => 'mercredi',
            'thursday' => 'jeudi',
            'friday' => 'vendredi',
            'saturday' => 'samedi',
            'sunday' => 'dimanche'
        ];

        $jourAnglais = strtolower($jourAnglais);
        return $jours[$jourAnglais] ?? $jourAnglais;
    }
    public function convertirJourFrancaisVersAnglais(string $jourFrancais): string
    {
        $jours = [
            'lundi' => 'monday',
            'mardi' => 'tuesday',
            'mercredi' => 'wednesday',
            'jeudi' => 'thursday',
            'vendredi' => 'friday',
            'samedi' => 'saturday',
            'dimanche' => 'sunday'
        ];

        $jourFrancais = strtolower($jourFrancais);
        return $jours[$jourFrancais] ?? $jourFrancais;
    }
    // Dans ProfessionnelDeSante.php
    public function initGestionAgenda(): void
    {
        // Ajouter la pause déjeuner récurrente
        $this->addIndisponibiliteRecurrente('12:00:00', '13:00:00', 'Pause déjeuner');
    }

    public function addIndisponibiliteRecurrente(string $heureDebut, string $heureFin, string $motif): void
    {
        // Créer une indisponibilité pour chaque jour de travail
        foreach ($this->getHorairesTravail() as $horaire) {
            $indispo = new GestionAgenda();
            $indispo->setProfessionnel($this);
            $indispo->setMotif($motif);

            // Convertir le jour de travail en date concrète (prochain jour correspondant)
            $jour = $this->convertirJourFrancaisVersAnglais($horaire->getJour());
            $prochainJour = date('Y-m-d', strtotime("next $jour"));

            $indispo->setDateDebutIndispo(new \DateTime($prochainJour . ' ' . $heureDebut));
            $indispo->setDateFinIndispo(new \DateTime($prochainJour . ' ' . $heureFin));

            $this->addIndisponibilite($indispo);
        }
    }
    // Dans App\Entity\ProfessionnelDeSante.php

    public function getJoursTravail(): array
    {
        $jours = [];
        foreach ($this->getHorairesTravail() as $horaire) {
            $jours[] = $horaire->getJour();
        }
        return array_unique($jours);
    }

    public function getHeuresTravailPourJour(string $jour): array
    {
        $heures = [];
        foreach ($this->getHorairesTravail() as $horaire) {
            if ($horaire->getJour() === $jour) {
                $heures[] = [
                    'debut' => $horaire->getHeureDebut(),
                    'fin' => $horaire->getHeureFin()
                ];
            }
        }
        return $heures;
    }
    public function estEnIndisponibilite(\DateTimeInterface $dateHeure): bool
    {
        foreach ($this->getIndisponibilites() as $indispo) {
            if (
                $dateHeure >= $indispo->getDateDebutIndispo() &&
                $dateHeure < $indispo->getDateFinIndispo()
            ) {
                return true;
            }
        }
        return false;
    }
}
