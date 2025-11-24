<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Specialite;
use App\Entity\HoraireTravail;
use App\Form\RoleChoiceType;
use App\Form\PatientRegistrationType;
use App\Form\ProfessionnelDeSanteRegistrationType;
use App\Form\AdministrateurRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        // Étape 1 : Choix du rôle
        $roleForm = $this->createForm(RoleChoiceType::class);
        $roleForm->handleRequest($request);

        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            $data = $roleForm->getData();
            $selectedRole = $data['role'];

            // Rediriger vers l'étape 2 en fonction du rôle
            return $this->redirectToRoute('app_register_step2', ['role' => $selectedRole]);
        }

        return $this->render('registration/step1_role_choice.html.twig', [
            'roleForm' => $roleForm->createView(),
        ]);
    }
    #[Route('/register/{role}', name: 'app_register_step2')]
    public function registerStep2(
        string $role,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        // Instancier l'entité appropriée en fonction du rôle
        switch ($role) {
            case 'patient':
                $user = new \App\Entity\Patient();
                $form = $this->createForm(PatientRegistrationType::class, $user);
                break;
            case 'professionnel_de_sante':
                $user = new \App\Entity\ProfessionnelDeSante();
                $form = $this->createForm(ProfessionnelDeSanteRegistrationType::class, $user);
                break;
            case 'administrateur':
                $user = new \App\Entity\Administrateur();
                $form = $this->createForm(AdministrateurRegistrationType::class, $user);
                break;
            default:
                throw $this->createNotFoundException('Rôle invalide.');
        }

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('motDePasse')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setMotDePasse($hashedPassword);

            // Gestion de la photo pour les professionnels de santé
            if ($role === 'professionnel_de_sante') {
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    $newFilename = $this->generateUniqueFileName() . '.' . $photoFile->guessExtension();

                    // Déplacer le fichier dans le répertoire
                    $photoFile->move(
                        $this->getParameter('professionnels_directory'),
                        $newFilename
                    );
                    // Redimensionner et recadrer l'image
                    $this->resizeAndCropImage($this->getParameter('professionnels_directory') . '/' . $newFilename);

                    $user->setPhoto($newFilename);
                }
            }

            // Gestion des horaires de travail pour les professionnels de santé
            if ($role === 'professionnel_de_sante' && $form->has('horairesJson')) {
                $horairesJson = $form->get('horairesJson')->getData();
                if ($horairesJson) {
                    $horairesData = json_decode($horairesJson, true);
                    foreach ($horairesData as $horaireData) {
                        $horaire = new HoraireTravail();
                        $horaire->setJour($horaireData['jour']);
                        $horaire->setHeureDebut($this->convertTimeStringToDateTime($horaireData['heureDebut']));
                        $horaire->setHeureFin($this->convertTimeStringToDateTime($horaireData['heureFin']));
                        $user->addHoraireTravail($horaire);
                    }
                }
            }

            // Définir le rôle et le statut de vérification
            switch ($role) {
                case 'patient':
                    $user->setRole('ROLE_PATIENT');
                    $user->setIsVerified(true); // Patients vérifiés automatiquement
                    break;
                case 'professionnel_de_sante':
                    $user->setRole('ROLE_PROFESSIONNEL_DE_SANTE');
                    $user->setIsVerified(false); // Professionnels non vérifiés par défaut
                    break;
                case 'administrateur':
                    $user->setRole('ROLE_ADMINISTRATEUR');
                    $user->setIsVerified(false); // Admins non vérifiés par défaut
                    break;
            }

            // Sauvegarder l'utilisateur en base de données
            try {
                $entityManager->persist($user);
                $entityManager->flush();

                // Notification à l'administrateur lorsqu'un professionnel de santé s'inscrit
                if ($role === 'professionnel_de_sante') {
                    $adminEmail = 'baymi312@gmail.com';
                    $emailAdmin = (new Email())
                        ->from('baymi312@gmail.com')
                        ->to($adminEmail)
                        ->subject('Nouveau professionnel de santé à valider')
                        ->html($this->renderView('emails/professionnel_admin_request.html.twig', [
                            'professionnel' => $user,
                        ]));
                    try {
                        $mailer->send($emailAdmin);
                    } catch (TransportExceptionInterface $e) {
                        $this->addFlash('warning', "Le compte professionnel a été créé, mais l'email de notification administrateur n'a pas pu être envoyé.");
                    }
                }
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Un compte avec cet email existe déjà.');
                return $this->redirectToRoute('app_register');
            }

            // Message différent selon le statut de vérification
            if ($user->isVerified()) {
                if ($role == 'patient') {
                    // Envoi de l'email de confirmation
                    $email = (new Email())
                        ->from('baymi312@gmail.com')
                        ->to($form->get('email')->getData())
                        ->subject('Validation de votre compte sur Docarya')
                        ->html($this->renderView('emails/patient_register.html.twig'));

                    try {
                        $mailer->send($email);
                    } catch (TransportExceptionInterface $e) {
                        $this->addFlash('warning', "Votre compte a été créé, mais l'email de confirmation n'a pas pu être envoyé.");
                    }
                }
                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            } else {
                $this->addFlash('warning', 'Votre compte est en attente de validation par un administrateur. Vous recevrez un email une fois votre compte validé.');
            }

            // Redirection vers la page appropriée
            return $user->isVerified()
                ? $this->redirectToRoute('app_login')
                : $this->redirectToRoute('app_pending_verification');
        }

        return $this->render('registration/step2_form.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
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
}
