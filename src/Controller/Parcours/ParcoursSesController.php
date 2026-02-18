<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursSesController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 06/02/2026 09:18
 */

namespace App\Controller\Parcours;

use App\Classes\ValidationProcess;
use App\Controller\BaseController;
use App\Entity\DpeParcours;
use App\Enums\TypeModificationDpeEnum;
use App\Form\Type\YesNoType;
use App\Utils\TurboStreamResponseFactory;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parcours/v2/ses', name: 'parcours_ses_')]
class ParcoursSesController extends BaseController
{
    public function __construct(
        private TurboStreamResponseFactory $turboStream
    )
    {
    }

    #[Route('/changer-etat/{dpeParcours}', name: 'changer_etat')]
    public function changerEtat(
        ValidationProcess $validationProcess,
        Request           $request,
        DpeParcours       $dpeParcours
    ): Response
    {
        $process = $validationProcess->getProcessForListe();
        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'form'
        ])
            ->add('etat', ChoiceType::class, [
                'choices' => $process,
                'choice_translation_domain' => 'process',
            ])
            ->add('inHistorique', YesNoType::class, ['data' => false])
            ->getForm();


        return $this->turboStream->streamOpenModalFromTemplates(
            'Modifier l\'état du parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/ses/_changer_etat.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
                'form' => $form->createView()
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Changer l\'état du parcours',
            ]
        );
    }

    #[Route('/changer-ouverture/{dpeParcours}', name: 'changer_ouverture')]
    public function changerOuverture(
        Request     $request,
        DpeParcours $dpeParcours
    ): Response
    {
        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'modal_form'],
            'translation_domain' => 'form'
        ])
            ->add('etat', ChoiceType::class, [
                'choices' => TypeModificationDpeEnum::cases(),
                'choice_label' => fn(TypeModificationDpeEnum $c) => 'type_modif_dpe.' . $c->value,
                'choice_value' => fn(?TypeModificationDpeEnum $c) => $c?->value,
                'choice_translation_domain' => 'process',
            ])
            ->getForm();


        return $this->turboStream->streamOpenModalFromTemplates(
            'Modifier l\'état d\'ouverture du parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/ses/_changer_ouverture.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
                'form' => $form->createView()
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Changer l\'état du parcours',
            ]
        );
    }

    #[Route('/commentaires/{dpeParcours}', name: 'commentaires')]
    public function commentaires(
        Request     $request,
        DpeParcours $dpeParcours
    ): Response
    {
        return $this->turboStream->streamOpenModalFromTemplates(
            'Commentaires sur le parcours',
            'Parcours : ' . $dpeParcours->getParcours()?->getDisplay(),
            'parcours_v2/ses/_commentaires.html.twig',
            [
                'parcours' => $dpeParcours->getParcours(),
                'dpeParcours' => $dpeParcours,
            ],
            '_ui/_footer_cancel.html.twig',
            [
                'cancelLabel' => 'Fermer',
            ]
        );
    }
}
