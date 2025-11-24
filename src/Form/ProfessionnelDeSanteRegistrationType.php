<?php

namespace App\Form;

use App\Entity\ProfessionnelDeSante;
use App\Entity\Specialite;
use App\Repository\SpecialiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessionnelDeSanteRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('adresse', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])
            ->add('telephone', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])

            ->add('email', EmailType::class, [ // Changed from TextType
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez saisir un email valide']),
                ],
            ])
            ->add('motDePasse', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'password-field',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Votre mot de passe est requis pour enregistrer les informations']),
                ],
            ])
            ->add('specialite', EntityType::class, [
                'label' => 'Spécialité',
                'class' => Specialite::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une spécialité',
                'query_builder' => function (SpecialiteRepository $er) {
                    return $er->createQueryBuilder('s');
                },
            ])
            ->add('tarif', NumberType::class, [
                'label' => 'Tarif (en €)',
                'scale' => 2,
            ])
            // Champ caché pour les données JSON
            ->add('horairesJson', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfessionnelDeSante::class,
        ]);
    }
}
