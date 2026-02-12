<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/TypeDiplome/Diplomes/Licence/Form/McccType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/01/2026 09:22
 */

namespace App\TypeDiplome\Diplomes\Licence\Form;

use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Form\Type\CardsChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class McccType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $typesMccc = [
            'cci' => [
                'label' => 'CCI',
                'description' => 'Contrôle continu intégral',
                'icone' => 'fal fa-infinity'
            ],
            'cc_ct' => [
                'label' => 'CC_CT',
                'description' => 'Contrôle continu & contrôle terminal',
                'icone' => 'fal fa-arrow-right-long-to-line'
            ],
            'ct' => [
                'label' => 'CT',
                'description' => 'Contrôle terminal',
                'icone' => 'fal fa-stopwatch'
            ],
            'cc' => [
                'label' => 'CC',
                'description' => 'Contrôle continu',
                'icone' => 'fal fa-arrow-right'
            ]
        ];


        $choices = [];
        foreach ($typesMccc as $value => $meta) {
            $choices[$meta['label']] = $value; // ex: 'CCI' => 'cci'
        }

        $builder->add('typeMccc', CardsChoiceType::class, [
            'mapped' => false,
            'label' => "Type de MCCC",
            'choices' => $choices,
            'columns' => 4,

            // Ici $choice est la VALUE (ex: 'cci', 'ct'...)
            'subtitle_getter' => static fn(?string $choice) => $choice ? ($typesMccc[$choice]['description'] ?? '') : '',
            'icon_getter' => static fn(?string $choice) => $choice ? ($typesMccc[$choice]['icone'] ?? '') : '',

            'on_change_action' => 'change->mccc--licence#updateType',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ElementConstitutif::class,
            'translation_domain' => 'form'
        ]);
    }
}
