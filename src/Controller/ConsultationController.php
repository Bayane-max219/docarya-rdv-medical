<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\DemandeAccesHistorique;
use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use App\Entity\RendezVous;
use App\Form\ConsultationType;
use App\Form\DemandeAccesHistoriqueType;
use App\Repository\ConsultationRepository;
use App\Repository\DemandeAccesHistoriqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/consultation')]
class ConsultationController extends AbstractController
{
    #[Route('/', name: 'app_consultation_index', methods: ['GET'])]
    public function index(ConsultationRepository $consultationRepository): Response
    {
        $user = $this->getUser();
        $consultations = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            // Si l'utilisateur est admin, on récupère toutes les consultations
            $consultations = $consultationRepository->findAll();
        } elseif ($user instanceof Patient) {
            $consultations = $consultationRepository->findByPatient($user);
        } elseif ($user instanceof ProfessionnelDeSante) {
            $consultations = $consultationRepository->findSharedWithProfessionnel($user);
        }

        return $this->render('consultation/index.html.twig', [
            'consultations' => $consultations,
        ]);
    }
    #[Route('/new/{rendezVousId}', name: 'app_consultation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $rendezVousId): Response
    {
        $rendezVous = $entityManager->getRepository(RendezVous::class)->find($rendezVousId);

        if (!$rendezVous || $rendezVous->getStatut() !== 'confirmé') {
            throw $this->createNotFoundException('Rendez-vous non trouvé ou non confirmé');
        }

        // Vérifie si une consultation existe déjà pour ce rendez-vous
        if ($rendezVous->getConsultation() !== null) {
            $this->addFlash('warning', 'Une consultation existe déjà pour ce rendez-vous');
            return $this->redirectToRoute('app_consultation_show', ['id' => $rendezVous->getConsultation()->getId()]);
        }

        // Vérification que l'utilisateur est le professionnel associé au rendez-vous
        $user = $this->getUser();
        if (!$user instanceof ProfessionnelDeSante || $user !== $rendezVous->getProfessionnel()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à créer une consultation pour ce rendez-vous');
        }

        $consultation = new Consultation();
        $consultation->setRendezVous($rendezVous);
        // On ne définit plus la date ici
        $consultation->addProfessionnelAutorise($user);
        $consultation->setPrix($user->getTarif()); // Définit le prix par défaut

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définit la date au moment de la soumission
            $consultation->setDate(new \DateTimeImmutable());

            // Récupère les données du formulaire
            $ordonnancesData = $form->get('ordonnances')->getData();

            // Formatte les données comme vous le souhaitez
            $formattedOrdonnances = [];
            foreach ($ordonnancesData as $item) {
                if (!empty($item['medicament'])) {
                    $formattedOrdonnances[] = [
                        'medicament' => $item['medicament'],
                        'dose' => $item['dose'],
                        'prise' => $item['prise']
                    ];
                }
            }

            // Assigne les données formatées à l'entité
            $consultation->setOrdonnances($formattedOrdonnances);

            // Persiste la consultation
            $entityManager->persist($consultation);

            // Met à jour le rendez-vous
            $rendezVous->setConsultation($consultation);
            $rendezVous->setStatut('terminé'); // Mise à jour du statut

            $entityManager->flush();

            $this->addFlash('success', 'La consultation a été créée avec succès et le rendez-vous est marqué comme terminé');
            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        return $this->render('consultation/new.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'app_consultation_show', methods: ['GET'])]
    public function show(Consultation $consultation): Response
    {
        $this->checkViewAccess($consultation);

        return $this->render('consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consultation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        $this->checkEditAccess($consultation);

        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire
            $ordonnancesData = $form->get('ordonnances')->getData();

            // Formatte les données comme vous le souhaitez
            $formattedOrdonnances = [];
            foreach ($ordonnancesData as $item) {
                if (!empty($item['medicament'])) {
                    $formattedOrdonnances[] = [
                        'medicament' => $item['medicament'],
                        'dose' => $item['dose'],
                        'prise' => $item['prise']
                    ];
                }
            }

            // Assigne les données formatées à l'entité
            $consultation->setOrdonnances($formattedOrdonnances);

            $entityManager->flush();

            return $this->redirectToRoute('app_consultation_show', ['id' => $consultation->getId()]);
        }

        return $this->render('consultation/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form->createView(),
        ]);
    }
    // Dans ConsultationController.php

    #[Route('/{id}/toggle-share', name: 'app_consultation_toggle_share', methods: ['POST'])]
    public function toggleShareCarnet(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        $shareStatus = $request->request->getBoolean('share');
        $patient->setCarnetPartage($shareStatus);

        $entityManager->flush();

        $this->addFlash('success', $shareStatus ?
            'Votre carnet de santé est maintenant partagé avec les professionnels autorisés' :
            'Votre carnet de santé est maintenant privé');

        return $this->redirectToRoute('app_carnet_sante_index');
    }

    #[Route('/{id}/manage-access', name: 'app_consultation_manage_access')]
    public function manageAccess(Patient $patient): Response
    {
        $this->denyAccessUnlessGranted('edit', $patient);

        return $this->render('carnet_sante/manage_access.html.twig', [
            'patient' => $patient,
        ]);
    }

    // Méthodes de vérification d'accès intégrées au contrôleur
    private function checkViewAccess(Consultation $consultation): void
    {
        if (!$this->canView($consultation, $this->getUser())) {
            throw $this->createAccessDeniedException('Accès refusé à cette consultation');
        }
    }

    private function checkEditAccess(Consultation $consultation): void
    {
        if (!$this->canEdit($consultation, $this->getUser())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette consultation');
        }
    }

    private function canView(Consultation $consultation, ?UserInterface $user): bool
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Le patient peut toujours voir ses propres consultations
        if ($user instanceof Patient && $user === $consultation->getRendezVous()->getPatient()) {
            return true;
        }

        // Le professionnel peut voir si autorisé
        if ($user instanceof ProfessionnelDeSante) {
            return $consultation->getProfessionnelsAutorises()->contains($user)
                || $user === $consultation->getRendezVous()->getProfessionnel();
        }

        return false;
    }

    private function canEdit(Consultation $consultation, ?UserInterface $user): bool
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Seul le professionnel qui a créé la consultation peut l'éditer
        return $user instanceof ProfessionnelDeSante
            && $user === $consultation->getRendezVous()->getProfessionnel();
    }
}
