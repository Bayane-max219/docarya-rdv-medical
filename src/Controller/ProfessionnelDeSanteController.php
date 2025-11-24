<?php

namespace App\Controller;

use App\Entity\ProfessionnelDeSante;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\HoraireTravail;
use App\Repository\ProfessionnelDeSanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\ProfessionnelDeSanteRegistrationType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\File\File;
use App\Repository\RendezVousRepository;
use App\Repository\AvisRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[Route('/professionnel')]
class ProfessionnelDeSanteController extends AbstractController
{
    #[Route('/', name: 'app_professionnel_de_sante_index', methods: ['GET'])]
    public function index(Request $request, ProfessionnelDeSanteRepository $professionnelDeSanteRepository): Response
    {
        $specialite = $request->query->get('specialite');
        if ($specialite) {
            $professionnel_de_santes = $professionnelDeSanteRepository->createQueryBuilder('p')
                ->leftJoin('p.specialite', 's')
                ->where('LOWER(s.nom) LIKE :specialite')
                ->setParameter('specialite', '%' . strtolower($specialite) . '%')
                ->getQuery()
                ->getResult();
        } else {
            $professionnel_de_santes = $professionnelDeSanteRepository->findAll();
        }
        return $this->render('professionnel_de_sante/index.html.twig', [
            'professionnel_de_santes' => $professionnel_de_santes,
        ]);
    }
    #[Route('/{id}', name: 'app_professionnel_de_sante_show')]
    public function show(ProfessionnelDeSante $professionnel, RendezVousRepository $rendezVousRepo, AvisRepository $avisRepo): Response
    {
        $nombrePatients = $professionnel->getNombrePatientsUniques($rendezVousRepo);
        $moyenneAvis = $professionnel->getMoyenneAvis($avisRepo);
        $horairesTravail = $professionnel->getHorairesTravail();
        $formattedHoraires = [];
        foreach ($horairesTravail as $horaire) {
            $formattedHoraires[] = [
                'jour' => $horaire->getJour(),
                'heureDebut' => $this->convertDateTimeToString($horaire->getHeureDebut()),
                'heureFin' => $this->convertDateTimeToString($horaire->getHeureFin()),
            ];
        }
        return $this->render('professionnel_de_sante/show.html.twig', [
            'professionnel' => $professionnel,
            'horairesTravail' => $formattedHoraires,
            'nombrePatients' => $nombrePatients,
            'moyenneAvis' => $moyenneAvis,
        ]);
    }
    #[Route('/{id}/edit', name: 'app_professionnel_de_sante_edit')]
    public function edit(
        ProfessionnelDeSante $professionnel,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // On conserve l'ancien nom de fichier au cas où l'utilisateur ne change pas la photo
        $ancienFichier = $professionnel->getPhoto();

        // Si une photo existe déjà, on crée un objet File pour le formulaire
        if ($ancienFichier) {
            $professionnel->setPhoto(
                new File($this->getParameter('professionnels_directory') . '/' . $ancienFichier)
            );
        }
        $form = $this->createForm(ProfessionnelDeSanteRegistrationType::class, $professionnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('horairesJson')) {
                $horairesJson = $form->get('horairesJson')->getData();
                if ($horairesJson) {
                    $horairesData = json_decode($horairesJson, true);
                    // Effacer les horaires existants
                    $professionnel->getHorairesTravail()->clear();
                    foreach ($horairesData as $horaireData) {
                        $horaire = new HoraireTravail();
                        $horaire->setJour($horaireData['jour']);
                        $horaire->setHeureDebut($this->convertTimeStringToDateTime($horaireData['heureDebut']));
                        $horaire->setHeureFin($this->convertTimeStringToDateTime($horaireData['heureFin']));
                        $professionnel->addHoraireTravail($horaire);
                    }
                }
            }
            // Hasher le mot de passe s'il a été modifié
            $plainPassword = $professionnel->getMotDePasse();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($professionnel, $plainPassword);
                $professionnel->setMotDePasse($hashedPassword);
            }

            $photoFile = $form->get('photo')->getData();
            // Si un nouveau photo est uploadé
            if ($photoFile) {
                // Génération d'un nom de photo unique (même méthode que dans new())
                $newFilename = $this->generateUniqueFileName() . '.' . $photoFile->guessExtension();
                // Déplacement du photo
                $photoFile->move(
                    $this->getParameter('professionnels_directory'),
                    $newFilename
                );

                // Redimensionnement (si nécessaire)
                $this->resizeAndCropImage($this->getParameter('professionnels_directory') . '/' . $newFilename);

                // Suppression de l'ancien photo s'il existe
                if ($ancienFichier) {
                    $ancienFichierPath = $this->getParameter('professionnels_directory') . '/' . $ancienFichier;
                    if (file_exists($ancienFichierPath)) {
                        unlink($ancienFichierPath);
                    }
                }

                $professionnel->setPhoto($newFilename);
            } else {
                // Si aucun nouveau photo n'est uploadé, on conserve l'ancien
                $professionnel->setPhoto($ancienFichier);
            }

            // Sauvegarder les modifications en base de données
            $entityManager->flush();
            $this->addFlash('success', 'Modifications enregistrées avec succès.');
            return $this->redirectToRoute('app_professionnel_de_sante_show', ['id' => $professionnel->getId()]);
        }

        // Préparer les horaires existants pour le formulaire
        $horairesExistant = [];
        foreach ($professionnel->getHorairesTravail() as $horaire) {
            $horairesExistant[] = [
                'jour' => $horaire->getJour(),
                'heureDebut' => $this->convertDateTimeToString($horaire->getHeureDebut()),
                'heureFin' => $this->convertDateTimeToString($horaire->getHeureFin()),
            ];
        }

        return $this->render('professionnel_de_sante/edit.html.twig', [
            'form' => $form->createView(),
            'horairesExistant' => $horairesExistant,
        ]);
    }

    #[Route('/{id}', name: 'app_professionnel_de_sante_delete', methods: ['POST'])]
    public function delete(Request $request, ProfessionnelDeSante $professionnelDeSante, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $professionnelDeSante->getId(), $request->request->get('_token'))) {
            $entityManager->remove($professionnelDeSante);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_professionnel_de_sante_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/validate', name: 'app_professionnel_de_sante_validate', methods: ['POST'])]
    public function validate(
        Request $request,
        ProfessionnelDeSante $professionnelDeSante,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('validate' . $professionnelDeSante->getId(), $request->request->get('_token'))) {
                // Validation du compte
                $professionnelDeSante->setIsVerified(true);
                $entityManager->flush();

                // Envoi de l'email de confirmation
                $email = (new Email())
                    ->from('baymi312@gmail.com')
                    ->to($professionnelDeSante->getEmail())
                    ->subject('Validation de votre compte professionnel')
                    ->html($this->renderView('emails/professionnel_validate.html.twig', [
                        'professionnel' => $professionnelDeSante
                    ]));
                try {
                    $mailer->send($email);
                } catch (TransportExceptionInterface $e) {
                    $this->addFlash('warning', "Le compte a été validé, mais l'email de confirmation n'a pas pu être envoyé.");
                }

                $this->addFlash('success', 'Le compte a été validé et l\'agenda initialisé.');
                return $this->redirectToRoute('app_professionnel_de_sante_index');
            }
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->render('professionnel_de_sante/validate.html.twig', [
            'professionnel_de_sante' => $professionnelDeSante,
        ]);
    }
    #[Route('/{id}/confirm-from-email', name: 'app_professionnel_de_sante_confirm_email', methods: ['GET'])]
    public function confirmFromEmail(
        ProfessionnelDeSante $professionnelDeSante,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if (!$professionnelDeSante->isVerified()) {
            $professionnelDeSante->setIsVerified(true);
            $entityManager->flush();

            $email = (new Email())
                ->from('baymi312@gmail.com')
                ->to($professionnelDeSante->getEmail())
                ->subject('Validation de votre compte professionnel')
                ->html($this->renderView('emails/professionnel_validate.html.twig', [
                    'professionnel' => $professionnelDeSante
                ]));
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('warning', "Le compte a été validé, mais l'email de confirmation n'a pas pu être envoyé.");
            }
        }

        $this->addFlash('success', 'Le compte du professionnel a été validé. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
    #[Route('/uploads/professionnel/resized/{filename}', name: 'app_professionnel_de_sante_photo_resized')]
    public function getResizedPhoto($filename): Response
    {
        $photosDirectory = $this->getParameter('professionnels_directory');
        $resizedDirectory = $this->getParameter('professionnels_resized_directory');
        $originalPath = $photosDirectory . '/' . $filename;

        if (!file_exists($originalPath)) {
            throw $this->createNotFoundException('Image not found');
        }

        // Redimensionner l'image à la volée
        $resizedPath = $resizedDirectory . '/' . pathinfo($filename, PATHINFO_FILENAME) . '_resized.' . pathinfo($filename, PATHINFO_EXTENSION);

        // Si l'image redimensionnée n'existe pas, la créer
        if (!file_exists($resizedPath)) {
            $this->resizeAndCropImage($originalPath);
        }

        // Retourner l'image redimensionnée en réponse
        return $this->file($resizedPath);
    }
    /**
     * Convertit une chaîne de temps au format "8h00" en un objet DateTime
     *
     * @param string $timeString Chaîne de temps au format "8h00"
     * @return \DateTimeInterface Objet DateTime
     */
    private function convertTimeStringToDateTime(string $timeString): \DateTimeInterface
    {
        // Supprimer le "h" de la chaîne de temps
        $timeString = str_replace('h', ':', $timeString);
        // Créer un objet DateTime à partir de la chaîne de temps
        return \DateTime::createFromFormat('H:i', $timeString);
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
    private function generateUniqueFileName(): string
    {
        return sprintf(
            '%s%s_%s',
            substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4),
            str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
            (new \DateTime())->format('Ymd_His')
        );
    }
    private function resizeAndCropImage($originalPath): string
    {
        ini_set('memory_limit', '512M');
        $targetWidth = 800;
        $targetHeight = 800;
        // Récupérer les dimensions de l'image originale
        list($originalWidth, $originalHeight, $type) = getimagesize($originalPath);
        $originalRatio = $originalWidth / $originalHeight;
        // Créer une image carrée avec un fond blanc si nécessaire
        if ($originalWidth !== $originalHeight) {
            $squareSize = max($originalWidth, $originalHeight);
            $squareImage = imagecreatetruecolor($squareSize, $squareSize);
            // Remplir le fond avec du blanc
            $white = imagecolorallocate($squareImage, 255, 255, 255);
            imagefill($squareImage, 0, 0, $white);
            // Charger l'image originale
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $originalImage = imagecreatefromjpeg($originalPath);
                    break;
                case IMAGETYPE_PNG:
                    $originalImage = imagecreatefrompng($originalPath);
                    break;
                case IMAGETYPE_GIF:
                    $originalImage = imagecreatefromgif($originalPath);
                    break;
                default:
                    throw new \Exception("Type d'image non supporté");
            }
            // Calculer les offsets pour centrer l'image dans le carré
            $xOffset = ($originalWidth < $squareSize) ? intval(($squareSize - $originalWidth) / 2) : 0;
            $yOffset = ($originalHeight < $squareSize) ? intval(($squareSize - $originalHeight) / 2) : 0;
            // Copier l'image originale sur le fond blanc
            imagecopy($squareImage, $originalImage, $xOffset, $yOffset, 0, 0, $originalWidth, $originalHeight);
            // Libérer la ressource de l'image originale
            imagedestroy($originalImage);
            // Redéfinir les dimensions comme étant celles du carré
            $originalWidth = $squareSize;
            $originalHeight = $squareSize;
            $originalImage = $squareImage;
        } else {
            // Si l'image est déjà carrée, charger directement l'image originale
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $originalImage = imagecreatefromjpeg($originalPath);
                    break;
                case IMAGETYPE_PNG:
                    $originalImage = imagecreatefrompng($originalPath);
                    // Ajouter un fond blanc pour les images PNG
                    $whiteBackground = imagecreatetruecolor($originalWidth, $originalHeight);
                    $white = imagecolorallocate($whiteBackground, 255, 255, 255);
                    imagefill($whiteBackground, 0, 0, $white);
                    // Copier l'image PNG sur le fond blanc (ignorer la transparence)
                    imagecopy($whiteBackground, $originalImage, 0, 0, 0, 0, $originalWidth, $originalHeight);
                    // Libérer la ressource de l'image originale
                    imagedestroy($originalImage);
                    // Utiliser le fond blanc comme nouvelle image originale
                    $originalImage = $whiteBackground;
                    break;
                case IMAGETYPE_GIF:
                    $originalImage = imagecreatefromgif($originalPath);
                    break;
                default:
                    throw new \Exception("Type d'image non supporté");
            }
        }
        // Redimensionner l'image carrée vers la taille cible (800x800)
        $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
        // Remplir le fond avec du blanc
        $white = imagecolorallocate($resizedImage, 255, 255, 255);
        imagefill($resizedImage, 0, 0, $white);
        // Redimensionner l'image
        imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
        // Sauvegarder l'image redimensionnée
        $pathInfo = pathinfo($originalPath);
        $croppedPath = $this->getParameter('professionnels_resized_directory') . '/' . $pathInfo['filename'] . '_resized.' . $pathInfo['extension'];
        if (!is_dir($this->getParameter('professionnels_resized_directory'))) {
            mkdir($this->getParameter('professionnels_resized_directory'), 0777, true);
        }
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($resizedImage, $croppedPath, 90);
                break;
            case IMAGETYPE_PNG:
                // Ignorer la transparence pour les images PNG
                imagepng($resizedImage, $croppedPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($resizedImage, $croppedPath);
                break;
        }
        // Libérer les ressources
        imagedestroy($originalImage);
        imagedestroy($resizedImage);
        return $croppedPath;
    }
}
