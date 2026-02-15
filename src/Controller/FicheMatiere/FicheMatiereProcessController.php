<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursProcessController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/02/2026 18:47
 */

namespace App\Controller\FicheMatiere;

use App\Classes\Process\FicheMatiereProcess;
use App\Classes\ValidationProcess;
use App\Classes\ValidationProcessFicheMatiere;
use App\Controller\BaseController;
use App\Entity\DpeParcours;
use App\Entity\FicheMatiere;
use App\Events\HistoriqueFicheMatiereEvent;
use App\Events\HistoriqueParcoursEvent;
use App\Utils\TurboStreamResponseFactory;
use App\Workflow\Form\MetaDrivenFormFactory;
use App\Workflow\Handler\TransitionHandlerRegistry;
use App\Workflow\Metadata\WorkflowMetaMapper;
use App\Workflow\ModalView\TransitionModalFicheMatiereViewBuilder;
use App\Workflow\ModalView\TransitionModalViewBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/fiche-matiere/v2/process', name: 'fiche_matiere_process')]
#[IsGranted('ROLE_ADMIN')]
class FicheMatiereProcessController extends BaseController
{

    private string $dir;

    public function __construct(
        private readonly MetaDrivenFormFactory                  $metaDrivenFormFactory,
        private readonly WorkflowMetaMapper                     $workflowMetaMapper,
        private readonly TransitionHandlerRegistry              $transitionHandlers,
        private readonly TransitionModalFicheMatiereViewBuilder $transitionModalViewBuilder,
        private readonly EventDispatcherInterface               $eventDispatcher,
        private readonly ValidationProcessFicheMatiere          $validationProcess,
    )
    {
    }


    #[Route('/{type}/{ficheMatiere}/{transition}', name: '_apply')]
    public function applyProcess(
        Request                    $request,
        TurbostreamResponseFactory $turboStream,
        FicheMatiere               $ficheMatiere,
        string                     $transition,
    ): Response
    {
        $rawMeta = $this->validationProcess->getMetaFromTransition($transition);
        $metaDto = $this->workflowMetaMapper->fromArray($rawMeta);

        $view = $this->transitionModalViewBuilder->build($transition, $ficheMatiere, $rawMeta);

        if ($view?->mode === 'report') {
            $formId = $metaDto->form?->formId ?? 'modal_form';
            $form = $this->metaDrivenFormFactory->createEmpty($formId);
        } else {
            // si pas de form dans metadata : form vide
            if ($metaDto->form === null) {
                $form = $this->metaDrivenFormFactory->createEmpty();
            } else {
                $form = $this->metaDrivenFormFactory->create($metaDto->form, $transition);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Blocage “report”
            if ($view?->mode === 'report' && $view?->canSubmit === false) {
                // toast error + rester dans la modal
            } elseif ($form->isValid()) {
                try {
                    $handlerCode = $metaDto->handlerCode ?? $transition; // fallback possible
                    dump($handlerCode);
                    dump($this->transitionHandlers->get($handlerCode));
                    $this->transitionHandlers->get($handlerCode)->handle(
                        $ficheMatiere,
                        $metaDto,
                        $transition,
                        (array)$form->getData());
                    // l'étape c'est la clé du tableau ficheMatiere->getEtatValidation()
                    $etape = array_keys($ficheMatiere->getEtatValidation($ficheMatiere))[0] ?? 'inconnue';
                    dump($etape);
                    $histoEvent = new HistoriqueFicheMatiereEvent($ficheMatiere, $this->getUser(), $etape, $metaDto->type, $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueFicheMatiereEvent::ADD_HISTORIQUE_FICHE_MATIERE);

                } catch (\Throwable $e) {
                    //toast erreur
                    dump($e->getMessage());
                    return $turboStream->stream('fiche_matiere_v2/turbo/apply_error.stream.html.twig', [
                        'ficheMatiere' => $ficheMatiere,
                        'transition' => $transition,
                        'type' => $metaDto->type,
                        'message' => $e->getMessage()
                    ]);
                }

                // toast success + refresh
                return $turboStream->stream('fiche_matiere_v2/turbo/apply_success.stream.html.twig', [
                    'ficheMatiere' => $ficheMatiere,
                    'transition' => $transition,
                    'type' => $metaDto->type,
                ]);
            }
        }

// rendu modal
        return $turboStream->streamOpenModalFromTemplates(
            'modal_title.' . $transition . '.' . $metaDto->type,
            'Fiche matière : ' . $ficheMatiere->getLibelle(),
            'fiche_matiere_v2/process/_apply.html.twig',
            [
                'ficheMatiere' => $ficheMatiere,
                'metaDto' => $metaDto,
                'transition' => $transition,
                'view' => $view,
                'form' => $form?->createView(),
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'modal_submit.' . $transition . '.' . $metaDto->type,
                'submitDisabled' => ($view?->mode === 'report' && $view->canSubmit === false),
            ]
        );
    }
}
