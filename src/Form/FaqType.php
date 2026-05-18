<?php

namespace App\Form;

use App\Entity\Faq;
use App\Enums\CentreGestionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $centreChoices = [];

        foreach (CentreGestionEnum::cases() as $centre) {
            if ($centre === CentreGestionEnum::CENTRE_GESTION_NULL) {
                continue;
            }

            $centreChoices[$centre->getLibelle()] = $centre->value;
        }

        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control mb-3', 'placeholder' => 'Posez votre question...']
            ])
            ->add('reponse', TextareaType::class, [
                'label' => 'Réponse',
                'attr' => ['class' => 'form-control', 'rows' => 12, 'placeholder' => 'Rédigez la réponse en markdown...']
            ])
            ->add('centresShow', ChoiceType::class, [
                'choices' => $centreChoices,
                'label' => 'Centres autorisés',
                'help' => 'Sélectionnez les centres qui auront la visibilité pour cette FAQ. Laisser vide = pas de restriction par centre.',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'empty_data' => [],
                'attr' => ['class' => 'mb-3']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => ' Afficher cette question',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Faq::class]);
    }
}
