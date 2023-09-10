<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\Process\FormationProcess;
use App\Classes\Process\ParcoursProcess;
use App\Classes\ValidationProcess;
use App\Classes\verif\FormationValide;
use App\Classes\verif\ParcoursValide;
use App\Events\HistoriqueFormationEvent;
use App\Events\HistoriqueParcoursEvent;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProcessValidationController extends AbstractController
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface   $entityManager,
        private ValidationProcess        $validationProcess,
        private FormationProcess         $formationProcess,
        private ParcoursProcess          $parcoursProcess,
    )
    {
    }

    #[Route('/validation/valide/{etape}', name: 'app_validation_valide')]
    public function valide(
        #[Target('dpe')]
        WorkflowInterface   $dpeWorkflow,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
//        $definition = $dpeWorkflow->getDefinition();

        $process = $this->validationProcess->getEtape($etape);
//        $valid = true;
        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->valideFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->valideParcours($objet, $this->getUser(), $process, $etape, $request);
                }

                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('process_validation/_valide.html.twig', [
            'objet' => $objet,
            'process' => $process,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/refuse/{etape}', name: 'app_validation_refuse')]
    public function refuse(
        TranslatorInterface $translator,
        #[Target('dpe')]
        WorkflowInterface   $dpeWorkflow,
        WorkflowInterface   $parcoursWorkflow,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
        $definition = $dpeWorkflow->getDefinition();

        $process = $this->validationProcess->getEtape($etape);
        //workflow pas toujours celui de dpe??

        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->refuseFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->refuseParcours($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
//                $objet = $parcoursRepository->find($id);
//                if ($objet === null) {
//                    return JsonReponse::error('Parcours non trouvé');
//                }
//                $place = $parcoursWorkflow->getMarking($objet);
//                $transitions = $parcoursWorkflow->getEnabledTransitions($objet);
//                if ($request->isMethod('POST')) {
//                    $parcoursWorkflow->apply($objet, $process['canRefuse'], ['motif' => $request->request->get('commentaire', '')]);
//                    $histoEvent = new HistoriqueParcoursEvent($objet, $this->getUser(), $etape, 'refuse', $request);
//                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
//                    return JsonReponse::success($translator->trans('parcours.refuse.'.$etape.'.flash.success', [], 'process'));
//                }
//                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Fiche EC/matière non trouvée');
                }
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('process_validation/_refuse.html.twig', [
            'process' => $process,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'objet' => $objet,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/reserve/{etape}', name: 'app_validation_reserve')]
    public function reserve(
        #[Target('dpe')]
        WorkflowInterface   $dpeWorkflow,
        WorkflowInterface   $parcoursWorkflow,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');
        $definition = $dpeWorkflow->getDefinition();

        $process = $this->validationProcess->getEtape($etape);

        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                $processData = $this->formationProcess->etatFormation($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->formationProcess->reserveFormation($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }

                $processData = $this->parcoursProcess->etatParcours($objet, $process);

                if ($request->isMethod('POST')) {
                    return $this->parcoursProcess->reserveParcours($objet, $this->getUser(), $process, $etape, $request);
                }
                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Fiche EC/matière non trouvée');
                }
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('process_validation/_reserve.html.twig', [
            'process' => $process,
            'objet' => $objet,
            'processData' => $processData ?? null,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/edit/{type}/{id}', name: 'app_validation_edit')]
    public function edit(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        WorkflowInterface   $dpeWorkflow,
        Request             $request,
        string              $type,
        int                 $id
    ) {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $process = $this->validationProcess->getEtape($data['etat']);
            //mise à jour du workflow
            switch ($type) {
                case 'formation':
                    $objet = $formationRepository->find($id);
                    if ($objet === null) {
                        return JsonReponse::error('Formation non trouvée');
                    }
                    $objet->setEtatDpe([$process['transition'] => 1]);
                    //mettre à jour l'historique
                    $histoEvent = new HistoriqueFormationEvent($objet, $this->getUser(), $data['etat'], 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
                    $this->entityManager->flush();
                    break;
                case 'parcours':
                    $objet = $parcoursRepository->find($id);
                    if ($objet === null) {
                        return JsonReponse::error('Parcours non trouvé');
                    }

                    $objet->setEtatParcours([$process['transition'] => 1]);
                    //mettre à jour l'historique
                    $histoEvent = new HistoriqueParcoursEvent($objet, $this->getUser(), $data['etat'], 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
                    $this->entityManager->flush();
                    break;
//                case 'ficheMatiere':
//                    $objet = $this->getDoctrine()->getRepository(FicheMatiere::class)->find($id);
//                    $dpeWorkflow->apply($objet, $data['etat']);
//                    break;
            }

            return JsonReponse::success('Validation modifiée');
        }


        return $this->render('process_validation/_edit.html.twig', [
            'etats' => $this->validationProcess->getProcess(),
            'type' => $type,
            'id' => $id,
        ]);
    }
}
