<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Site;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formation = $options['data'];

        $builder
            ->add('sites', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Localisation(s) de la formation',
                'help' => 'Plusieurs choix possibles',
                'choice_attr' => function($choice, $key, $value) {
                    return ['data-action' => 'change->formation--step1#changeSite'];
                },
            ]);
        if ($formation->getTypeDiplome() === 'LP') {
            $builder->
            add('semestre', ChoiceType::class, [
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
            ]);
        }

        $builder->add('hasParcours', YesNoType::class, [
            'label' => 'Formation en parcours ? ',
            'choice_attr' => function($choice, $key, $value) {
                // adds a class like attending_yes, attending_no, etc
                return ['data-action' => 'change->formation--step1#changeParcours'];
            },
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
