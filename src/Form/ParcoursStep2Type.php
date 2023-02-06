<?php

namespace App\Form;

use App\Entity\Parcours;
use App\Enums\ModaliteEnseignementEnum;
use App\Form\Type\TextareaAutoSaveType;
use App\Form\Type\YesNoType;
use App\TypeDiplome\Source\ButTypeDiplome;
use App\TypeDiplome\Source\LicenceProfessionnelleTypeDiplome;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcoursStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplome = $options['typeDiplome'];

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
            ->add('stageText', TextareaAutoSaveType::class, [
                'label' => 'Description du stage',
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveStageText'],
                'help' => 'Indiquez ici le nombre, sur quel semestre ils sont prévus, les modalités...',
            ])
            ->add('nbHeuresStages', NumberType::class, [
                'label' => 'Nombre d\'heures de stage prévu',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresStages'],
            ])
            //alors zone de saisi
            //si L ou M, nombre d'heures
            ->add('hasProjet', YesNoType::class, [
                'label' => 'Projets tuteurés ?',
                'attr' => ['data-action' => 'change->parcours--step2#changeProjet'],
            ])
            ->add('projetText', TextareaAutoSaveType::class, [
                'label' => 'Description du projet',
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveProjetText'],
                'help' => 'Indiquez ici le nombre, sur quel semestre ils sont prévus, les modalités...',
            ])
            ->add('nbHeuresProjet', NumberType::class, [
                'label' => 'Nombre d\'heures de projets tutorés prévu',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresProjet'],
            ])

            ->add('hasMemoire', YesNoType::class, [
                'label' => 'TER/Mémoire ?',
                'attr' => ['data-action' => 'change->parcours--step2#changeMemoire'],
            ])
            ->add('memoireText', TextareaAutoSaveType::class, [
                'label' => 'Description du projet',
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveMemoireText'],
                'help' => 'Indiquez ici le nombre, sur quel semestre ils sont prévus, les modalités...',
            ]);

            if ($typeDiplome::SOURCE === ButTypeDiplome::SOURCE || $typeDiplome::SOURCE === LicenceProfessionnelleTypeDiplome::SOURCE)
            {
                $builder->add('nbHeuresSituationPro', NumberType::class, [
                    'label' => 'Nombre d\'heures de situation professionnelle prévu',
                    'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresSituationPro'],
                ]);
            }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'typeDiplome' => null,
        ]);
    }
}
