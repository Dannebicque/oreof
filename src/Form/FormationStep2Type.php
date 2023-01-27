<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\Site;
use App\Enums\RythmeFormationEnum;
use App\Form\Type\TextareaWithSaveType;
use App\Form\Type\YesNoType;
use Doctrine\Common\Annotations\Annotation\Enum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formation = $options['data'];

        $builder
            ->add('contenuFormation', TextareaWithSaveType::class, [
                'label' => 'Contenu de la formation',
                'help' => 'Indiquez en 3000 caractères maximum le contenu de la formation et une description détaillée des différents sujets traités dans la formation.',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->formation--step2#saveContenu',
            ])
            ->add('resultatsAttendus', TextareaWithSaveType::class, [
                'label' => 'Résultats attendus de la formation',
                'help' => 'Indiquez en 3000 caractères maximum les résultats attendus de la formation (titre, diplôme, certificat, attestation, …) et précise les modalités de reconnaissance ou de validation. De la même manière que pour l’élément “intitule-formation”, les diplômes, titres ou certifications devraient utiliser des dénominations conformes aux tables de l’Éducation Nationale ou au Répertoire National des Certifications Professionnelles (RNCP).',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->formation--step2#saveResultats',
            ])
            ->add('rythmeFormation', EnumType::class, [
                'label' => 'Rythme de la formation',
                'help' => 'Indiquez le rythme de la formation (temps plein, temps partiel, cours du soir,etc.).',
                'class' => RythmeFormationEnum::class,
                'translation_domain' => 'enum',
                'attr' => ['data-action' => 'change->formation--step2#changeRythme'],
            ])
            ->add('rythmeFormationTexte', TextareaWithSaveType::class, [
                'label' => 'Compléments sur le rythlme de formation',
                'help' => 'Indiquez en 3000 caractères maximum le rythme de la formation : temps plein, temps partiel, cours du soir,etc..',
                'attr' => ['rows' => 20, 'maxlength' => 3000],
                'button_action' => 'click->formation--step2#saveRythme',
            ]);

        if ($formation->getTypeDiplome() === 'LP') {
            $builder->
            add('semestre', ChoiceType::class, [
                //todo: filtrer ? uniquement pour les LP?
                'choices' => [
                    'Semestre 1' => 1,
                    'Semestre 2' => 2,
                    'Semestre 3' => 3,
                    'Semestre 4' => 4,
                    'Semestre 5' => 5,
                    'Semestre 6' => 6,

                ],
                'label' => 'Semestre de début de la formation',
                'attr' => ['data-action' => 'change->formation--step1#changeSemestre'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
