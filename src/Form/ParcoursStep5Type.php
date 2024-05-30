<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/ParcoursStep5Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 17:44
 */

namespace App\Form;

use App\Entity\Composante;
use App\Entity\Parcours;
use App\Enums\NiveauFormationEnum;
use App\Enums\NiveauLangueEnum;
use App\Enums\RegimeInscriptionEnum;
use App\Form\Type\TextareaAutoSaveType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnitEnum;

class ParcoursStep5Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveauFrancais', EnumType::class, [
        'class' => NiveauLangueEnum::class,
        'choice_label' => static function (UnitEnum $choice): string {
            return $choice->libelle();
        },
                'attr' => ['data-action' => 'change->parcours--step5#changeNiveauLangue'],
    ])
            ->add('prerequis', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 15, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#savePrerequis'],
                'help' => '-',
            ])
            ->add('composanteInscription', EntityType::class, [
                'placeholder' => 'Choisissez une composante d\'inscription',
                'class' => Composante::class,
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                'autocomplete' => true,
                'attr' => ['data-action' => 'change->parcours--step5#changeComposanteInscription'],
            ])
            ->add('regimeInscription', EnumType::class, [
                'class' => RegimeInscriptionEnum::class,
                'translation_domain' => 'enum',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['data-action' => 'change->parcours--step5#changeRegimeInscription']
            ])
            ->add('modalitesAlternance', TextareaAutoSaveType::class, [
                'help' => 'Indiquez en 3000 caractères maximum les périodes et leurs durées en centre ou en entreprise.',
                'attr' => ['rows' => 20, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#saveModalitesAlternance'],
            ])
            ->add('coordSecretariat', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 5, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#coordSecretariat'],
                'help' => '-',
            ])
            ->add('modalitesAdmission', TextareaAutoSaveType::class, [
                'attr' => ['rows' => 8, 'maxlength' => 3000, 'data-action' => 'change->parcours--step5#saveModalitesAdmission'],
                'label' => "Modalités d'admission"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcours::class,
            'translation_domain' => 'form'
        ]);
    }
}
