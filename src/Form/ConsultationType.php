<?php

namespace App\Form;

use App\Entity\Consultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control datetimepicker'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes de consultation',
                'required' => false,
                'attr' => ['rows' => 10, 'class' => 'form-control'],
            ])
            // Dans votre ConsultationType.php
            ->add('ordonnances', CollectionType::class, [
                'label' => 'Ordonnances',
                'entry_type' => OrdonnanceType::class, // Créez ce nouveau FormType
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'required' => false,
                'attr' => [
                    'class' => 'ordonnances-collection',
                ],
            ])
            // Dans App\Form\ConsultationType
            ->add('prix', NumberType::class, [
                'label' => 'Prix de la consultation',
                'attr' => [
                    'step' => '0.5'
                ],
                'html5' => true
            ])
            ->add('partageAutorise', null, [
                'label' => 'Autoriser le partage avec d\'autres professionnels',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
