<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Form/BugReportType.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 18/05/2026 20:41
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BugReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formation', HiddenType::class, [
                'data' => $options['formation_id'] ?? ''
            ])
            ->add('parcours', HiddenType::class, [
                'data' => $options['parcours_id'] ?? '',
            ])
            ->add('page', HiddenType::class, [
                'data' => $options['page'] ?? '',
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['maxlength' => 200],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le titre est obligatoire.'),
                    new Assert\Length(max: 200, maxMessage: 'Le titre ne doit pas dépasser 200 caractères.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'rows' => 8,
                    'placeholder' => 'Décrivez le problème, les étapes pour le reproduire et le résultat attendu.',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le message est obligatoire.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'bug_report',
            'formation_id' => null,
            'parcours_id' => null,
            'page' => null,
        ]);
    }
}

