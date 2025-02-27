<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/FicheMatiereStep2Type.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/03/2023 16:15
 */

namespace App\Form;

use App\Entity\FicheMatiere;
use App\Entity\Langue;
use App\Form\Type\TextareaAutoSaveType;
use App\Repository\LangueRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheMatiereStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('description', TextareaAutoSaveType::class, [
                'attr' => ['data-action' => 'change->fichematiere--step2#saveDescription', 'maxlength' => 3000, 'rows' => 20],
                'help' => '-'
            ])
            ->add('langueDispense', EntityType::class, [
                'attr' => ['data-action' => 'change->fichematiere--step2#changeLangue', 'data-fichematiere--step2-type-param' => 'langueDispense' ],
                'class' => Langue::class,
                'query_builder' => function (LangueRepository $lr) {
                    return $lr->createQueryBuilder('l')
                        ->orderBy('l.libelle', 'ASC');
                },
                'choice_label' => 'libelle',
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
            ->add('langueSupport', EntityType::class, [
                'attr' => ['data-action' => 'change->fichematiere--step2#changeLangue', 'data-fichematiere--step2-type-param' => 'langueSupport'],
                'class' => Langue::class,
                'choice_label' => 'libelle',
                'query_builder' => function (LangueRepository $lr) {
                    return $lr->createQueryBuilder('l')
                        ->orderBy('l.libelle', 'ASC');
                },
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'help' => '-'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FicheMatiere::class,
            'translation_domain' => 'form'
        ]);
    }
}
