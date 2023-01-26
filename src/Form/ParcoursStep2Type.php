<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Enums\ModaliteEnseignementEnum;
use App\Form\Type\TextareaWithSaveType;
use App\Form\Type\YesNoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('modalitesEnseignement', EnumType::class, [
                //http://lheo.gouv.fr/2.3/lheo/dict-modalites-enseignement.html#dict-modalites-enseignement
                'class' => ModaliteEnseignementEnum::class,
                'label' => 'Semestre de début de la parcours',
                'attr' => ['data-action' => 'change->parcours--step2#changeModaliteEnseignement'],
                'choice_label' => fn ($choice) => match ($choice) {
                    modaliteEnseignementEnum::PRESENTIELLE => 'En présentiel',
                    modaliteEnseignementEnum::DISTANCIELLE => 'En distanciel',
                    modaliteEnseignementEnum::HYBRIDE  => 'Hybride',
                },
                'expanded' => true,
            ])
            ->add('hasStage', YesNoType::class, [
                'label' => 'Stage ?',
                'attr' => ['data-action' => 'change->parcours--step2#changeStage'],
            ])
            //si oui...
            ->add('stageText', TextareaWithSaveType::class, [
                'label' => 'Description du stage',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici ...',
                'button_action' => 'click->parcours--step2#saveStageText',
            ])
            ->add('nbHeuresStages', NumberType::class, [
                'label' => 'Nombre d\'heures de stage',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresStages'],
            ])
            //alors zone de saisi
            //si L ou M, nombre d'heures
            ->add('hasProjet', YesNoType::class, [
                'label' => 'Projets tuteurés ?',
                'attr' => ['data-action' => 'change->parcours--step2#changeProjet'],
            ])
            ->add('projetText', TextareaWithSaveType::class, [
                'label' => 'Description du projet',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici ...',
                'button_action' => 'click->parcours--step2#saveProjetText',
            ])
            ->add('nbHeuresProjet', NumberType::class, [
                'label' => 'Nombre d\'heures de stage',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresProjet'],
            ])

            ->add('hasMemoire', YesNoType::class, [
                'label' => 'TER/Mémoire ?',
                'attr' => ['data-action' => 'change->parcours--step2#changeMemoire'],
            ])
            //si oui...
            //alors zone de saisi
            //si L ou M, nombre d'heures
            ->add('memoireText', TextareaWithSaveType::class, [
                'label' => 'Description du projet',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici ...',
                'button_action' => 'click->parcours--step2#saveMemoireText',
            ])
            ->add('nbHeuresMemoire', NumberType::class, [
                'label' => 'Nombre d\'heures de stage',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresMemoire'],
            ])

            //todo: si LP ou BUT nb heures en situation pro...
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
        ]);
    }
}
