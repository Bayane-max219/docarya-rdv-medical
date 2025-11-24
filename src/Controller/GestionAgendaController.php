<?php

namespace App\Controller;

use App\Entity\GestionAgenda;
use App\Entity\ProfessionnelDeSante;
use App\Form\GestionAgendaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/gestion-agenda')]
class GestionAgendaController extends AbstractController
{
    #[Route('/', name: 'app_gestion_agenda_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            // Admin voit toutes les indisponibilités
            $indisponibilites = $entityManager
                ->getRepository(GestionAgenda::class)
                ->findAll();
        } else {
            // Professionnel ne voit que ses propres indisponibilités
            if (!$this->isGranted('ROLE_PROFESSIONNEL_DE_SANTE')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette page.');
            }
            /** @var ProfessionnelDeSante $professionnel */
            $professionnel = $user;
            $indisponibilites = $professionnel->getIndisponibilites();
        }
        return $this->render('gestion_agenda/index.html.twig', [
            'gestion_agendas' => $indisponibilites,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
        ]);
    }

    #[Route('/new', name: 'app_gestion_agenda_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Vérification que l'utilisateur est un professionnel de santé
        if (!$this->isGranted('ROLE_PROFESSIONNEL_DE_SANTE')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette page.');
        }
        /** @var ProfessionnelDeSante $professionnel */
        $professionnel = $user;
        $gestionAgenda = new GestionAgenda();
        $gestionAgenda->setProfessionnel($professionnel);
        $form = $this->createForm(GestionAgendaType::class, $gestionAgenda);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des dates
            $errors = $this->validateDates($gestionAgenda);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('app_gestion_agenda_new');
            }
            $entityManager->persist($gestionAgenda);
            $entityManager->flush();
            $this->addFlash('success', 'L\'indisponibilité a été ajoutée avec succès.');
            return $this->redirectToRoute('app_gestion_agenda_index');
        }
        return $this->render('gestion_agenda/new.html.twig', [
            'gestion_agenda' => $gestionAgenda,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_gestion_agenda_show', methods: ['GET'])]
    public function show(GestionAgenda $gestionAgenda): Response
    {
        // Vérification que l'utilisateur a le droit de voir cette indisponibilité
        $this->checkAccess($gestionAgenda);
        return $this->render('gestion_agenda/show.html.twig', [
            'gestion_agenda' => $gestionAgenda,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestion_agenda_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        GestionAgenda $gestionAgenda,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification que l'utilisateur a le droit de modifier cette indisponibilité
        $this->checkAccess($gestionAgenda);
        $form = $this->createForm(GestionAgendaType::class, $gestionAgenda);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des dates
            $errors = $this->validateDates($gestionAgenda);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->redirectToRoute('app_gestion_agenda_edit', ['id' => $gestionAgenda->getId()]);
            }
            $entityManager->flush();
            $this->addFlash('success', 'L\'indisponibilité a été modifiée avec succès.');
            return $this->redirectToRoute('app_gestion_agenda_index');
        }
        return $this->render('gestion_agenda/edit.html.twig', [
            'gestion_agenda' => $gestionAgenda,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_gestion_agenda_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        GestionAgenda $gestionAgenda,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification que l'utilisateur a le droit de supprimer cette indisponibilité
        $this->checkAccess($gestionAgenda);
        if ($this->isCsrfTokenValid('delete' . $gestionAgenda->getId(), $request->request->get('_token'))) {
            $entityManager->remove($gestionAgenda);
            $entityManager->flush();
            $this->addFlash('success', 'L\'indisponibilité a été supprimée avec succès.');
        }
        return $this->redirectToRoute('app_gestion_agenda_index');
    }

    /**
     * Vérifie que l'utilisateur a le droit d'accéder à cette indisponibilité
     * @throws AccessDeniedException
     */
    private function checkAccess(GestionAgenda $gestionAgenda): void
    {
        $user = $this->getUser();
        // L'admin a toujours accès
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }
        // Pour les professionnels, on vérifie que c'est bien leur indisponibilité
        if (!$this->isGranted('ROLE_PROFESSIONNEL_DE_SANTE') || $gestionAgenda->getProfessionnel() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette indisponibilité.');
        }
    }

    /**
     * Vérifie que les dates sont valides
     * @param GestionAgenda $gestionAgenda
     * @return array Tableau d'erreurs
     */
    private function validateDates(GestionAgenda $gestionAgenda): array
    {
        $errors = [];
        $now = new \DateTime();

        // Vérification que la date de début est dans le futur
        if ($gestionAgenda->getDateDebutIndispo() <= $now) {
            $errors[] = 'La date de début doit être dans le futur.';
        }

        // Vérification que la date de fin est postérieure à la date de début
        if ($gestionAgenda->getDateDebutIndispo() >= $gestionAgenda->getDateFinIndispo()) {
            $errors[] = 'La date de fin doit être postérieure à la date de début.';
        }

        return $errors;
    }
}
