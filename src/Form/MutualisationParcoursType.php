<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/src/Form/MutualisationParcoursType.php
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 16/05/2026 21:55
 */

declare(strict_types=1);

namespace App\Form;

use App\Entity\Composante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MutualisationParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $controllerIdentifier = $options['controller_identifier'];
        $targetAttr = static fn(string $target): string => 'data-' . $controllerIdentifier . '-target';

        $builder
            ->add('composante', EntityType::class, [
                'class' => Composante::class,
                'choices' => $options['composantes'],
                'choice_label' => 'libelle',
                'choice_value' => 'id',
                'label' => 'Choisir une composante',
                'placeholder' => 'Choisir une composante',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->' . $controllerIdentifier . '#changeComposante',
                    'data-controller' => 'symfony--ux-autocomplete--autocomplete',
                    $targetAttr('composante') => 'composante',
                ],
            ])
            ->add('formation', ChoiceType::class, [
                'label' => 'Choisir une mention/specialite',
                'placeholder' => 'Choisir d\'abord une composante',
                'choices' => [],
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'data-action' => 'change->' . $controllerIdentifier . '#changeFormation',
                    'data-controller' => 'symfony--ux-autocomplete--autocomplete',
                    $targetAttr('formation') => 'formation',
                ],
            ])
            ->add('parcours', ChoiceType::class, [
                'label' => 'Choisir un parcours',
                'placeholder' => 'Choisir d\'abord une mention/specialite',
                'choices' => [],
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'data-controller' => 'symfony--ux-autocomplete--autocomplete',
                    $targetAttr('parcours') => 'parcours',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => false,
            'composantes' => [],
            'controller_identifier' => '',
        ]);

        $resolver->setAllowedTypes('composantes', 'array');
        $resolver->setAllowedTypes('controller_identifier', 'string');
    }
}

