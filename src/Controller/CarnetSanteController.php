<?php
// src/Controller/CarnetSanteController.php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\ProfessionnelDeSante;
use App\Form\ShareCarnetType;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/carnet-sante')]
class CarnetSanteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'app_carnet_sante_index')]
    public function index(ConsultationRepository $consultationRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Patient) {
            throw $this->createAccessDeniedException('Seuls les patients peuvent accéder à leur carnet de santé');
        }

        $consultations = $consultationRepository->findByPatient($user);

        return $this->render('carnet_sante/index.html.twig', [
            'consultations' => $consultations,
            'patient' => $user,
        ]);
    }

    #[Route('/patient/{id}', name: 'app_carnet_sante_view')]
    public function viewPatient(Patient $patient, ConsultationRepository $consultationRepository): Response
    {
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            if ($user !== $patient) {
                if (
                    !$user instanceof ProfessionnelDeSante ||
                    !$patient->isCarnetPartage() ||
                    !$patient->getProfessionnelsAutorisesCarnet()->contains($user)
                ) {
                    throw $this->createAccessDeniedException('Accès non autorisé à ce carnet de santé');
                }
            }
        }

        $consultations = $consultationRepository->findByPatient($patient);

        return $this->render('carnet_sante/view.html.twig', [
            'consultations' => $consultations,
            'patient' => $patient,
            'is_owner' => $user === $patient,
        ]);
    }

    #[Route('/toggle-share', name: 'app_carnet_sante_toggle_share', methods: ['POST'])]
    public function toggleShare(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Patient) {
            throw $this->createAccessDeniedException();
        }

        $shareStatus = $request->request->getBoolean('share');
        $user->setCarnetPartage($shareStatus);

        $this->em->flush();

        $this->addFlash('success', $shareStatus ?
            'Votre carnet de santé est maintenant partagé' :
            'Votre carnet de santé est maintenant privé');

        return $this->redirectToRoute('app_consultation_index');
    }

    #[Route('/manage-access', name: 'app_carnet_sante_manage')]
    public function manageAccess(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Patient) {
            throw $this->createAccessDeniedException();
        }

        // Créez le formulaire avec les professionnels actuellement autorisés
        $form = $this->createForm(ShareCarnetType::class, null, [
            'currentAuthorized' => $user->getProfessionnelsAutorisesCarnet(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedPros = $form->get('professionnels')->getData();

            // Mise à jour des professionnels autorisés
            $user->getProfessionnelsAutorisesCarnet()->clear();
            foreach ($selectedPros as $pro) {
                $user->addProfessionnelAutoriseCarnet($pro);
            }

            $this->em->flush();
            $this->addFlash('success', 'Les autorisations ont été mises à jour');
            return $this->redirectToRoute('app_carnet_sante_index');
        }

        return $this->render('carnet_sante/manage_access.html.twig', [
            'form' => $form->createView(),
            'patient' => $user,
        ]);
    }
}
