<?php

namespace App\Controller;

use App\Entity\ProfessionnelDeSante;
use App\Repository\ProfessionnelDeSanteRepository;
use App\Repository\RendezVousRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Entity\Patient;

class DisponibiliteController extends AbstractController
{
    #[Route('/professionnel/{id}/calendrier', name: 'app_professionnel_de_sante_calendrier')]
    public function index(
        ProfessionnelDeSante $professionnel,
        RendezVousRepository $rendezVousRepo,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $today = new \DateTime('now', new \DateTimeZone('Indian/Antananarivo'));
        $endDate = (clone $today)->modify('+30 days'); // Réduit à 30 jours pour les tests

        $joursDisponibles = [];

        // Générer les jours du calendrier
        $currentDate = clone $today;
        while ($currentDate <= $endDate) {
            $jour = [
                'date' => clone $currentDate,
                'creneaux' => []
            ];

            // Vérifier si c'est un jour de travail
            $jourSemaine = strtolower($currentDate->format('l'));
            $jourFrancais = $this->convertirJourAnglaisVersFrancais($jourSemaine);

            // Récupérer les horaires pour ce jour
            $horairesDuJour = [];
            foreach ($professionnel->getHorairesTravail() as $horaire) {
                if (strtolower($horaire->getJour()) === $jourFrancais) {
                    $horairesDuJour[] = [
                        'debut' => $horaire->getHeureDebut()->format('H:i'),
                        'fin' => $horaire->getHeureFin()->format('H:i')
                    ];
                }
            }

            if (!empty($horairesDuJour)) {
                foreach ($horairesDuJour as $horaire) {
                    // Convertir les heures de début/fin en DateTime
                    $heureDebut = clone $currentDate;
                    list($heures, $minutes) = explode(':', $horaire['debut']);
                    $heureDebut->setTime($heures, $minutes);

                    $heureFin = clone $currentDate;
                    list($heures, $minutes) = explode(':', $horaire['fin']);
                    $heureFin->setTime($heures, $minutes);

                    $currentHeure = clone $heureDebut;

                    while ($currentHeure < $heureFin) {
                        $creneauFin = (clone $currentHeure)->modify('+30 minutes');

                        // Ne pas dépasser l'heure de fin
                        if ($creneauFin > $heureFin) {
                            break;
                        }

                        // Vérifier la disponibilité
                        $disponible = true;

                        // Vérifier les indisponibilités
                        if ($professionnel->estEnIndisponibilite($currentHeure)) {
                            $disponible = false;
                        }

                        // Vérifier les rendez-vous existants
                        if (!$rendezVousRepo->estDisponible($currentHeure, $professionnel)) {
                            $disponible = false;
                        }

                        $jour['creneaux'][] = [
                            'debut' => clone $currentHeure,
                            'fin' => clone $creneauFin,
                            'disponible' => $disponible
                        ];

                        $currentHeure = $creneauFin;
                    }
                }
            }

            $joursDisponibles[] = $jour;
            $currentDate->modify('+1 day');
        }

        // Gestion du formulaire de rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->setProfessionnel($professionnel);
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patient = $this->getUser();
            if (!$patient instanceof Patient) {
                $this->addFlash('error', 'Vous devez être connecté en tant que patient pour demander un rendez-vous.');
                return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
            }

            $rendezVous->setPatient($patient);
            $dateHeure = $rendezVous->getDateHeure();

            $jourSemaine = strtolower($dateHeure->format('l'));
            $jourFrancais = $this->convertirJourAnglaisVersFrancais($jourSemaine);

            // Vérifications
            $horairesDuJour = [];
            foreach ($professionnel->getHorairesTravail() as $horaire) {
                if (strtolower($horaire->getJour()) === $jourFrancais) {
                    $horairesDuJour[] = [
                        'debut' => $horaire->getHeureDebut()->format('H:i'),
                        'fin' => $horaire->getHeureFin()->format('H:i')
                    ];
                }
            }

            if (empty($horairesDuJour)) {
                $this->addFlash('error', 'Le professionnel ne travaille pas ce jour-là.');
                return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
            }

            if (!$this->estDansCreneauValide($dateHeure, $horairesDuJour)) {
                $this->addFlash('error', 'Le professionnel ne travaille pas à cette heure.');
                return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
            }

            if ($professionnel->estEnIndisponibilite($dateHeure)) {
                $this->addFlash('error', 'Le professionnel est indisponible pendant cette période.');
                return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
            }

            if (!$rendezVousRepo->estDisponible($dateHeure, $professionnel)) {
                $this->addFlash('error', 'Le professionnel n\'est pas disponible à cette date et heure.');
                return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
            }

            $entityManager->persist($rendezVous);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a été prise en compte avec succès.');
            return $this->redirectToRoute('app_professionnel_de_sante_calendrier', ['id' => $professionnel->getId()]);
        }

        return $this->render('calendrier/index.html.twig', [
            'professionnel' => $professionnel,
            'joursDisponibles' => $joursDisponibles,
            'today' => $today,
            'form' => $form->createView(),
        ]);
    }

    private function convertirJourAnglaisVersFrancais(string $jourAnglais): string
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

    private function estDansCreneauValide(\DateTimeInterface $dateHeure, array $horairesDuJour): bool
    {
        return true;
    }
}
