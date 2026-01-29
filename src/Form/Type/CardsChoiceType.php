<?php

// src/Form/Type/CardsChoiceType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class CardsChoiceType extends AbstractType
{
    private PropertyAccessorInterface $accessor;

    public function __construct(?PropertyAccessorInterface $accessor = null)
    {
        $this->accessor = $accessor ?? PropertyAccess::createPropertyAccessor();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'expanded' => true,
            'multiple' => false,

            'label' => null,
            'help' => null,

            // Stimulus (optionnel)
            'on_change_action' => null,
            'event_name' => null,
            'update_target' => null,

            // Layout
            'columns' => 3, // 2/3/4/5/6...

            // Meta générée automatiquement
            'subtitle_property' => null, // ex: "descriptionCourte"
            'subtitle_getter' => null,   // fn($choice): ?string

            'icon_property' => null,     // ex: "icon" (nom, slug, classe, etc.)
            'icon_getter' => null,       // fn($choice): ?string

            'disabled_property' => null, // ex: "disabled" ou "isDisabled"
            'disabled_getter' => null,   // fn($choice): bool
        ]);

        $resolver->setAllowedTypes('columns', 'int');

        $resolver->setAllowedTypes('subtitle_property', ['null', 'string']);
        $resolver->setAllowedTypes('subtitle_getter', ['null', 'callable']);

        $resolver->setAllowedTypes('icon_property', ['null', 'string']);
        $resolver->setAllowedTypes('icon_getter', ['null', 'callable']);

        $resolver->setAllowedTypes('disabled_property', ['null', 'string']);
        $resolver->setAllowedTypes('disabled_getter', ['null', 'callable']);

        $resolver->setAllowedTypes('on_change_action', ['null', 'string']);
        $resolver->setAllowedTypes('event_name', ['null', 'string']);
        $resolver->setAllowedTypes('update_target', ['null', 'string']);

        // Rend la grille exploitable côté Twig
        $resolver->setNormalizer('columns', function (Options $options, $value) {
            return max(1, (int)$value);
        });

        /**
         * Normalizer : enrichit choice_attr automatiquement
         * - data-card-subtitle
         * - data-card-icon
         * - disabled
         */
        $resolver->setNormalizer('choice_attr', function (Options $options, $choiceAttr) {
            $needsSubtitle = (bool)($options['subtitle_property'] || $options['subtitle_getter']);
            $needsIcon = (bool)($options['icon_property'] || $options['icon_getter']);
            $needsDisabled = (bool)($options['disabled_property'] || $options['disabled_getter']);

            if (!$needsSubtitle && !$needsIcon && !$needsDisabled) {
                return $choiceAttr;
            }

            $computeString = function ($choice, ?string $property, $getter): string {
                if ($getter) {
                    $v = $getter($choice);
                    return is_string($v) ? trim($v) : '';
                }
                if ($property) {
                    try {
                        $v = $this->accessor->getValue($choice, $property);
                        if ($v === null) return '';
                        return is_string($v) ? trim($v) : trim((string)$v);
                    } catch (\Throwable) {
                        return '';
                    }
                }
                return '';
            };

            $computeBool = function ($choice, ?string $property, $getter): bool {
                if ($getter) {
                    return (bool)$getter($choice);
                }
                if ($property) {
                    try {
                        return (bool)$this->accessor->getValue($choice, $property);
                    } catch (\Throwable) {
                        return false;
                    }
                }
                return false;
            };

            $apply = function (array $attrs, $choice) use ($options, $computeString, $computeBool, $needsSubtitle, $needsIcon, $needsDisabled): array {
                if ($needsSubtitle && !array_key_exists('data-card-subtitle', $attrs)) {
                    $subtitle = $computeString($choice, $options['subtitle_property'], $options['subtitle_getter']);
                    if ($subtitle !== '') {
                        $attrs['data-card-subtitle'] = $subtitle;
                    }
                }

                if ($needsIcon && !array_key_exists('data-card-icon', $attrs)) {
                    $icon = $computeString($choice, $options['icon_property'], $options['icon_getter']);
                    if ($icon !== '') {
                        $attrs['data-card-icon'] = $icon;
                    }
                }

                if ($needsDisabled && !array_key_exists('disabled', $attrs)) {
                    $disabled = $computeBool($choice, $options['disabled_property'], $options['disabled_getter']);
                    if ($disabled) {
                        $attrs['disabled'] = 'disabled';
                        // Bonus : état CSS sans JS
                        $attrs['data-card-disabled'] = '1';
                    }
                }

                return $attrs;
            };

            // Wrap callable existant
            if (is_callable($choiceAttr)) {
                return function ($choice, $key, $value) use ($choiceAttr, $apply) {
                    $attrs = (array)$choiceAttr($choice, $key, $value);
                    return $apply($attrs, $choice);
                };
            }

            // Convert array/null -> callable
            return function ($choice, $key, $value) use ($choiceAttr, $apply) {
                $attrs = is_array($choiceAttr) ? $choiceAttr : [];
                return $apply($attrs, $choice);
            };
        });
    }

    public function buildView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options): void
    {
        $view->vars['on_change_action'] = $options['on_change_action'];
        $view->vars['event_name'] = $options['event_name'];
        $view->vars['update_target'] = $options['update_target'];
        $view->vars['columns'] = $options['columns'];
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'cards_choice';
    }
}
