<?php

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\ValidationProcess;
use App\Classes\verif\FormationValide;
use App\Entity\Historique;
use App\Entity\HistoriqueFormation;
use App\Entity\HistoriqueParcours;
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

class ValidationController extends AbstractController
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private EntityManagerInterface   $entityManager,
        private ValidationProcess $validationProcess)
    {
    }

    #[Route('/validation/valide/{etape}', name: 'app_validation_valide')]
    public function valide(
        FormationValide        $formationValide,
        #[Target('dpe')]
        WorkflowInterface      $dpeWorkflow,
        WorkflowInterface      $parcoursWorkflow,
        ParcoursRepository    $parcoursRepository,
        FormationRepository    $formationRepository,
        string                 $etape,
        Request $request
    ): Response
    {
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
                if (array_key_exists('check', $process)) {
                    $validation = $formationValide->valide($objet, $process);
                }

                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);

                if ($request->isMethod('POST')) {
                    $dpeWorkflow->apply($objet, $process['canValide']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    $this->entityManager->flush();
                    $histoEvent = new HistoriqueFormationEvent($objet, $this->getUser(), $etape, 'valide', $request);
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
                    return JsonReponse::success('ok');
                }
                break;
            case 'parcours':
                $objet = $parcoursRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Parcours non trouvé');
                }
                $place = $parcoursWorkflow->getMarking($objet);
                $transitions = $parcoursWorkflow->getEnabledTransitions($objet);
                if ($request->isMethod('POST')) {
                    $parcoursWorkflow->apply($objet, $process['canValide']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    $this->entityManager->flush();
                    $histoEvent = new HistoriqueParcoursEvent($objet, $this->getUser(), $etape, 'valide',$request->request->get('commentaire'));
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueParcoursEvent::ADD_HISTORIQUE_PARCOURS);
                    return JsonReponse::success('ok');
                }
                break;
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
        }

        return $this->render('validation/_valide.html.twig', [
            'process' => $process,
            'place' => array_keys($place->getPlaces())[0],
            'transitions' => $transitions,
            'defintion' => $definition,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'validation' => $validation ?? '',
        ]);
    }

    #[Route('/validation/refuse/{etape}', name: 'app_validation_refuse')]
    public function refuse(
        #[Target('dpe')]
        WorkflowInterface      $dpeWorkflow,
        FormationRepository    $formationRepository,
        string                 $etape,
        Request $request
    ): Response
    {
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
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
            case 'parcours':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Parcours ou formation non trouvés');
                }
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                if ($request->isMethod('POST')) {
                    $dpeWorkflow->apply($objet, $process['canRefuse']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    $histoEvent = new HistoriqueFormationEvent($objet, $this->getUser(), $etape, 'refuse',$request->request->get('commentaire'));
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
                    return JsonReponse::success('ok');
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

        return $this->render('validation/_refuse.html.twig', [
            'process' => $process,
            'place' => array_keys($place->getPlaces())[0],
            'transitions' => $transitions,
            'defintion' => $definition,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/reserve/{etape}', name: 'app_validation_reserve')]
    public function reserve(
        #[Target('dpe')]
        WorkflowInterface      $dpeWorkflow,
        WorkflowInterface      $parcoursWorkflow,
        ParcoursRepository    $parcoursRepository,
        FormationRepository    $formationRepository,
        string                 $etape,
        Request $request
    ): Response
    {
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
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                break;
            case 'parcours':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Parcours ou formation non trouvés');
                }
                $place = $dpeWorkflow->getMarking($objet);
                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
                if ($request->isMethod('POST')) {
                    $dpeWorkflow->apply($objet, $process['canRefuse']); //todo: a rendre dynamique, next step ou step de validation, de refus oud e reserve
                    $histoEvent = new HistoriqueFormationEvent($objet, $this->getUser(), $etape, 'refuse',$request->request->get('commentaire'));
                    $this->eventDispatcher->dispatch($histoEvent, HistoriqueFormationEvent::ADD_HISTORIQUE_FORMATION);
                    return JsonReponse::success('ok');
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

        return $this->render('validation/_reserve.html.twig', [
            'process' => $process,
            'place' => array_keys($place->getPlaces())[0],
            'transitions' => $transitions,
            'defintion' => $definition,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
        ]);
    }

    #[Route('/validation/edit/{type}/{id}', name: 'app_validation_edit')]
    public function edit(
        ParcoursRepository       $parcoursRepository,
        FormationRepository      $formationRepository,
        WorkflowInterface        $dpeWorkflow,
        Request                  $request,
        string                   $type,
        int                      $id
    )
    {
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

            return JsonReponse::success('ok');
        }


        return $this->render('validation/_edit.html.twig', [
            'etats' => $this->validationProcess->getProcess(),
            'type' => $type,
            'id' => $id,
        ]);
    }
}
