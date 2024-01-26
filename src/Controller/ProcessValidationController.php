<?php

namespace App\Controller;

use App\Classes\GetHistorique;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProcessValidationController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface   $entityManager,
        private readonly ValidationProcess        $validationProcess,
        private readonly FormationProcess         $formationProcess,
        private readonly ParcoursProcess          $parcoursProcess,
    ) {
    }

    #[Route('/validation/valide/{etape}', name: 'app_validation_valide')]
    public function valide(
        GetHistorique       $getHistorique,
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $process = $this->validationProcess->getEtape($etape);
        $laisserPasser = false;
        switch ($type) {
            case 'formation':
                $objet = $formationRepository->find($id);

                if ($objet === null) {
                    return JsonReponse::error('Formation non trouvée');
                }

                if ($etape === 'cfvu') {
                    $laisserPasser = $getHistorique->getHistoriqueFormationLastStep($objet, 'conseil');
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
//            case 'ficheMatiere':
//                $objet = $formationRepository->find($id);
//                $place = $dpeWorkflow->getMarking($objet);
//                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
//                break;
        }

        return $this->render('process_validation/_valide.html.twig', [
            'objet' => $objet,
            'process' => $process,
            'type' => $type,
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'laisserPasser' => $laisserPasser,
        ]);
    }

    #[Route('/validation/refuse/{etape}', name: 'app_validation_refuse')]
    public function refuse(
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

        $process = $this->validationProcess->getEtape($etape);

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
            case 'ficheMatiere':
                $objet = $formationRepository->find($id);
                if ($objet === null) {
                    return JsonReponse::error('Fiche EC/matière non trouvée');
                }
//                $place = $dpeWorkflow->getMarking($objet);
//                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
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
        ParcoursRepository  $parcoursRepository,
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        $type = $request->query->get('type');
        $id = $request->query->get('id');

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
//            case 'ficheMatiere':
//                $objet = $formationRepository->find($id);
//                if ($objet === null) {
//                    return JsonReponse::error('Fiche EC/matière non trouvée');
//                }
//                $place = $dpeWorkflow->getMarking($objet);
//                $transitions = $dpeWorkflow->getEnabledTransitions($objet);
//                break;
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

    #[Route('/validation/valide-lot/{etape}', name: 'app_validation_valide_lot')]
    public function valideLot(
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFormations = $request->request->get('formations');
        } else {
            $sFormations = $request->query->get('formations');
        }
        $formations = explode(',', $sFormations);

        $process = $this->validationProcess->getEtape($etape);
        $laisserPasser = false;
        $tFormations = [];
        foreach ($formations as $id) {
            $objet = $formationRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Formation non trouvée');
            }
            $tFormations[] = $objet;
//            if ($etape === 'cfvu') {
//                $histo = $objet->getHistoriqueFormations();
//                foreach ($histo as $h) {
//                    if ($h->getEtape() === 'conseil') {
//                        if ($h->getEtat() === 'laisserPasser' && ($laisserPasser === false || $laisserPasser->getCreated() < $h->getCreated())) {
//                            $laisserPasser = $h;
//                        } elseif ($h->getEtat() === 'valide' && $laisserPasser->getCreated() < $h->getCreated()) {
//                            $laisserPasser = false;
//                        }
//                    }
//                }
//            }

            $processData = $this->formationProcess->etatFormation($objet, $process);

            if ($request->isMethod('POST')) {
                $this->formationProcess->valideFormation($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Formations validées');
            return $this->redirectToRoute('app_validation_index');
        }


        return $this->render('process_validation/_valide_lot.html.twig', [
            'formations' => $tFormations,
            'sFormations' => $sFormations,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'processData' => $processData ?? null,
            'laisserPasser' => $laisserPasser,
        ]);
    }

    #[Route('/validation/refuse-lot/{etape}', name: 'app_validation_refuse_lot')]
    public function refuseLot(
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFormations = $request->request->get('formations');
        } else {
            $sFormations = $request->query->get('formations');
        }
        $formations = explode(',', $sFormations);

        $process = $this->validationProcess->getEtape($etape);
        $tFormations = [];
        foreach ($formations as $id) {
            $objet = $formationRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Formation non trouvée');
            }
            $tFormations[] = $objet;
            $processData = $this->formationProcess->etatFormation($objet, $process);

            if ($request->isMethod('POST')) {
                $this->formationProcess->refuseFormation($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Formations refusées');
            return $this->redirectToRoute('app_validation_index');
        }

        return $this->render('process_validation/_refuse_lot.html.twig', [
            'formations' => $tFormations,
            'sFormations' => $sFormations,
            'process' => $process,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
            'objet' => $objet,
            'processData' => $processData ?? null,
        ]);
    }

    #[Route('/validation/reserve-lot/{etape}', name: 'app_validation_reserve_lot')]
    public function reserveLot(
        FormationRepository $formationRepository,
        string              $etape,
        Request             $request
    ): Response {
        if ($request->isMethod('POST')) {
            $sFormations = $request->request->get('formations');
        } else {
            $sFormations = $request->query->get('formations');
        }
        $formations = explode(',', $sFormations);

        $process = $this->validationProcess->getEtape($etape);
        $tFormations = [];
        foreach ($formations as $id) {
            $objet = $formationRepository->find($id);

            if ($objet === null) {
                return JsonReponse::error('Formation non trouvée');
            }
            $tFormations[] = $objet;
            $processData = $this->formationProcess->etatFormation($objet, $process);

            if ($request->isMethod('POST')) {
                $this->formationProcess->reserveFormation($objet, $this->getUser(), $process, $etape, $request);
            }
        }

        if ($request->isMethod('POST')) {
            $this->toast('success', 'Formations marquées avec des réserves');
            return $this->redirectToRoute('app_validation_index');
        }

        return $this->render('process_validation/_reserve_lot.html.twig', [
            'formations' => $tFormations,
            'sFormations' => $sFormations,
            'process' => $process,
            'objet' => $objet,
            'processData' => $processData ?? null,
            'type' => 'lot',
            'id' => $id,
            'etape' => $etape,
        ]);
    }
}
