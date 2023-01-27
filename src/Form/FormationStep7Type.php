<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Ville;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @deprecated  */
class FormationStep7Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Villes', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Localisation de la formation',
                'attr' => ['data-action' => 'change->formation--step1#changeVille']
            ])
            ->add('semestre', ChoiceType::class, [
                //todo: filtrer ? uniquement pour les LP?
                'choices' => [
                    'Semestre 1' => 1,
                    'Semestre 2' => 2,
                    'Semestre 3' => 3,
                    'Semestre 4' => 4,
                    'Semestre 5' => 5,
                    'Semestre 6' => 6,

                ],
                'label' => 'Semestre de début de la formation',
                'attr' => ['data-action' => 'change->formation--step1#changeSemestre'],
                'mapped' => false,
            ])
            ->add('parcours', YesNoType::class, [
                'label' => 'Formation en parcours',
                'attr' => ['data-action' => 'change->formation--step1#changeParcours'],
                'mapped' => false,
            ])
            //si oui...
            //alors formulaire dynamique pour ajouter les libellés des parcours ?
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
