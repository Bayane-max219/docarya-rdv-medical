<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\ProfessionnelDeSante;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/avis')]
class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AvisRepository $avisRepository
    ) {}
    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $avis = $this->avisRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );

        return $this->render('avis/index.html.twig', [
            'avis' => $avis,
            'page' => $page,
            'limit' => $limit,
        ]);
    }
    #[Route('/new', name: 'app_avis_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $avis = new Avis();

        // Associe automatiquement le patient connecté s'il existe
        if ($this->getUser() && method_exists($this->getUser(), 'getId')) {
            $avis->setPatient($this->getUser());
        }

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($avis);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre avis a été enregistré avec succès.');
            return $this->redirectToRoute('app_avis_index');
        }

        return $this->render('avis/new.html.twig', [
            'avis' => $avis,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avis): Response
    {
        return $this->render('avis/show.html.twig', [
            'avis' => $avis,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avis): Response
    {
        // Vérification supplémentaire de sécurité
        if (!$this->isGranted('ROLE_ADMIN') && $avis->getPatient() !== $this->getUser()) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier cet avis.');
        }

        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'avis a été mis à jour.');

            return $this->redirectToRoute('app_home_user');
        }

        return $this->render('avis/edit.html.twig', [
            'avis' => $avis,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avis): Response
    {
        if ($this->isCsrfTokenValid('delete' . $avis->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($avis);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'avis a été supprimé.');
        }

        return $this->redirectToRoute('app_avis_index');
    }

    #[Route('/professionnel/{id}', name: 'app_avis_professionnel', methods: ['GET'])]
    public function byProfessional(ProfessionnelDeSante $professionnel): Response
    {
        $avis = $this->avisRepository->findBy(
            ['professionnel' => $professionnel],
            ['createdAt' => 'DESC']
        );

        return $this->render('avis/by_professional.html.twig', [
            'avis' => $avis,
            'professionnel' => $professionnel,
        ]);
    }
}
