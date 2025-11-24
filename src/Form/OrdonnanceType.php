<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicament', TextType::class, [
                'label' => 'Médicament',
                'attr' => ['class' => 'form-control mb-2']
            ])
            ->add('dose', TextType::class, [
                'label' => 'Dose par prise',
                'attr' => ['class' => 'form-control mb-2'],
            ])
            ->add('prise', ChoiceType::class, [
                'label' => 'Prise',
                'choices' => [
                    'Matin' => 'matin',
                    'Midi' => 'midi',
                    'Soir' => 'soir'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas de classe associée car nous utilisons un tableau
        ]);
    }
}
