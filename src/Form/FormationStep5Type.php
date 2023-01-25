<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Site;
use App\Form\Type\TextareaWithSaveType;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep5Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prerequis', TextareaWithSaveType::class, [
                'label' => 'Prérequis recommandés',
                'attr' => ['rows' => 5, 'maxlength' => 3000],
                'help' => 'Indiquez ici si des prérequis pédagogiques sont conseillés pour réussir dans cette formation',
                'button_action' => 'click->formation--step5#savePrerequis',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
