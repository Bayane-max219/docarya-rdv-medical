<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use App\Entity\RendezVous;

use App\Repository\ProfessionnelDeSanteRepository;
use App\Repository\AvisRepository;
use App\Repository\RendezVousRepository;
use App\Repository\ConsultationRepository;

use App\Form\AvisType;
use App\Form\RendezVousType;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends AbstractController
{
    private $security;
    private $httpClient;

    public function __construct(Security $security, HttpClientInterface $httpClient)
    {
        $this->security = $security;
        $this->httpClient = $httpClient;
    }

    /**
     * Calcule la distance entre deux points géographiques en km (formule Haversine)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Rayon de la Terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2); // Arrondi à 2 décimales
    }

    /**
     * Calcule la durée estimée du trajet en voiture entre deux points
     * Utilise l'API OpenRouteService pour des résultats précis
     */
    private function calculateTravelTime(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        try {
            // Clé API OpenRouteService (à remplacer par votre clé)
            $apiKey = $this->getParameter('5b3ce3597851110001cf62483fafa340bc074ade88e465359cfd06ca');

            // Appel à l'API OpenRouteService pour obtenir la durée et la distance du trajet
            $response = $this->httpClient->request('GET', 'https://api.openrouteservice.org/v2/directions/driving-car', [
                'query' => [
                    'api_key' => $apiKey,
                    'start' => "$lon1,$lat1",
                    'end' => "$lon2,$lat2"
                ],
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            $data = $response->toArray();

            if (isset($data['features'][0]['properties']['segments'][0])) {
                $segment = $data['features'][0]['properties']['segments'][0];
                $distanceKm = round($segment['distance'] / 1000, 2); // Conversion en km
                $durationMin = round($segment['duration'] / 60, 0); // Conversion en minutes

                return [
                    'distance' => $distanceKm,
                    'duration' => $durationMin,
                    'formatted_duration' => $this->formatDuration($durationMin)
                ];
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on utilise une estimation basée sur la distance à vol d'oiseau
            $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);
            $estimatedDuration = round($distance * 2); // Estimation grossière: 2 min par km

            return [
                'distance' => $distance,
                'duration' => $estimatedDuration,
                'formatted_duration' => $this->formatDuration($estimatedDuration),
                'estimated' => true
            ];
        }

        // Fallback si l'API ne renvoie pas les données attendues
        $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);
        $estimatedDuration = round($distance * 2);

        return [
            'distance' => $distance,
            'duration' => $estimatedDuration,
            'formatted_duration' => $this->formatDuration($estimatedDuration),
            'estimated' => true
        ];
    }

    /**
     * Formate la durée en minutes en texte lisible
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "$minutes min";
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return "$hours h";
        }

        return "$hours h $mins min";
    }

    /**
     * Convertit un objet DateTime en une chaîne de temps au format "HHhMM"
     *
     * @param \DateTimeInterface $dateTime Objet DateTime
     * @return string Chaîne de temps au format "HHhMM"
     */
    private function convertDateTimeToString(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format('H\hi');
    }

    private function formatHorairesTravail(Collection $horairesTravail): array
    {
        $formattedHoraires = [];
        foreach ($horairesTravail as $horaire) {
            $formattedHoraires[] = [
                'jour' => $horaire->getJour(),
                'heureDebut' => $this->convertDateTimeToString($horaire->getHeureDebut()),
                'heureFin' => $this->convertDateTimeToString($horaire->getHeureFin()),
            ];
        }
        return $formattedHoraires;
    }

    #[Route('/search', name: 'app_search')]
    public function index(
        ProfessionnelDeSanteRepository $professionnelDeSanteRepository,
        Request $request
    ): Response {
        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Récupérer les paramètres de recherche
        $filters = [
            'specialite_nom' => $request->query->get('specialite_nom'),
            'categorie' => $request->query->get('categorie'),
            'sous_categorie' => $request->query->get('sous_categorie'),
            'specialite_select' => $request->query->get('specialite_select'),
            'geolocalisation' => $request->query->get('geolocalisation'),
            'tarif_min' => $request->query->get('tarif_min'),
            'tarif_max' => $request->query->get('tarif_max'),
            'jour' => $request->query->get('jour'),
            'heure_debut' => $request->query->get('heure_debut'),
            'heure_fin' => $request->query->get('heure_fin'),
            'user_latitude' => $request->query->get('user_latitude'),
            'user_longitude' => $request->query->get('user_longitude'),
            'search_radius' => $request->query->get('search_radius', 40),
            'travel_mode' => $request->query->get('travel_mode', 'driving'), // Mode de transport par défaut
        ];

        try {
            // Recherche des professionnels avec filtres
            $paginator = $professionnelDeSanteRepository->findByAdvancedFilters($filters, $page, $limit);
            $professionnels = iterator_to_array($paginator->getIterator());

            // Traitement des résultats avec géolocalisation
            $professionnelsWithDistance = [];
            if ($filters['user_latitude'] && $filters['user_longitude']) {
                $userLat = (float)$filters['user_latitude'];
                $userLng = (float)$filters['user_longitude'];

                foreach ($professionnels as $pro) {
                    // Vérifier que le professionnel a des coordonnées valides
                    if (
                        is_object($pro) && method_exists($pro, 'getLatitude') &&
                        $pro->getLatitude() !== null && $pro->getLongitude() !== null
                    ) {
                        // Calculer la distance à vol d'oiseau
                        $distance = $this->calculateDistance(
                            $userLat,
                            $userLng,
                            $pro->getLatitude(),
                            $pro->getLongitude()
                        );

                        // Calculer la durée et la distance du trajet
                        $travelInfo = $this->calculateTravelTime(
                            $userLat,
                            $userLng,
                            $pro->getLatitude(),
                            $pro->getLongitude()
                        );

                        $professionnelsWithDistance[] = [
                            'professionnel' => $pro,
                            'distance' => $distance,
                            'distance_km' => number_format($distance, 1) . ' km',
                            'duration' => $travelInfo['duration'],
                            'duration_text' => $travelInfo['formatted_duration'],
                            'estimated' => $travelInfo['estimated'] ?? false
                        ];
                    }
                }

                // Trier par distance
                usort($professionnelsWithDistance, function ($a, $b) {
                    return $a['distance'] <=> $b['distance'];
                });
            }

            // Formatter les résultats finaux
            $professionnelsDecodes = [];
            $dataToUse = $filters['user_latitude'] ? $professionnelsWithDistance : $professionnels;

            foreach ($dataToUse as $item) {
                $pro = is_array($item) ? $item['professionnel'] : $item;

                // Récupérer les avis pour ce professionnel
                $avisList = $this->getDoctrine()->getRepository(\App\Entity\Avis::class)->findBy(['professionnel' => $pro]);
                $totalNotes = 0;
                $nbAvis = count($avisList);
                foreach ($avisList as $avis) {
                    $totalNotes += $avis->getNote();
                }
                $moyenneNotes = $nbAvis > 0 ? $totalNotes / $nbAvis : 0;

                $professionnelData = [
                    'professionnel' => $pro,
                    'horairesTravail' => $this->formatHorairesTravail($pro->getHorairesTravail()),
                    'avisList' => $avisList,
                    'moyenneNotes' => $moyenneNotes,
                    'nbAvis' => $nbAvis,
                ];

                // Ajouter les informations de distance et de durée si disponibles
                if (is_array($item)) {
                    if (isset($item['distance'])) {
                        $professionnelData['distance'] = $item['distance'];
                        $professionnelData['distance_km'] = $item['distance_km'];
                    }
                    if (isset($item['duration'])) {
                        $professionnelData['duration'] = $item['duration'];
                        $professionnelData['duration_text'] = $item['duration_text'];
                        $professionnelData['estimated'] = $item['estimated'] ?? false;
                    }
                }

                $professionnelsDecodes[] = $professionnelData;
            }

            // Calcul du nombre total d'éléments et de pages
            $totalItems = count($professionnels);
            $totalPages = ceil($totalItems / $limit);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
            $professionnelsDecodes = [];
            $totalItems = 0;
            $totalPages = 0;
        }

        // Récupérer toutes les spécialités uniques pour le filtre
        $specialites = $professionnelDeSanteRepository->findAllSpecialitesWithDetails();

        return $this->render('home/index.html.twig', [
            'professionnels' => $professionnelsDecodes,
            'specialites' => $specialites,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'hasGeolocation' => !empty($filters['user_latitude']),
        ]);
    }
    #[Route('/accueil', name: 'app_home_user')]
    public function index_user(
        ProfessionnelDeSanteRepository $professionnelDeSanteRepository,
        RendezVousRepository $rendezVousRepository,
        ConsultationRepository $consultationRepository,
        Request $request
    ): Response {
        $user = $this->security->getUser();
        $searchFilters = [
            'specialite_nom' => $request->query->get('specialite_nom'),
            'categorie' => $request->query->get('categorie'),
        ];

        $professionnels = [];
        $specialites = [];

        try {
            $professionnelsPaginator = $professionnelDeSanteRepository->findByAdvancedFilters($searchFilters, 1, 5);
            $specialites = $professionnelDeSanteRepository->findAllSpecialitesWithDetails();

            // Formatter les horaires de travail ET ajouter les avis pour le modal
            $professionnels = array_map(function ($pro) {
                $avisList = $this->getDoctrine()->getRepository(\App\Entity\Avis::class)->findBy(['professionnel' => $pro]);
                $totalNotes = 0;
                $nbAvis = count($avisList);
                foreach ($avisList as $avis) {
                    $totalNotes += $avis->getNote();
                }
                $moyenneNotes = $nbAvis > 0 ? $totalNotes / $nbAvis : 0;
                return [
                    'professionnel' => $pro,
                    'horairesTravail' => $this->formatHorairesTravail($pro->getHorairesTravail()),
                    'avisList' => $avisList,
                    'moyenneNotes' => $moyenneNotes,
                    'nbAvis' => $nbAvis,
                ];
            }, iterator_to_array($professionnelsPaginator));
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        // Section Rendez-vous (seulement si utilisateur connecté)
        $rendezVous = [];
        if ($user) {
            $rendezVous = $rendezVousRepository->findForUser($user, 5); // Limité à 5 rendez-vous
        }

        // Section Consultations (seulement si utilisateur connecté)
        $consultations = [];
        if ($user) {
            if ($this->isGranted('ROLE_ADMIN')) {
                $consultations = $consultationRepository->findRecent(5); // Limité à 5 consultations
            } elseif ($user instanceof Patient) {
                $consultations = $consultationRepository->findByPatient($user, 5);
            } elseif ($user instanceof ProfessionnelDeSante) {
                $consultations = $consultationRepository->findSharedWithProfessionnel($user, 5);
            }
        }

        return $this->render('home/index_user.html.twig', [
            // Section Recherche
            'professionnels' => $professionnels,
            'specialites' => $specialites,
            'filters' => $searchFilters,

            // Section Rendez-vous
            'rendez_vouses' => $rendezVous,

            // Section Consultations
            'consultations' => $consultations,

            // Info utilisateur
            'user' => $user,
        ]);
    }

    #[Route('/home/sp/{id}', name: 'app_professionnel_show')]
    public function showProfessionnel(
        ProfessionnelDeSante $professionnel,
        ProfessionnelDeSanteRepository $professionnelDeSanteRepository,
        AvisRepository $avisRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        RendezVousRepository $rendezVousRepository
    ): Response {
        // Récupérer toutes les spécialités uniques pour le filtre
        $specialites = $professionnelDeSanteRepository->findAllSpecialitesWithDetails();

        // Utiliser directement les horaires de travail décodés par Doctrine
        $formattedHoraires = $this->formatHorairesTravail($professionnel->getHorairesTravail());

        // Récupérer les avis pour le professionnel
        $avisList = $avisRepository->findBy(['professionnel' => $professionnel]);

        // Calculer la moyenne des notes
        $totalNotes = 0;
        $nbAvis = count($avisList);

        foreach ($avisList as $avis) {
            $totalNotes += $avis->getNote();
        }

        $moyenneNotes = $nbAvis > 0 ? $totalNotes / $nbAvis : 0;

        // Créer un nouveau rendez-vous
        $rendezVous = new RendezVous();
        $rendezVous->setProfessionnel($professionnel);
        $form = $this->createForm(RendezVousType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patient = $this->getUser();
            if (!$patient instanceof Patient) {
                $this->addFlash('error', 'Vous devez être connecté en tant que patient pour demander un rendez-vous.');
                return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
            }

            $rendezVous->setPatient($patient);
            $dateHeure = $rendezVous->getDateHeure();
            $jourSemaine = strtolower($dateHeure->format('l'));

            // 1. Vérification jour de travail
            $horairesDuJour = $this->getHorairesDuJour($professionnel, $jourSemaine);
            if (empty($horairesDuJour)) {
                $this->addFlash('error', 'Le professionnel ne travaille pas ce jour-là.');
                return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
            }

            // 2. Vérification créneau horaire
            if (!$this->estDansCreneauValide($dateHeure, $horairesDuJour)) {
                $this->addFlash('error', 'Le professionnel ne travaille pas à cette heure.');
                return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
            }

            // 3. Vérification indisponibilités
            if ($this->estEnIndisponibilite($dateHeure, $professionnel)) {
                $this->addFlash('error', 'Le professionnel est indisponible pendant cette période.');
                return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
            }

            // 4. Vérification disponibilité exacte
            if (!$rendezVousRepository->estDisponible($dateHeure, $professionnel)) {
                $this->addFlash('error', 'Le professionnel n\'est pas disponible à cette date et heure.');
                return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
            }

            // Tout est OK, on persiste
            $entityManager->persist($rendezVous);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a été prise en compte avec succès.');
            return $this->redirectToRoute('app_professionnel_show', ['id' => $professionnel->getId()]);
        }

        return $this->render('home/professionnel_show.html.twig', [
            'professionnel' => $professionnel,
            'horairesTravail' => $formattedHoraires,
            'specialites' => $specialites,
            'form' => $form->createView(),
            'avisList' => $avisList,
            'moyenneNotes' => $moyenneNotes,
            'nbAvis' => $nbAvis
        ]);
    }

    #[Route('/categories', name: 'app_categories')]
    public function getCategories(ProfessionnelDeSanteRepository $repo): JsonResponse
    {
        $categories = $repo->findDistinctCategories();
        return $this->json($categories);
    }

    #[Route('/sous-categories/{categorie}', name: 'app_sous_categories')]
    public function getSousCategories(string $categorie, ProfessionnelDeSanteRepository $repo): JsonResponse
    {
        $sousCategories = $repo->findDistinctSousCategories($categorie);
        return $this->json($sousCategories);
    }

    #[Route('/specialites/{categorie}/{sousCategorie}', name: 'app_specialites')]
    public function getSpecialites(string $categorie, string $sousCategorie, ProfessionnelDeSanteRepository $repo): JsonResponse
    {
        $specialites = $repo->findSpecialitesByCategoryAndSubcategory($categorie, $sousCategorie);
        return $this->json($specialites);
    }
    #[Route('/admin', name: 'app_admin_home')]
    public function indexAdmin(): Response
    {
        // Liste des entités avec leurs routes CRUD correspondantes
        $crudLinks = [
            'Professionnel de Santé' => $this->generateUrl('app_professionnel_de_sante_index'),
            'Spécialité' => $this->generateUrl('app_specialite_index'),
            'Avis' => $this->generateUrl('app_avis_index'),
            // Ajoutez d'autres entités ici avec leurs routes CRUD
        ];

        return $this->render('administrateur/home.html.twig', [
            'crudLinks' => $crudLinks,
        ]);
    }

    #[Route('/professionnel/{id}/avis', name: 'app_professionnel_avis')]
    public function showProfessionnelAvis(
        ProfessionnelDeSante $professionnel,
        AvisRepository $avisRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les avis pour le professionnel spécifié
        $avisList = $avisRepository->findBy(['professionnel' => $professionnel]);

        // Calculer la moyenne des notes
        $totalNotes = 0;
        $nbAvis = count($avisList);

        foreach ($avisList as $avis) {
            $totalNotes += $avis->getNote();
        }

        $moyenneNotes = $nbAvis > 0 ? $totalNotes / $nbAvis : 0;

        // Créer un nouvel avis
        $avis = new Avis();
        $avis->setProfessionnel($professionnel); // Associer l'avis au professionnel
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le patient à l'avis (supposons que vous avez un système d'authentification)
            $patient = $this->getUser(); // Supposons que l'utilisateur connecté est un patient
            if ($patient instanceof Patient) {
                $avis->setPatient($patient);
                $entityManager->persist($avis);
                $entityManager->flush();

                // Redirection vers la même page après avoir ajouté l'avis
                return $this->redirectToRoute('app_professionnel_avis', ['id' => $professionnel->getId()]);
            } else {
                $this->addFlash('error', 'Vous devez être connecté en tant que patient pour laisser un avis.');
            }
        }

        return $this->render('home/professionnel_avis.html.twig', [
            'professionnel' => $professionnel,
            'avisList' => $avisList,
            'form' => $form->createView(),
            'moyenneNotes' => $moyenneNotes,
        ]);
    }
    #[Route('/professionnel/{id}/avis/{avisId}/edit', name: 'app_professionnel_avis_edit')]
    public function editProfessionnelAvis(
        ProfessionnelDeSante $professionnel,
        int $avisId,
        AvisRepository $avisRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer l'avis par son ID
        $avis = $avisRepository->find($avisId);

        // Vérifier si l'avis existe
        if (!$avis) {
            $this->addFlash('error', 'L\'avis n\'existe pas.');
            return $this->redirectToRoute('app_professionnel_avis', ['id' => $professionnel->getId()]);
        }

        // Vérifier si le patient connecté est le propriétaire de l'avis
        $patient = $this->getUser();
        if ($patient !== $avis->getPatient()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cet avis.');
            return $this->redirectToRoute('app_professionnel_avis', ['id' => $professionnel->getId()]);
        }

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirection vers la même page après avoir modifié l'avis
            return $this->redirectToRoute('app_professionnel_avis', ['id' => $professionnel->getId()]);
        }

        return $this->render('home/professionnel_avis_edit.html.twig', [
            'professionnel' => $professionnel,
            'avis' => $avis,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/patientsdupro', name: 'app_list_patient_professionnel', methods: ['GET', 'POST'])]
    public function listPatients(RendezVousRepository $rendezVousRepository): Response
    {
        // Récupérer le professionnel de santé connecté
        $professionnel = $this->security->getUser();

        if (!$professionnel instanceof ProfessionnelDeSante) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        // Récupérer les rendez-vous pour ce professionnel de santé
        $rendezVous = $rendezVousRepository->findBy(['professionnel' => $professionnel]);

        // Extraire les patients
        $patients = array_map(function ($rv) {
            return $rv->getPatient();
        }, $rendezVous);

        // Supprimer les doublons
        $patients = array_unique($patients, SORT_REGULAR);

        return $this->render('rendez_vous/list_patients.html.twig', [
            'patients' => $patients,
        ]);
    }


    /**
     * Vérifie si une date/heure donnée est dans les horaires de travail du professionnel
     */
    private function estDansHorairesTravail(\DateTimeInterface $dateHeure, ProfessionnelDeSante $professionnel): bool
    {
        $jourSemaine = strtolower($dateHeure->format('l')); // Retourne le jour en anglais (ex: "Monday")
        $heure = $dateHeure->format('H:i:s');

        foreach ($professionnel->getHorairesTravail() as $horaire) {
            // Convertir le jour du français vers l'anglais si nécessaire
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

    /**
     * Convertit les jours français en anglais pour la comparaison
     */
    private function convertirJourFrancaisVersAnglais(string $jourFrancais): string
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

    // Méthodes helper
    private function getHorairesDuJour(ProfessionnelDeSante $professionnel, string $jourSemaine): array
    {
        $horaires = [];
        foreach ($professionnel->getHorairesTravail() as $horaire) {
            $jourHoraire = $this->convertirJourFrancaisVersAnglais($horaire->getJour());
            if (strtolower($jourHoraire) === $jourSemaine) {
                $horaires[] = [
                    'debut' => $horaire->getHeureDebut(),
                    'fin' => $horaire->getHeureFin()
                ];
            }
        }
        return $horaires;
    }

    private function estDansCreneauValide(\DateTimeInterface $dateHeure, array $horairesDuJour): bool
    {
        $heureRdv = $dateHeure->format('H:i:s');
        foreach ($horairesDuJour as $creneau) {
            $heureDebut = $creneau['debut']->format('H:i:s');
            $heureFin = $creneau['fin']->format('H:i:s');
            if ($heureRdv >= $heureDebut && $heureRdv <= $heureFin) {
                return true;
            }
        }
        return false;
    }

    private function estEnIndisponibilite(\DateTimeInterface $dateHeure, ProfessionnelDeSante $professionnel): bool
    {
        foreach ($professionnel->getIndisponibilites() as $indispo) {
            if ($dateHeure >= $indispo->getDateDebutIndispo() && $dateHeure <= $indispo->getDateFinIndispo()) {
                return true;
            }
        }
        return false;
    }
}
