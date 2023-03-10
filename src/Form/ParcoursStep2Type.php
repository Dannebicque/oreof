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
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeModaliteEnseignement'],
                'choice_label' => fn ($choice) => match ($choice) {
                    modaliteEnseignementEnum::PRESENTIELLE => 'En présentiel',
                    modaliteEnseignementEnum::DISTANCIELLE => 'En distanciel',
                    modaliteEnseignementEnum::HYBRIDE  => 'Hybride',
                },
                'expanded' => true,
            ])
            ->add('hasStage', YesNoType::class, [
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeStage'],
            ])
            //si oui...
            ->add('stageText', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveStageText'],
                'help' => '-',
            ])
            ->add('nbHeuresStages', NumberType::class, [
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresStages'],
            ])
            //alors zone de saisi
            //si L ou M, nombre d'heures
            ->add('hasProjet', YesNoType::class, [
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeProjet'],
            ])
            ->add('projetText', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveProjetText'],
                'help' => '-',
            ])
            ->add('nbHeuresProjet', NumberType::class, [
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresProjet'],
            ])

            ->add('hasMemoire', YesNoType::class, [
                'help' => '-',
                'attr' => ['data-action' => 'change->parcours--step2#changeMemoire'],
            ])
            ->add('memoireText', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step2#saveMemoireText'],
                'help' => '-',
            ]);

            if ($typeDiplome::SOURCE === ButTypeDiplome::SOURCE || $typeDiplome::SOURCE === LicenceProfessionnelleTypeDiplome::SOURCE)
            {
                $builder->add('nbHeuresSituationPro', NumberType::class, [
//                    'label' => 'Nombre d\'heures de situation professionnelle prévu',
                    'help' => '-',
                    'attr' => ['data-action' => 'change->parcours--step2#changeNbHeuresSituationPro'],
                ]);
            }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'typeDiplome' => null,
            'translation_domain' => 'form'
        ]);
    }
}
