<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Form/UeType.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:36
 */

namespace App\Form;

use App\Entity\NatureUeEc;
use App\Entity\TypeEc;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Form\Type\CardsChoiceType;
use App\Form\Type\FloatType;
use App\Form\Type\InlineCreateEntitySelectType;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use App\Repository\TypeUeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UeType extends AbstractType
{
    public function __construct(private readonly NatureUeEcRepository $natureUeEcRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $typeDiplome = $options['typeDiplome'];
        $natures = $this->natureUeEcRepository->findBy(['type' => NatureUeEc::Nature_UE], ['libelle' => 'ASC']);

        $builder
            ->add('libelle', TextType::class, [
                'attr' => [
                    'maxlength' => 255,
                ],
                'required' => false
            ])
            ->add('ects', FloatType::class, [
                'required' => false
            ])
            ->add('typeUe', InlineCreateEntitySelectType::class, [

                'class' => TypeUe::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisir un type d’UE',
                'new_placeholder' => 'Nom du nouveau type…',
                'label' => "Type d'UE",
                'required' => true,

                // ton contexte (typediplome)
                'scope' => $typeDiplome,

                // filtrage de la liste (optionnel)
                'query_builder' => function (TypeUeRepository $repo) use ($typeDiplome) {
                    return $repo->findByTypeDiplome($typeDiplome);
                },

                // évite doublons (optionnel)
                'find_existing' => function (string $label, $scope, EntityManagerInterface $em) {
                    return $em->getRepository(TypeUe::class)->createQueryBuilder('t')
                        ->andWhere('LOWER(t.libelle) = LOWER(:l)')
                        ->andWhere(':td MEMBER OF t.typeDiplomes')
                        ->setParameter('l', $label)
                        ->setParameter('td', $scope)
                        ->getQuery()
                        ->getOneOrNullResult();
                },

                // création (obligatoire)
                'create' => function (string $label, $typeDiplome, EntityManagerInterface $em) {
                    $e = new TypeUe();
                    $e->setLibelle($label);
                    $e->addTypeDiplome($typeDiplome);
                    return $e; // persist/flush gérés par le type (ou tu peux le faire ici)
                },
            ])
            ->add('natureUeEc', CardsChoiceType::class, [
                'label' => "Nature de l'élément",
                'choices' => $natures, // entités
                'choice_label' => fn($e) => $e->getLibelle(),
                'choice_value' => fn($e) => (string)$e?->getId(),
                'columns' => 4,
                'subtitle_property' => 'descriptionCourte',
                'icon_property' => 'icone',
                'on_change_action' => 'change->ue#changeNatureUe',
            ])
            ->add('descriptionUeLibre', TextareaType::class, [
                'attr' => [
                    'maxlength' => 255,
                    'rows' => 5,
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ue::class,
            'translation_domain' => 'form',
            'typeDiplome' => null,
        ]);
    }
}
