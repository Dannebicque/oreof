<?php

namespace App\Workflow\Form;

use App\DTO\Workflow\ModalFormMetaDto;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class MetaDrivenFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface  $formFactory,
        private readonly MetaFormTypeResolver  $typeResolver,
        private readonly MetaFormOptionsFilter $optionsFilter,
    )
    {
    }

    public function create(ModalFormMetaDto $meta, string $transition): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'process',
        ]);

        foreach ($meta->fields as $field) {
            $type = $this->typeResolver->resolve($field->type);

            $options = [
                'required' => $field->required,
            ];

            $options['label_translation_parameters'] = [
                '%transition%' => $transition,
                '%field%' => $field->name,
            ];

            $options['help_translation_parameters'] = [
                '%transition%' => $transition,
                '%field%' => $field->name,
            ];

            $options = array_replace($options, $this->optionsFilter->filter($field->options));

            $builder->add($field->name, $type, $options);
        }

        return $builder->getForm();
    }

    public function createEmpty(string $formId = 'modal_form'): FormInterface
    {
        return $this->formFactory->createBuilder(FormType::class, null, [
            'attr' => ['id' => $formId],
            'translation_domain' => 'form',
        ])->getForm();
    }
}
