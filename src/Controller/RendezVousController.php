<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Repository\RendezVousRepository;
use App\Entity\ProfessionnelDeSante;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/rendez-vous')]
class RendezVousController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    #[Route('/', name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $rendezVous = $rendezVousRepository->findForUser($user);

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVous,
        ]);
    }


    #[Route('/{id}/confirmer', name: 'app_rendez_vous_confirm', methods: ['POST'])]
    public function confirm(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();

        // Vérification que l'utilisateur est bien le professionnel concerné
        if (!$this->isGranted('ROLE_PROFESSIONNEL_DE_SANTE') || $rendezVous->getProfessionnel() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas confirmer ce rendez-vous');
        }
        if ($rendezVous->getStatut() === 'annule') {
            $this->addFlash('warning', 'Impossible de confirmer un rendez-vous annulé');
            return $this->redirectToRoute('app_rendez_vous_index');
        }
        if ($this->isCsrfTokenValid('confirm' . $rendezVous->getId(), $request->request->get('_token'))) {
            $rendezVous->setStatut('confirmé');
            $entityManager->persist($rendezVous);
            $entityManager->flush();

            $this->addFlash('success', 'Le rendez-vous a été confirmé avec succès.');
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $user = $this->security->getUser();

        // Vérification que l'utilisateur a le droit d'annuler ce rendez-vous
        if ($this->isGranted('ROLE_PATIENT') && $rendezVou->getPatient() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler ce rendez-vous');
        }

        if ($this->isGranted('ROLE_PROFESSIONNEL_DE_SANTE') && $rendezVou->getProfessionnel() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler ce rendez-vous');
        }

        if ($this->isCsrfTokenValid('delete' . $rendezVou->getId(), $request->request->get('_token'))) {
            // Au lieu de supprimer, on change le statut
            $rendezVou->setStatut('annule');
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            $this->addFlash('success', 'Le rendez-vous a été annulé avec succès.');
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
}
