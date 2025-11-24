<?php
// Dans App\Form\ShareCarnetType.php
namespace App\Form;

use App\Entity\ProfessionnelDeSante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\ProfessionnelDeSanteRepository;


class ShareCarnetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('professionnels', EntityType::class, [
            'class' => ProfessionnelDeSante::class,
            'choice_label' => 'nom', // Remplacez par le champ à afficher (ex: nom + prénom)
            'multiple' => true,
            'expanded' => true, // Case à cocher pour chaque professionnel
            'query_builder' => function (ProfessionnelDeSanteRepository $repo) {
                return $repo->createQueryBuilder('p')
                    ->orderBy('p.nom', 'ASC'); // Trie par ordre alphabétique
            },
            // Définit les professionnels déjà sélectionnés
            'data' => $options['currentAuthorized'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas de classe de données principale
            'currentAuthorized' => [], // Option pour les professionnels autorisés actuels
        ]);
    }
}
