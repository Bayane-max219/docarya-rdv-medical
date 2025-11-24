<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class PatientRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])
            ->add('prenom', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'email'
                ],
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'email est obligatoire']),
                    new Email(['message' => 'Veuillez saisir un email valide']),
                ],
            ])
            ->add('adresse', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])
            ->add('telephone', TextType::class, ['attr' => ['class' => 'form-control',], 'label_attr' => ['class' => 'form-label'],])
            ->add('motDePasse', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password', // Sécurité supplémentaire
                    'class' => 'password-field', // Classe CSS pour le style
                ],
            ])
            ->add('antecedentsMedicaux', TextareaType::class, ['required' => false, 'attr' => ['class' => 'form-control',],])
            ->add('maladiesChroniques', ChoiceType::class, [
                'choices' => [
                    'Asthme' => 'asthme',
                    'Diabète' => 'diabète',
                    'Hypertension' => 'hypertension',
                ],
                'multiple' => true,
                'expanded' => true, // Cases à cocher
                'label' => 'Maladies chroniques',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Patient::class,
        ]);
    }
}
