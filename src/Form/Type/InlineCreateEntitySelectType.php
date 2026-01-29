<?php

// src/Form/Type/InlineCreateEntitySelectType.php
namespace App\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InlineCreateEntitySelectType extends AbstractType
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entity', EntityType::class, [
                'class' => $options['class'],
                'choice_label' => $options['choice_label'],
                'placeholder' => $options['placeholder'],
                'required' => $options['required'],
                'query_builder' => $options['query_builder'], // callable|null (Doctrine attend callable(repo): QB)
            ])
            ->add('new', TextType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => $options['new_placeholder'],
                    'data-inline-create-target' => 'newInput',
                ],
            ]);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($modelValue) {
                return ['entity' => $modelValue];
            },
            function ($submittedValue) {
                if (!is_array($submittedValue)) {
                    return null;
                }
                return $submittedValue['entity'] ?? null;
            }
        ));

        // Création/selection au submit

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();        // tableau brut soumis : ['entity' => '...', 'new' => '...']
            $form = $event->getForm();

            $newLabel = trim((string)($data['new'] ?? ''));
            if ($newLabel === '') {
                return;
            }

            $scope = $options['scope'] ?? null;

            // 1) éviter doublons (optionnel)
            if (is_callable($options['find_existing'])) {
                $existing = ($options['find_existing'])($newLabel, $scope, $this->em);
                if ($existing) {
                    // on force la valeur soumise "entity" (id)
                    $data['entity'] = (string)$existing->getId();
                    $data['new'] = '';
                    $event->setData($data);
                    return;
                }
            }

            // 2) créer
            if (!is_callable($options['create'])) {
                throw new \LogicException('Option "create" must be a callable that returns the created entity.');
            }

            $entity = ($options['create'])($newLabel, $scope, $this->em);
            $this->em->persist($entity);
            $this->em->flush();

            // 3) injecter l'id dans la soumission => Symfony sélectionnera cet élément
            $data['entity'] = (string)$entity->getId();
            $data['new'] = ''; // important
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['class', 'create']);
        $resolver->setDefaults([
            'choice_label' => 'id',
            'placeholder' => 'Choisir…',
            'required' => false,

            // texte de l’input
            'new_placeholder' => 'Nom du nouveau…',

            // filtre contextuel (ex: typediplome, tenant, etc.)
            'scope' => null,

            // callable|null: fn(Repository $repo) => QueryBuilder
            'query_builder' => null,

            // callable|null: fn(string $label, mixed $scope, EntityManagerInterface $em) => ?object
            'find_existing' => null,

            // (Block prefix + theme)
            'label' => false,
        ]);

        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('choice_label', ['string', 'callable']);
        $resolver->setAllowedTypes('query_builder', ['null', 'callable']);
        $resolver->setAllowedTypes('create', 'callable');
        $resolver->setAllowedTypes('find_existing', ['null', 'callable']);
    }

    public function getBlockPrefix(): string
    {
        return 'inline_create_entity_select';
    }
}
