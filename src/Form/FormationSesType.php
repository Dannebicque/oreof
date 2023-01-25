<?php

namespace App\Form;

use App\Entity\Domaine;
use App\Entity\Formation;
use App\Entity\Mention;
use App\Entity\User;
use App\Enums\NiveauFormationEnum;
use App\Form\Type\YesNoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationSesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeDiplome', ChoiceType::class, [
                'choices' => $options['typesDiplomes'],
                'label' => 'Type de diplôme',
                'attr' => ['data-action' => 'change->formation#changeTypeDiplome']
            ])
            ->add('domaine', EntityType::class, [
                'class' => Domaine::class,
                'choice_label' => 'libelle',
                'label' => 'Domaine',
                'attr' => ['data-action' => 'change->formation#changeDomaine']
            ])
            ->add('mention', EntityType::class, [
                'attr' => ['placeholder' => 'Choisir un type de diplôme et un domaine'],
                'class' => Mention::class,
                'choice_label' => 'libelle',
                'label' => 'Mention',
                'required' => false,
                'help' => 'Si la mention n\'existe pas, veuillez la créer dans la section "Autre mention"'
            ])
            ->add('mentionTexte', TextType::class, [
                'label' => 'Autre mention',
                'required' => false,
                'help' => 'Si la mention existe, veuillez la sélectionner dans la liste déroulante'
            ])
            ->add('niveauEntree', EnumType::class, [
                'class' => NiveauFormationEnum::class,
                'label' => 'Niveau d\'entrée en formation',
            ])
            ->add('niveauSortie', EnumType::class, [
                'class' => NiveauFormationEnum::class,
                'label' => 'Niveau de sortie de la formation',
            ])
            ->add('inscriptionRNCP', YesNoType::class, [
                'label' => 'Inscription au RNCP ?',
                'attr' => ['data-action' => 'change->formation#changeInscriptionRNCP']
            ])
            ->add('codeRNCP', TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 10],
                'label' => 'Code RNCP'
            ])
            ->add('responsableMention', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'display',
                'label' => 'Responsable de la mention',
                'attr' => ['data-action' => 'change->formation#changeResponsableMention']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'typesDiplomes' => []
        ]);
    }
}
