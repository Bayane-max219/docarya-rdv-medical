<?php

namespace App\Form;

use App\Entity\GestionAgenda;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class GestionAgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebutIndispo', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'datetime-picker']
            ])
            ->add('dateFinIndispo', DateTimeType::class, [
                'label' => 'Date et heure de fin',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'datetime-picker']
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de l\'indisponibilité',
                'required' => false,
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GestionAgenda::class,
        ]);
    }
}
